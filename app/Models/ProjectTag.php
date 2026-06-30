<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTag extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'name',
    ];

    // ── Relationships ─────────────────────────────────────────────────────
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
