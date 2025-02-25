<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    // Define many-to-many relationship with users
    public function users()
    {
        return $this->belongsToMany(User::class, 'operator_category')->withTimestamps();
    }

    public function markets()
    {
        return $this->hasMany(Market::class);
    }
    
    public function matches()
    {
        return $this->hasMany(MatchModel::class);
    }

    public function assignments(): HasMany {
        return $this->hasMany(Assignment::class, 'category_id');
    }
}
