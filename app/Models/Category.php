<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, HasSlug;

    protected $fillable = ['name', 'slug', 'icon', 'off_tag'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function impacts(): HasManyThrough
    {
        return $this->hasManyThrough(Impact::class, Product::class);
    }

    /** Categories whose products are suggested as alternatives (see App\Services\ProductRecommender). */
    public function substitutes(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_substitutes', 'category_id', 'substitute_id');
    }

    /**
     * Replaces the substitutes and mirrors the links, so "A replaces B" always means "B replaces A".
     *
     * @param  array<int>  $ids
     */
    public function syncSubstitutes(array $ids): void
    {
        $ids = array_values(array_diff(array_map('intval', $ids), [$this->id]));

        DB::transaction(function () use ($ids) {
            DB::table('category_substitutes')->where('substitute_id', $this->id)->delete();
            $this->substitutes()->sync($ids);
            DB::table('category_substitutes')->insert(array_map(fn (int $id) => ['category_id' => $id, 'substitute_id' => $this->id], $ids));
        });
    }
}
