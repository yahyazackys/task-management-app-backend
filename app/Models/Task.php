<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $fillable = ['user_id', 'category_id', 'title', 'description', 'reminder', 'start_date', 'end_date', 'repeat', 'to_do_list', 'to_do_list_status', 'status',];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(CategoryTask::class, 'category_id', 'id');
    }
}
