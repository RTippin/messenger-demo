<?php

namespace App\Services\Messenger;

use App\Services\Location\LocationService;

class MessengerLocationService
{

    /**
     * @var LocationService
     */
    private $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Get location details from IP, update messenger
     */
    public function update()
    {
        $location = $this->locationService->locate();
        messenger_profile()->messenger->timezone = $location['timezone'];
        messenger_profile()->messenger->ip = $location['ip'];
        messenger_profile()->messenger->save();
    }
}