<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'title',
        'institution',
        'year',
        'type',
        'level',
        'file_path',
        'file_type',
        'thumbnail_path',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Type Labels ──────────────────────────────────────────────────────
    public static function getTypeLabels(): array
    {
        return [
            'CSEE' => 'CSEE (Ordinary Level)',
            'ACSEE' => 'ACSEE (Advanced Level)',
            'Degree' => 'Degree',
            'Diploma' => 'Diploma',
            'Certificate' => 'Certificate',
            'Certification' => 'Certification',
            'Professional' => 'Professional Certification',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::getTypeLabels()[$this->type] ?? $this->type;
    }

    // ── Level Labels ──────────────────────────────────────────────────────
    public static function getLevelLabels(): array
    {
        return [
            'secondary' => 'Secondary Education',
            'tertiary' => 'Tertiary/University',
            'professional' => 'Professional',
            'certificate' => 'Certificate Program',
        ];
    }

    public function getLevelLabelAttribute(): string
    {
        return self::getLevelLabels()[$this->level] ?? $this->level;
    }

    // ── Badge Colors ──────────────────────────────────────────────────────
    public static function getTypeBadgeClass(string $type): string
    {
        return match($type) {
            'CSEE' => 'csee',
            'ACSEE' => 'acsee',
            'Degree' => 'degree',
            'Diploma' => 'diploma',
            'Certificate' => 'certificate',
            'Certification' => 'certification',
            'Professional' => 'professional',
            default => '',
        };
    }

    public static function getLevelBadgeClass(string $level): string
    {
        return match($level) {
            'secondary' => 'secondary',
            'tertiary' => 'tertiary',
            'professional' => 'professional',
            'certificate' => 'certificate',
            default => '',
        };
    }

    // ── Type Icon ──────────────────────────────────────────────────────────
    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'CSEE', 'ACSEE' => 'fa-solid fa-graduation-cap',
            'Degree' => 'fa-solid fa-university',
            'Diploma', 'Certificate' => 'fa-regular fa-file',
            'Certification' => 'fa-solid fa-award',
            'Professional' => 'fa-solid fa-badge',
            default => 'fa-regular fa-file',
        };
    }

    // ── File Icon ──────────────────────────────────────────────────────────
    public function getFileIconAttribute(): string
    {
        return match($this->file_type) {
            'pdf' => 'fa-regular fa-file-pdf',
            'image' => 'fa-regular fa-file-image',
            'doc' => 'fa-regular fa-file-word',
            'excel' => 'fa-regular fa-file-excel',
            default => 'fa-regular fa-file',
        };
    }

    // ── File Color ──────────────────────────────────────────────────────────
    public function getFileColorAttribute(): string
    {
        return match($this->file_type) {
            'pdf' => '#ff6b6b',
            'image' => '#00e5ff',
            'doc' => '#4dabf7',
            'excel' => '#51cf66',
            default => 'rgba(255,255,255,0.3)',
        };
    }
}