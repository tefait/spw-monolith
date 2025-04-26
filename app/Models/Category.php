<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(Item::class);
    }
    public function getImageAttribute($value)
    {
        if (empty($value)) {
            return asset('assets/images/semua.png');
        }

        if (Storage::disk('public')->exists($value)) {
            return Storage::url($value);
        }

        if (filter_var($value, FILTER_VALIDATE_URL) || str_starts_with($value, '/assets')) {
            return $value;
        }

        return asset('assets/images/semua.png');
    }
}
