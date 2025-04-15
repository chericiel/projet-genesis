<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Connection extends Model
{
    protected $fillable = [
        'user_id',
        'email', 
        'mot_de_passe'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
