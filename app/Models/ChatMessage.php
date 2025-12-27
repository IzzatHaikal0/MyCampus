<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StudyGroup;

class ChatMessage extends Model
{
    use HasFactory;

    // Field yang boleh diisi mass assignment
    protected $fillable = [
        'study_group_id', 
        'firebase_uid', 
        'sender_name', 
        'message', 
        'file_path'
    ];

    /**
     * Relasi ke StudyGroup
     */
    public function group()
    {
        return $this->belongsTo(StudyGroup::class, 'study_group_id');
    }
}
