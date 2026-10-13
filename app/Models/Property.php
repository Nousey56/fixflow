<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'address',
        'city',
        'state',
        'zip_code',
    ];

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
        ];
    }

    /**
     * The company that owns this property.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Units belonging to this property.
     */
    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * Only active properties.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    /**
     * Only archived properties.
     */
    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    /**
     * Check whether this property is archived.
     */
    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    /**
     * Archive the property without deleting its data.
     */
    public function archive(): bool
    {
        if ($this->isArchived()) {
            return true;
        }

        $this->archived_at = now();

        return $this->save();
    }

    /**
     * Restore an archived property.
     */
    public function restoreFromArchive(): bool
    {
        if (! $this->isArchived()) {
            return true;
        }

        $this->archived_at = null;

        return $this->save();
    }
}
