<?php

namespace App\Traits;

trait Networked
{
    public function networks()
    {
        return $this->morphMany('App\Models\Networks\Networks', 'owner');
    }

    public function getNetworksUserAttribute()
    {
        return $this->networks()->where('party_type', 'App\User')->get();
    }

    public function pendingSentNetworks()
    {
        return $this->morphMany('App\Models\Networks\PendingNetworks', 'sender');
    }

    public function pendingReceivedNetworks()
    {
        return $this->morphMany('App\Models\Networks\PendingNetworks', 'recipient');
    }

    public function isNetworked($model)
    {
        return $this->networks()->where('party_id', $model->id)->where('party_type', get_class($model))->first() ? true : false;
    }

    public function isPendingNetwork($model)
    {
        return $this->pendingSentNetworks()->where('recipient_id', $model->id)->where('recipient_type', get_class($model))->first() ? true : false;
    }

    public function notApprovedNetwork($model)
    {
        return $this->pendingReceivedNetworks()->where('sender_id', $model->id)->where('sender_type', get_class($model))->first() ? true : false;
    }

    public function networkStatus($model)
    {
        if($this->isNetworked($model)){
            return 1;
        }
        if($this->isPendingNetwork($model)){
            return 2;
        }
        if($this->notApprovedNetwork($model)){
            return 3;
        }
        return 0;
    }
}
