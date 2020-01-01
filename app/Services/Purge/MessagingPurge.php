<?php

namespace App\Services\Purge;

use File;

class MessagingPurge
{
    protected $asset;
    public function __construct($asset)
    {
        $this->asset = $asset;
    }

    public function startDelete($type)
    {
        switch($type){
            case 'message':
                return $this->deleteMessage($this->asset);
            break;
            case 'thread':
                return $this->deleteThread($this->asset);
            break;
            default : return true;
        }
    }

    private function deleteMessage($message)
    {
        switch($message->mtype){
            case 1:
                $file_path = storage_path('app/public/messenger/images/'.$message->body);
                if(file_exists($file_path)){
                    File::delete($file_path);
                }
            break;
            case 2:
                $file_path = storage_path('app/public/messenger/documents/'.$message->body);
                if(file_exists($file_path)){
                    File::delete($file_path);
                }
            break;
        }
        $message->setTouchedRelations([]);
        $message->forceDelete();
        return true;
    }

    private function deleteThread($thread)
    {
        $message_files = $thread->messages()->whereIn('mtype', [1,2])->get();
        foreach($message_files as $message){
            $this->deleteMessage($message);
        }
        $file_path = storage_path('app/public/messenger/avatar/'.$thread->image);
        if(file_exists($file_path)){
            File::delete($file_path);
        }
        $thread->forceDelete();
        return true;
    }
}
