<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'properties' => 'array',
    ];

    public function causer()
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Parse properties into a readable diff format
     */
    public function getChangesAttribute()
    {
        $props = $this->properties;
        
        // Handle potential double-encoding or string-stored JSON
        if (is_string($props)) {
            $props = json_decode($props, true);
        }
        
        if (!$props || !is_array($props)) return [];

        $attributes = $props['attributes'] ?? [];
        $old = $props['old'] ?? [];
        
        $changes = [];

        // For updates, compare attributes vs old
        if ($this->description === 'updated') {
            foreach ($attributes as $key => $value) {
                // Skip timestamp fields
                if (in_array($key, ['created_at', 'updated_at', 'deleted_at'])) continue;
                
                $oldValue = $old[$key] ?? null;
                
                if ($value !== $oldValue) {
                    $changes[] = [
                        'field' => $this->humanizeField($key),
                        'old' => $this->formatValue($oldValue),
                        'new' => $this->formatValue($value),
                        'type' => 'modified'
                    ];
                }
            }
        } 
        // For creations, show all non-null attributes
        elseif ($this->description === 'created') {
            foreach ($attributes as $key => $value) {
                if (in_array($key, ['created_at', 'updated_at'])) continue;
                if ($value === null) continue;

                $changes[] = [
                    'field' => $this->humanizeField($key),
                    'old' => '-',
                    'new' => $this->formatValue($value),
                    'type' => 'added'
                ];
            }
        }
        // For deletions, show the old state
        elseif ($this->description === 'deleted') {
            foreach ($old as $key => $value) {
                if (in_array($key, ['created_at', 'updated_at'])) continue;
                
                $changes[] = [
                    'field' => $this->humanizeField($key),
                    'old' => $this->formatValue($value),
                    'new' => 'DELETED',
                    'type' => 'removed'
                ];
            }
        }

        // Add reason if provided
        if (isset($props['reason'])) {
            $changes[] = [
                'field' => 'Action Reason',
                'old' => '-',
                'new' => $props['reason'],
                'type' => 'info'
            ];
        }

        return $changes;
    }

    protected function humanizeField($field)
    {
        return ucwords(str_replace(['_', 'id'], [' ', ''], $field));
    }

    protected function formatValue($value)
    {
        if (is_bool($value)) return $value ? 'Yes' : 'No';
        if (is_array($value)) return json_encode($value);
        if ($value === null) return 'N/A';
        return $value;
    }
}
