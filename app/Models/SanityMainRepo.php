<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Jetstream\Team;

class SanityMainRepo extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'git', 'branch', 'team_id'
    ];

    public function sanityDeployments()
    {
        return $this->hasMany(SanityDeployment::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
