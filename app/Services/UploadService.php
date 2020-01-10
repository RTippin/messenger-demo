<?php

namespace App\Services;

use Carbon\Carbon;
use File;
use Illuminate\Http\Request;
use Validator;

class UploadService
{
    protected $extra, $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * call upload, only specify -> newUpload($type) -> message_photo, message_doc, user_image)
     * will always return array where state is bool, and text is file name or error message
     * @param null $type
     * @param null $extra
     * @return array
     */
    public function newUpload($type = null, $extra = null)
    {
        if($type){
            $this->extra = $extra;
            return $this->startUpload($type);
        }
        return [
            'state' => false,
            'error' => 'Upload type not set!'
        ];
    }

    private function startUpload($type)
    {
        switch($type){
            case 'message_photo':
            case 'messenger_avatar':
                return $this->grabFile('image_file', 1, $type);
            break;
            case 'message_doc':
                return $this->grabFile('doc_file', 2, $type);
            break;
            case 'group_avatar':
                return $this->grabFile('avatar_image_file', 1, $type);
            break;
        }
        return [
            'state' => false,
            'error' => 'Upload type not set!'
        ];
    }

    private function validate($file, $rules)
    {
        switch($rules){
            case 1:
                $rule = array('photo' => 'max:10240|required|mimes:jpeg,png,bmp,gif,svg,jpg');
                $messages = array('mimes'=>'Image must be (jpeg, jpg, png, bmp, gif, or svg)', 'max'=>'The photo must be under 10mb');
                $key = 'photo';
            break;
            case 2:
                $rule = array('file' => 'max:10240|required|mimes:pdf,doc,ppt,xls,docx,pptx,xlsx,rar,zip,7z');
                $messages = array('mimes'=>'Document must be (doc, docx, xls, xlsx, pdf, pptx, ppt, 7z, rar or zip)', 'max'=>'The file must be under 10mb');
                $key = 'file';
            break;
        }
        return Validator::make(array($key => $file), $rule, $messages);
    }

    private function grabFile($key, $rules, $type)
    {
        $file = $this->request->file($key);
        $validate = $this->validate($file, $rules);
        if($validate->fails()){
            return array("state" => false, "error" => $validate->errors()->first());
        }
        return $this->fileUpload($type, $file);
    }

    private function nameFile($file, $type)
    {
        $extension = $file->getClientOriginalExtension() ? $file->getClientOriginalExtension() : 'png';
        switch($type){
            case 'image':
                return uniqid('img_', true).'.'.$extension;
            break;
            case 'document':
                $original_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $carbon = Carbon::now()->timestamp;
                return $original_name.'_'.$carbon.'.'.$extension;
            break;
        }
        return false;
    }

    private function fileUpload($type, $file)
    {
        $name = null;
        $destination = null;
        switch($type){
            case 'message_photo':
                $destination = 'public/messenger/images';
                $name = $this->nameFile($file, 'image');
            break;
            case 'message_doc':
                $destination = 'public/messenger/documents';
                $name = $this->nameFile($file, 'document');
            break;
            case 'messenger_avatar':
                $destination = 'public/profile/'.messenger_alias();
                $this->removeOld();
                $name = $this->nameFile($file, 'image');
            break;
            case 'group_avatar':
                $destination = 'public/messenger/avatar';
                $name = $this->nameFile($file, 'image');
            break;
        }
        if($name && $file->storeAs($destination, $name)){
            return array("state" => true, "text" => $name);
        }
        return array("state" => false, "error" => "File was unable to upload. Please try again.");
    }

    private function removeOld()
    {
        $old_pic = messenger_profile()->messenger->picture;
        $file_path = storage_path('app/public/profile/'.messenger_alias().'/'.$old_pic);
        if(file_exists($file_path)){
            File::delete($file_path);
        }
    }
}
