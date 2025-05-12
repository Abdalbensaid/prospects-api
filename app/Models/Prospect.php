<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prospect extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name', 'last_name', 'username', 'phone', 'tags', 'activity'
    ];
}

