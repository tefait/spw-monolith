<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order::create(
            [
                'transaction_code' => 'TRX-01230123',
                'customer_name' => 'Ryuu tsuki',
                'whatsapp_number' => 62812312452,
                'email' => '62812312452@phone.id',
                'user_has_account' => false,
                'payment_method' => 'cash',
                'status' => 'unpaid',
                'notes' => 'Di antar ke kelas XII RPL 7 yaa',
                'total_amount' => 100000,
                'cash_given' => 100000,
                'change' => 0
            ]
        );
    }
}
