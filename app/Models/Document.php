<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Document extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['title', 'file_path', 'uploaded_by'];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
