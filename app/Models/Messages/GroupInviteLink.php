<?php

namespace App\Models\Messages;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class GroupInviteLink extends Model
{
    use Uuids, SoftDeletes;
    public $incrementing = false;
    protected $table = 'thread_invites';
    protected $dates = ['expires_at'];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $slug = Str::random(6);
            $exist = $model->where(DB::raw('BINARY `slug`'), $slug)->first();
            $model->slug = $exist ? $slug.Str::random(1) : $slug;
        });
    }

    public function owner()
    {
        return $this->morphTo();
    }

    public function thread()
    {
        return $this->belongsTo('App\Models\Messages\Thread', 'thread_id', 'id');
    }
}
