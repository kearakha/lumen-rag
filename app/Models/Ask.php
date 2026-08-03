<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ask extends Model
{
    protected $fillable = ['document_id', 'question', 'answer', 'sources', 'status', 'error'];

    protected $casts = [
        'sources' => 'array',
    ];
}
