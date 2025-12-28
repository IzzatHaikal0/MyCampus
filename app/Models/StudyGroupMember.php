<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyGroupMember extends Model
{
    protected $fillable = [
        'study_group_id',
        'firebase_uid'
    ];

    public function group()
    {
        return $this->belongsTo(StudyGroup::class);
    }
    public function messages()
{
    return $this->hasMany(ChatMessage::class, 'study_group_id');
}

}
