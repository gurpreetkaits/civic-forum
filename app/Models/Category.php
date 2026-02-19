<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'description', 'sort_order', 'name_hi', 'description_hi'];

    protected $appends = ['translated_name', 'translated_description'];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    protected function translatedName(): Attribute
    {
        return Attribute::get(function () {
            if (app()->getLocale() === 'hi' && $this->name_hi) {
                return $this->name_hi;
            }
            return $this->name;
        });
    }

    protected function translatedDescription(): Attribute
    {
        return Attribute::get(function () {
            if (app()->getLocale() === 'hi' && $this->description_hi) {
                return $this->description_hi;
            }
            return $this->description;
        });
    }
}
