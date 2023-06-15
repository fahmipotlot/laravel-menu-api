<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Recipe extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $appends = [
        'image_url'
    ];

    /**
     * The menus that belong to the recipe.
     */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'menu_recipes', 'menu_id', 'recipe_id')
                ->withPivot('id')
                ->withTimestamps();
    }

    public function getImageUrlAttribute()
    {
        if ($this->image && \Storage::disk('public')->exists($this->image)) {
            return \Storage::disk('public')->url($this->image);
        } else {
            return asset('img/recipe.png');
        }
    }
}
