<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class DatabaseSeeder extends Seeder
{

    public function run(): void
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        User::truncate();
        Supplier::truncate();
        Category::truncate();
        Item::truncate();
        Order::truncate();
        OrderItem::truncate();
        Cart::truncate();

        \App\Models\User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.default',
            'password' => 'rahasia',
            'role' => 'admin',
            'jenis_kelamin' => 1,
            'tanggal_lahir' => '2000-01-01',
            'whatsapp_number' => '081234567890',
        ]);
        \App\Models\User::create([
            'name' => 'Kasir',
            'email' => 'kasir@example.default',
            'password' => 'rahasiakasir',
            'role' => 'kasir',
            'jenis_kelamin' => 0,
            'tanggal_lahir' => '2000-01-01',
            'whatsapp_number' => '081234567890',
        ]);
        \App\Models\User::create([
            'name' => 'Pelayan',
            'email' => 'pelayan@example.default',
            'password' => 'rahasiapelayan',
            'role' => 'staff',
            'jenis_kelamin' => 0,
            'tanggal_lahir' => '2000-01-01',
            'whatsapp_number' => '081234567890',
        ]);
        \App\Models\User::create([
            'name' => 'Customer',
            'email' => 'customer@example.default',
            'password' => 'rahasiacustomer',
            'role' => 'customer',
            'jenis_kelamin' => 1,
            'tanggal_lahir' => '2000-01-01',
            'whatsapp_number' => '081234567890',
        ]);

        $customers = User::factory(25)->create();
        $suppliers = Supplier::factory(7)->create();
        $categories = Category::factory(10)->create();

        $items = Item::factory(50)->make()->each(function ($item) use ($suppliers, $categories) {
            $item->supplier_id = $suppliers->random()->id;
            $item->category_id = $categories->random()->id;
            $item->save();
        });

        Order::factory(150)
            ->create() 
            ->each(function ($order) use ($items) {

                $orderItems = OrderItem::factory(rand(1, 5))->make();

                foreach ($orderItems as $orderItem) {
                    $randomItem = $items->random();
                    $orderItem->item_id = $randomItem->id;
                    $orderItem->order_id = $order->id;
                    $orderItem->price = $randomItem->price; 
                    $orderItem->supplier_price = $randomItem->supplier_price; 
                    $orderItem->save();
                }

                $total = $order->items->sum(fn($item) => $item->price * $item->quantity);
                $order->update(['total_amount' => $total]);
            });

        $customers->random(floor($customers->count() / 2))->each(function ($customer) use ($items) {
            $itemsInCart = $items->random(rand(1, 3));
            foreach ($itemsInCart as $item) {
                Cart::factory()->create([
                    'user_id' => $customer->id,
                    'item_id' => $item->id,
                ]);
            }
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}