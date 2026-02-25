<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('store.category'), [
            'name' => 'Snacks',
            'status' => 1,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('categories', ['name' => 'Snacks']);
    }

    public function test_admin_can_delete_a_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->delete(route('delete.category', $category));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
