<?php

namespace App\Http\Controllers;

use App\Services\Messenger\MessageService;
use App\Services\Messenger\MessengerService;
use Illuminate\Http\Request;

class DownloadsController extends Controller
{
    protected $messenger,
        $request;

    public function __construct(Request $request, MessengerService $messenger)
    {
        $this->request = $request;
        $this->messenger = $messenger;
    }

    public function MessengerDownloadDocument()
    {
        $message = MessageService::LocateGlobalMessageById($this->request->message_id);
        if($message && $this->messenger->authorize($message->thread)['state']){
            $file_path = storage_path('app/public/messenger/documents/'.$message->body);
            if(file_exists($file_path)){
                return response()->download($file_path);
            }
        }
        return response()->view('errors.custom', ['err' => 'noDownload'], 404);
    }
}
