<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    // Book has many sections
    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    // Books belong to a user
    public function user()
    {
        $this->belongsTo(User::class);
    }
}
