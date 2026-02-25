<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'status' => 'nullable|boolean',
        ]);
        $data = $request->except('image');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('media', 'public');
        }
        $data['slug'] = \Illuminate\Support\Str::slug($request->name);
        Category::where('slug', $data['slug'])->exists() ? $data['slug'] = $data['slug'] . '-' . time() : '';
        Category::create($data);

        return redirect()->back()->with('success', 'Kategori berhasil ditambah, mantap euy!');
    }
    public function destroy(Category $category)
    {
        $category->delete();
        $imagePath = $category->getRawOriginal('image');
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
        return redirect()->back()->with('success', 'Noice! Kategori berhasil dihapus!');
    }

    public function update(Category $category, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->except('image');
        $previousImage = $category->getRawOriginal('image');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('media', 'public');
        }

        $category->update($data);

        if ($previousImage && Storage::disk('public')->exists($previousImage)) {
            Storage::disk('public')->delete($previousImage);
        }


        return redirect()->back()->with('success', 'Kategori berhasil diperbarui!');
    }
    public function toggleActiveState(Category $category, Request $request)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        $category->update([
            'status' => $request->status,
        ]);
        return redirect()->back()->with('success', 'Berhasil mengubah status menu!');
    }

}
