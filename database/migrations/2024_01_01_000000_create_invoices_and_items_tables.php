<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesAndItemsTables extends Migration
{
    public function up()
    {
        // جدول محصولات
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->string('image')->nullable();
                $table->integer('inventory')->default(0);
                $table->bigInteger('price')->default(0);
                $table->timestamps();
            });
        } else {
            if (!Schema::hasColumn('products', 'inventory')) {
                Schema::table('products', function (Blueprint $table) {
                    $table->integer('inventory')->default(0);
                });
            }
        }

        // جدول فاکتور
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->date('date');
            $table->date('due_date');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('currency_id');
            $table->unsignedBigInteger('seller_id');
            $table->string('reference')->nullable();
            $table->double('discount_amount')->default(0);
            $table->double('discount_percent')->default(0);
            $table->double('tax_percent')->default(0);
            $table->double('total_amount')->default(0);
            $table->double('final_amount')->default(0);
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('persons')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
        });

        // جدول آیتم‌های فاکتور
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('qty');
            $table->bigInteger('price');
            $table->bigInteger('total');
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
}
