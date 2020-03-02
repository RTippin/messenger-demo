<?php

namespace App\Traits;

use App\Models\Networks\Networks;
use App\Models\Networks\PendingNetworks;

trait Networked
{
    /**
     * @return mixed
     */
    public function networks()
    {
        return $this->morphMany(Networks::class, 'owner');
    }

    /**
     * @return mixed
     */
    public function pendingSentNetworks()
    {
        return $this->morphMany(PendingNetworks::class, 'sender');
    }

    /**
     * @return mixed
     */
    public function pendingReceivedNetworks()
    {
        return $this->morphMany(PendingNetworks::class, 'recipient');
    }

    /**
     * @param $model
     * @return bool
     */
    public function isNetworked($model)
    {
        return $this->networks()->where('party_id', $model->id)->where('party_type', get_class($model))->first() ? true : false;
    }

    /**
     * @param $model
     * @return bool
     */
    public function isPendingNetwork($model)
    {
        return $this->pendingSentNetworks()->where('recipient_id', $model->id)->where('recipient_type', get_class($model))->first() ? true : false;
    }

    /**
     * @param $model
     * @return bool
     */
    public function notApprovedNetwork($model)
    {
        return $this->pendingReceivedNetworks()->where('sender_id', $model->id)->where('sender_type', get_class($model))->first() ? true : false;
    }

    /**
     * @param $model
     * @return int
     */
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