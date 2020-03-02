<?php

namespace App\Http\Controllers;

use App\Services\Messenger\MessageService;
use App\Services\Messenger\MessengerService;
use App\Services\Messenger\ThreadService;
use Illuminate\Http\Request;
use Image;
use File;
use Exception;


class ImageController extends Controller
{
    protected $messenger,
        $request;

    public function __construct(Request $request, MessengerService $messenger)
    {
        $this->request = $request;
        $this->messenger = $messenger;
    }

    public function makeImageThumb($file, $new_width = null, $new_height = null)
    {
        try{
            $width = ($new_width ? $new_width : 150);
            $height = ($new_height ? $new_height : 150);
            (Image::make($file)->width() > Image::make($file)->height()) ? $width = null : $height = null;
            return Image::cache(function($image) use($file, $width, $height){
                return $image->make($file)->resize($width, $height, function ($constraint){
                    $constraint->aspectRatio();
                })->orientate();
            },120);
        }catch (Exception $e){
            report($e);
            return Image::make($this->makeDefaultImage(1))->response();
        }
    }

    public function makeImageFull($file)
    {
        try{
            return Image::cache(function($image) use($file){
                return $image->make($file)->orientate();
            },120);
        }catch (Exception $e){
            report($e);
            return Image::make($this->makeDefaultImage(1))->response();
        }
    }

    public function makeDefaultImage($type)
    {
        switch($type){
            case 1:
                return Image::cache(function($image){
                    return $image->make(public_path('images/image404.png'))->resize(null, 100, function ($constraint){
                                $constraint->aspectRatio();
                        });
                    },120);
            break;
            case 2:
                return Image::cache(function($image){
                    return $image->make(public_path('images/users.png'))->resize(null, 200, function ($constraint){
                                $constraint->aspectRatio();
                        });
                    },120);
            break;
        }
        return $this->makeDefaultImage(1);
    }

    public function makeResponse($file, $ext, $type)
    {
        try{
            $img = ($type === 'full' ? Image::make($this->makeImageFull($file)) : Image::make($this->makeImageThumb($file)));
            $response = response($img->encode($ext, ($type === 'full' ? 70 : 50)));
            $response->header('Cache-Control', 'public');
            $response->header('Content-Type', 'image/'.$ext);
            $response->header('Content-Length', strlen($response->getOriginalContent()));
            return $response;
        }catch (Exception $e){
            report($e);
            return Image::make($this->makeDefaultImage(1))->response();
        }
    }

    public function MessengerPhotoView()
    {
        $message = MessageService::LocateGlobalMessageById($this->request->message_id);
        if($message && $this->messenger->authorize($message->thread)['state']){
            $file_path = storage_path('app/public/messenger/images/'.$message->body);
            if(file_exists($file_path)){
                $extension = File::extension($file_path);
                if(($this->request->is('*thumb')) && ($extension !== 'gif')){
                    return Image::make($this->makeImageThumb($file_path, 225, 225))->response($extension, 60);
                }
                else{
                    if($extension === 'gif'){
                        header("Content-type: image/gif");
                        readfile($file_path);
                    }
                    return Image::make($this->makeImageFull($file_path))->response($extension, 70);
                }
            }
        }
        return Image::make($this->makeDefaultImage(1))->response();
    }

    public function MessengerGroupAvatarView()
    {
        $thread = ThreadService::LocateThread($this->request->thread_id);
        if($thread && $this->messenger->authorize($thread)['state']){
            $file_path = (in_array($thread->image, array('1.png','2.png','3.png','4.png','5.png'), true ) ? public_path('images/messenger/'.$thread->image) : storage_path('app/public/messenger/avatar/'.$thread->image));
            if(file_exists($file_path)){
                $extension = File::extension($file_path);
                if(($this->request->is('*thumb')) && ($extension !== 'gif')){
                    return Image::make($this->makeImageThumb($file_path))->response($extension, 50);
                }
                else{
                    if($extension === 'gif'){
                        header("Content-type: image/gif");
                        readfile($file_path);
                    }
                    return Image::make($this->makeImageFull($file_path))->response($extension, 70);
                }
            }
        }
        return Image::make($this->makeDefaultImage(1))->response();
    }

    public function ProfileImageView($alias, $slug, $full = null, $image = null, $full_two = null)
    {
        if(!$image || !$slug || !$alias){
            return Image::make($this->makeDefaultImage(2))->response();
        }
        $file_path = storage_path('app/public/profile/'.$alias.'/'.$image);
        if(file_exists($file_path)){
            $extension = File::extension($file_path);
            if($full === 'full' || $full_two === 'full'){
                return $this->makeResponse($file_path, $extension, 'full');
            }
            else{
                return $this->makeResponse($file_path, $extension, 'thumb');
            }
        }
        return Image::make($this->makeDefaultImage(2))->response();
    }
}
