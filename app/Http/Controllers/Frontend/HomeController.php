<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function renderHomePage(Request $request)
    {
        $search = $request->input('search');
        $category = $request->input('category');

        $itemsQuery = Item::query()->where('stock', '>=', 1)->where('status', true);

        if ($search) {
            $itemsQuery->where('name', 'like', '%' . $search . '%');
        }

        if ($category) {
            $itemsQuery->where('category_id', $category);
        }

        $items = $itemsQuery->latest()->get();
        $categories = Category::latest()->get();

        return Inertia::render('Home', [
            'items' => $items,
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'category' => $category,
            ],
        ]);
    }

    public function searchItems(Request $request): JsonResponse
    {
        $query = $request->input('query');
        $items = Item::where(['name', 'price'], 'LIKE', "%$query%")->get();
        $items = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'price' => $item->price,
                'stock' => $item->stock,
                'image' => $item->image,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $items,
        ]);
    }

    public function getItemByCategory($category): JsonResponse
    {
        $items = Item::where('category', $category)->get();
        if ($items->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No items found in this category',
            ], 404);
        }
        $items = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'price' => $item->price,
                'stock' => $item->stock,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $items,
        ]);
    }
}
