<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $table = 'assignments';
    protected $fillable = ['operator_id', 'category_id', 'match_id','market_id'];

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function match()
    {
        return $this->belongsTo(MatchModel::class, 'match_id');
    }
}
