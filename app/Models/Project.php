<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug', // ✅ Add slug to fillable
        'description',
        'full_description',
        'category',
        'icon',
        'thumbnail',
        'gallery',
        'tech_stack',
        'team_size',
        'year',
        'client',
        'duration',
        'live_url',
        'github_url',
        'featured',
        'active',
        'views',
    ];

    protected $casts = [
        'gallery'    => 'array',
        'tech_stack' => 'array',
        'featured'   => 'boolean',
        'active'     => 'boolean',
    ];

    // ── Boot Method ──────────────────────────────────────────────────────
    protected static function boot()
    {
        parent::boot();

        //  Auto-generate slug from title before creating
        static::creating(function ($project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->title);
            }
        });

        //  Auto-generate slug from title when updating
        static::updating(function ($project) {
            if ($project->isDirty('title') && empty($project->slug)) {
                $project->slug = Str::slug($project->title);
            }
        });
    }

    // ── Relationships ────────────────────────────────────────────────────
    public function tags(): HasMany
    {
        return $this->hasMany(ProjectTag::class);
    }

    public function testimonial(): HasOne
    {
        return $this->hasOne(Testimonial::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    // ── Accessors ────────────────────────────────────────────────────────
    public function getTagListAttribute(): array
    {
        return $this->tags->pluck('name')->toArray();
    }
}