<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderDiscount;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Tạo đơn hàng mới",
     *     tags={"Orders"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer", example=1, nullable=true),
     *             @OA\Property(property="recipient_name", type="string", example="Nguyễn Văn A"),
     *             @OA\Property(property="recipient_phone", type="string", example="0123456789"),
     *             @OA\Property(property="shipping_address", type="string", example="123 Đường ABC, Hà Nội"),
     *             @OA\Property(property="total_price", type="number", example=1500000),
     *             @OA\Property(property="payment_method", type="string", example="Bank_transfer"),
     *             @OA\Property(property="user_note", type="string", nullable=true, example="Giao hàng vào buổi sáng"),
     *             @OA\Property(property="items", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="product_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=2)
     *                 )
     *             ),
     *             @OA\Property(property="discounts", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="discount_id", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Đã tạo đơn hàng thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Đã tạo đơn hàng thành công"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Lỗi số lượng hàng trong kho",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Sản phẩm không đủ hàng trong kho.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Lỗi xác thực dữ liệu đầu vào",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Lỗi không xác định")
     *         )
     *     )
     * )
     */

    public function createOrder(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'error' => 'Unauthorized. Please log in to create an order.',
                ], Response::HTTP_UNAUTHORIZED);
            }

            $validator = Validator::make($request->all(), [
                'user_id' => 'nullable|exists:users,id',
                'recipient_name' => 'required|string|max:50',
                'recipient_phone' => 'required|string|max:10',
                'shipping_address' => 'required|string|max:500',
                'total_price' => 'required|numeric|min:0',
                'discounts' => 'nullable|array',
                'discounts.*.discount_id' => 'exists:discounts,id',
                'payment_method' => 'required|in:Bank_transfer,Momo,cod',
                'user_note' => 'nullable|string|max:200',
                'items' => 'required|array',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $errorQuantity = [];
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                if ($product->quantity_in_stock < $item['quantity']) {
                    $errorQuantity[$item['product_id']] = "Sản phẩm $product->product_name không đủ hàng.";
                }
            }

            if (!empty($errorQuantity)) {
                return response()->json([
                    'errors' => $errorQuantity,
                ], Response::HTTP_BAD_REQUEST);
            }

            // Generate a unique 11-character order code
            $orderCode = $this->generateUniqueOrderCode();

            if ($request->payment_method == 'cod') {
                $payment_status = 'Completed';
            } else {
                $payment_status = 'Pending';
            }

            $order = Order::create([
                'user_id' => $user->id,
                'code' => $this->generateUniqueOrderCode(),
                'recipient_name' => $request->recipient_name,
                'recipient_phone' => $request->recipient_phone,
                'shipping_address' => $request->shipping_address,
                'total_price' => $request->total_price,
                'payment_method' => $request->payment_method,
                'payment_status' => $payment_status,
                'status' => 'Pending',
                'user_note' => $request->user_note,
                'admin_note' => null,
            ]);

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $unit_price = $product->promotion_price ?? $product->price;
                $total_price = $unit_price * $item['quantity'];

                if ($product->quantity_in_stock < $item['quantity']) {
                    return response()->json([
                        'error' => "Sản phẩm $product->product_name không đủ hàng trong kho.",
                    ], Response::HTTP_BAD_REQUEST);
                }

                $product->quantity_in_stock -= $item['quantity'];
                $product->quantity_sold += $item['quantity'];
                $product->save();

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $unit_price,
                ]);

                // Delete the cart item (assuming cart_id is sent in items)
                if (isset($item['cart_id'])) {
                    Cart::where('id', $item['cart_id'])->where('user_id', $user->id)->delete();
                }
            }

            if (!empty($request->discounts)) {
                foreach ($request->discounts as $discountData) {
                    $discount = Discount::find($discountData['discount_id']);
                    if ($discount) {
                        OrderDiscount::create([
                            'order_id' => $order->id,
                            'discount_id' => $discount->id,
                        ]);
                    }
                }
            }

            return response()->json(['message' => 'Đã tạo đơn hàng thành công', 'data' => $order], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Helper method to generate a unique 11-character order code
    private function generateUniqueOrderCode(): string
    {
        do {
            // Generate a random 11-character code (e.g., "ORD-XXXXXXX")
            $code = '#ORD' . Str::upper(Str::random(7)); // ORD- plus 7 random chars
            // Ensure length is exactly 11; adjust if needed
        } while (Order::where('code', $code)->exists()); // Check uniqueness

        return $code;
    }

    /**
     * @OA\Post(
     *     path="/api/cancelOrder/{id}",
     *     summary="Hủy đơn hàng",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của đơn hàng cần hủy",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Hủy đơn hàng thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Hủy đơn hàng thành công")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy đơn hàng",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Không tìm thấy đơn hàng")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Chỉ đơn hàng có trạng thái Pending mới được hủy",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Chỉ đơn hàng có trạng thái Pending mới được hủy")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Lỗi không xác định")
     *         )
     *     )
     * )
     */
    public function cancelOrder($orderId)
    {
        try {
            $order = Order::find($orderId);

            if (!$order) {
                return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
            }

            if ($order->status !== 'Pending') {
                return response()->json(['message' => 'Chỉ đơn hàng có trạng thái "Pending" mới được hủy'], 400);
            }

            $order->update(['status' => 'Cancelled']);

            return response()->json(['message' => 'Hủy đơn hàng thành công']);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Lấy danh sách đơn hàng theo địa chỉ IP",
     *     tags={"Orders"},
     *     @OA\Response(
     *         response=200,
     *         description="Lấy danh sách đơn hàng thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="orders", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="total_price", type="number", example=1500000),
     *                     @OA\Property(property="payment_status", type="string", example="Pending"),
     *                     @OA\Property(property="status", type="string", example="Pending"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-06T12:00:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Lỗi không xác định")
     *         )
     *     )
     * )
     */
    public function getOrderByIp(Request $request)
    {
        try {
            $orders = Order::with('orderItems')
                ->where('ip_address', $request->getClientIp())
                ->get();

            if (!$orders) {
                return response()->json([
                    'error' => 'Bạn chưa có đơn hàng nào',
                ]);
            }

            return response()->json(['orders' => $orders]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
