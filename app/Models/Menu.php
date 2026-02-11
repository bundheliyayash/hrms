<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'route_name', 'icon', 'order', 'is_active'];

    public function permissions()
    {
        return $this->hasMany(MenuPermission::class);
    }
}
