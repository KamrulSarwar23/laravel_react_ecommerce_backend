<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $appends = ['image_url'];

    public function getImageUrlAttribute(){
        if ($this->image == "") {
            return "";
        }
        return asset('/uploads/products/small/'. $this->image);
    }

    public function ProductImages(){
        return $this->hasMany(ProductImage::class);
    }

    public function ProductSizes(){
        return $this->hasMany(ProductSize::class);
    }

    public function sizes()
    {
        return $this->belongsToMany(Size::class, 'product_sizes');
    }

}
