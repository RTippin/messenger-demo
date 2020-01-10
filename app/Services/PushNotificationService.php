<?php

namespace App\Services;

use Edujugon\PushNotification\PushNotification;
use Log;

class PushNotificationService
{
    protected $apple, $google;
    public function sendPushNotify($devices, $data, $voip = false)
    {
        if(!config('messenger.mobile_notify') || !$devices->count()){
            return;
        }
        $this->apple = [];
        $this->google = [];
        foreach ($devices as $device){
            if($voip && $device->voip_token){
                array_push($this->apple, $device->voip_token);
            }
            else{
                array_push($this->google, $device->device_token);
            }
        }
        $this->notificationFCM($data);
        if($voip) $this->notificationAPN($data);
        return;
    }

    private function notificationFCM($data)
    {
        $push = new PushNotification('fcm');
        $push->setMessage([
            'notification' => [
                'title' => $data['title'],
                'body'=> $data['body'],
                'sound' => 'default'

            ],
            'data' => $data['data']
        ])
        ->setDevicesToken($this->google)
        ->send();
//        Log::info(print_r($push->getFeedback(), true));

    }

    private function notificationAPN($data)
    {
        $push = new PushNotification('apn');
        $push->setMessage([
            'aps' => [
                'alert' => [
                    'title' => $data['title'],
                    'body'=> $data['body'],
                ],
                'sound' => 'default',
                'badge' => 1
            ],
            'extraPayLoad' => $data['data']
        ])
        ->setUrl((config('app.env') === 'production') ? 'ssl://gateway.push.apple.com:2195' : 'ssl://gateway.sandbox.push.apple.com:2195')
        ->setDevicesToken($this->apple)
        ->send();
    }
}
