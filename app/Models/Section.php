<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    // Sections belong to one book
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    // Section can have many sub sections
    public function children()
    {
        return $this->hasMany(Section::class, 'parent_id');
    }

    // Sub section can have one parent
    public function parent()
    {
        return $this->belongsTo(Section::class, 'parent_id');
    }

    // Recursive children
    public function recursiveChildren()
    {
        return $this->hasMany(Section::class, 'parent_id')->with('children');
    }

    // Section belongsTo many collaborators
    public function collaborators()
    {
        return $this->belongsToMany(User::class, 'section_collaborators')
    }

}
