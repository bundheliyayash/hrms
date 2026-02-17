<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryHistory extends Model
{
    protected $fillable = ['user_id', 'amount', 'effective_date', 'change_reason'];

    protected $casts = [
        'effective_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
