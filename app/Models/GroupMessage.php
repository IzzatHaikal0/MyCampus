<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMessage extends Model
{
    protected $fillable = [
        'group_id',
        'firebase_uid',
        'message',
        'file_path',
    ];

    public function group()
    {
        return $this->belongsTo(StudyGroup::class, 'group_id'); // <--- FIX FK
    }
}
