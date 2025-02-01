<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    // Define many-to-many relationship with users
    public function users()
    {
        return $this->belongsToMany(User::class, 'operator_category')->withTimestamps();
    }
}
