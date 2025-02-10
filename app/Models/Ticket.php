<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'priority',
        'username',
        'subject',
        'error_type',
        'error_details',
        'file',
        'assigned_to',
        'match_id',
        'user_id',
        'status',
        'ticket_date',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assigned()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function match()
    {
        return $this->belongsTo(MatchModel::class);
    }

}
