<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyGroup extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'subject', 'description', 'owner_uid', 'owner_name', 'join_code'];

    // relation users (members)
    public function users() {
        return $this->belongsToMany(User::class, 'study_group_user', 'group_id', 'user_id');
    }

    // relation messages
    public function messages()
{
    return $this->hasMany(ChatMessage::class, 'study_group_id');
}
}

