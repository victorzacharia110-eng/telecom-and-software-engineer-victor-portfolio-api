<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'author',
        'role',
        'company',
        'avatar',
        'content',
        'rating',
        'featured',
    ];

    protected $casts = [
        'rating'   => 'integer',
        'featured' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // ── Accessors ─────────────────────────────────────────────────────────
    public function getStarsAttribute(): string
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    // ── Scopes ────────────────────────────────────────────────────────────
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }
}
