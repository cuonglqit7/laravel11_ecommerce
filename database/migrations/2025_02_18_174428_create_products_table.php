<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('quantity_in_stock')->notNull();
            $table->integer('quantity_sold')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->foreignId('category_id')->nullable()->constrained('categories', 'id')->onDelete('set null'); // Khi danh mục bị xóa sẽ set là null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
