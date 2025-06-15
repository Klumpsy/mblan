<?php

namespace App\Traits;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait Taggable
{
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }


    public function availableTags()
    {
        return Tag::forModel(static::class);
    }

    public function syncTags(array $tagIds): static
    {
        $this->tags()->sync($tagIds);
        return $this;
    }

    public function addTag(int|Tag $tag): static
    {
        $tagModel = $tag instanceof Tag ? $tag : Tag::find($tag);

        if ($tagModel && $tagModel->canBeUsedWith(static::class)) {
            $this->tags()->syncWithoutDetaching([$tagModel->id]);
        }

        return $this;
    }

    public function addTags(array $tags): static
    {
        $validTagIds = collect($tags)->map(function ($tag) {
            $tagModel = $tag instanceof Tag ? $tag : Tag::find($tag);
            return $tagModel && $tagModel->canBeUsedWith(static::class) ? $tagModel->id : null;
        })->filter()->toArray();

        if (!empty($validTagIds)) {
            $this->tags()->syncWithoutDetaching($validTagIds);
        }

        return $this;
    }

    public function removeTag(int|Tag $tag): static
    {
        $tagId = $tag instanceof Tag ? $tag->id : $tag;
        $this->tags()->detach($tagId);
        return $this;
    }

    public function removeTags(array $tags): static
    {
        $tagIds = collect($tags)->map(fn($tag) => $tag instanceof Tag ? $tag->id : $tag)->toArray();
        $this->tags()->detach($tagIds);
        return $this;
    }

    public function hasTag(string|int|Tag $tag): bool
    {
        if ($tag instanceof Tag) {
            return $this->tags->contains('id', $tag->id);
        }

        if (is_string($tag)) {
            return $this->tags->contains('name', $tag);
        }

        return $this->tags->contains('id', $tag);
    }

    public function hasAnyTag(array $tags): bool
    {
        return collect($tags)->some(fn($tag) => $this->hasTag($tag));
    }

    public function hasAllTags(array $tags): bool
    {
        return collect($tags)->every(fn($tag) => $this->hasTag($tag));
    }


    public function getTagNames(): array
    {
        return $this->tags->pluck('name')->toArray();
    }

    public function scopeWithTag($query, string $tagName)
    {
        return $query->whereHas('tags', function ($q) use ($tagName) {
            $q->where('name', $tagName);
        });
    }


    public function scopeWithAnyTag($query, array $tagNames)
    {
        return $query->whereHas('tags', function ($q) use ($tagNames) {
            $q->whereIn('name', $tagNames);
        });
    }

    public function scopeWithAllTags($query, array $tagNames)
    {
        return $query->whereHas('tags', function ($q) use ($tagNames) {
            $q->whereIn('name', $tagNames);
        }, '=', count($tagNames));
    }
}
