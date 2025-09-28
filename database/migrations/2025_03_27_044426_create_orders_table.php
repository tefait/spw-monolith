<?php

use App\Models\User;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->unique();
            $table->string('customer_name');
            $table->string('whatsapp_number');
            $table->string('email');
            $table->boolean('user_has_account')->default(false);
            $table->foreignIdFor(User::class)->nullable()->constrained()->noActionOnDelete()->cascadeOnUpdate();
            $table->enum('payment_method', ['qris', 'cash']);
            $table->enum('status', ['paid', 'unpaid', 'under-review', 'rejected', 'done'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->unsignedInteger('total_amount');
            $table->unsignedInteger('cash_given')->nullable();
            $table->unsignedInteger('change')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
