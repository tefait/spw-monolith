<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_an_item(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $response = $this->actingAs($admin)->post(route('store.item'), [
            'name' => 'Testing Burger',
            'price' => 20000,
            'stock' => 10,
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'supplier_price' => 10000,
            'status' => 1,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('items', ['name' => 'Testing Burger']);
    }

    public function test_admin_can_delete_an_item(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $item = Item::factory()->create();

        $response = $this->actingAs($admin)->delete(route('delete.item', $item));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('items', ['id' => $item->id]);
    }
}
