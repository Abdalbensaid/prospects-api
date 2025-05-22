<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Prospect extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name', 'last_name', 'username', 'phone', 'tags', 'activity', 'user_id'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

}

