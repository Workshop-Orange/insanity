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

    const STATUSES = [
        self::STATUS_NOT_DEPLOYMENT,
        self::STATUS_PENDING_DEPLOYMENT,
        self::STATUS_DEPLOYING,
        self::STATUS_DEPLOYED,
        self::STATUS_FAILED,
        self::STATUS_CANCELLED
    ];

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

    public function getSanityProjectNameAttribute()
    {
        return config('insanity.insanityId') . ":" . $this->id . ":" . $this->title;
    }

    public function getSanityProjectNamePrefixAttribute()
    {
        return config('insanity.insanityId') . ":" . $this->id . ":";
    }

    public function getSanityStudioHostAttribute()
    {
        return config('insanity.insanityId') . '-studio-' . $this->sanity_project_id;
    }
}
