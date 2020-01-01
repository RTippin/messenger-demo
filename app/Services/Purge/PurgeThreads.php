<?php

namespace App\Services\Purge;

class PurgeThreads
{
    protected $model;
    public function __construct($model)
    {
        $this->model = $model;
    }

    public function purgeThreads()
    {
        $privates = $this->model->threads->where("ttype", 1);
        $participants = $this->model->participants;
        $messages = $this->model->messages;
        foreach($privates as $private){
            $private->delete();
        }
        foreach($participants as $participant){
            $participant->delete();
        }
        foreach($messages as $message){
            $message->setTouchedRelations([]);
            $message->delete();
        }
    }
}