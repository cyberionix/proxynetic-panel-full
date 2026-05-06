<?php

namespace App\Models;

use App\Models\Scopes\SeqOrderScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

//    public function parent(): HasOne
//    {
//        return $this->hasOne(ProductCategory::class, "id", "parent_id");
//    }

    protected static function booted()
    {
        static::addGlobalScope(new SeqOrderScope);
    }

    public function children(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'parent_id', 'id')->with("products")->orderBy("name");
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, "category_id", "id")->with("prices")->where(["is_active" => 1]);
    }
}
