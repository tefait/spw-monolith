<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'supplier_id' => 'required|exists:suppliers,id',
            'supplier_price' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0|gt:supplier_price',
            'status' => 'required|boolean',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:2048',
        ]);
        $data = $request->except('image');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('media', 'public');
        }

        Item::create($data);

        return redirect()->back()->with('success', 'Menu baru berhasil dibuat!');
    }



    public function toggleActiveState(Item $item, Request $request)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        $item->update([
            'status' => $request->status,
        ]);
        return redirect()->back()->with('success', 'Ubah status menu berhasil!');
    }

    public function update(Item $item, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'supplier_id' => 'required|exists:suppliers,id',
            'supplier_price' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0|gt:supplier_price',
            'status' => 'required|boolean',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->except('image');
        $previousImage = $item->getRawOriginal('image');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('media', 'public');
        }

        $item->update($data);


        if ($previousImage && Storage::disk('public')->exists($previousImage)) {
            Storage::disk('public')->delete($previousImage);
        }


        return redirect()->back()->with('success', 'Menu berhasil diperbarui!');
    }

    public function destroy(Item $item)
    {
        $item->delete();
        if (Storage::disk('public')->exists($item->getRawOriginal('image'))) {
            Storage::disk('public')->delete($item->getRawOriginal('image'));
        }
        return redirect()->back()->with('success', 'Menu berhasil dihapus!');
    }
}
