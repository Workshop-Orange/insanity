<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Jetstream\Team;


class SanityDeployment extends Model
{
    const STATUS_NOT_DEPLOYMENT = 'undeployed';
    const STATUS_PENDING_DEPLOYMENT = 'pending';
    const STATUS_DEPLOYING = 'deploying';
    const STATUS_DEPLOYED = 'deployed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    use HasFactory;

    protected $fillable = [
        'title', 'sanity_api_token','sanity_main_repo_id', 'team_id'
    ];

    public function sanityMainRepo()
    {
        return $this->belongsTo(SanityMainRepo::class);
    }

    public function team()
    {
        return $this->belognsTo(Team::class);
    }
}
