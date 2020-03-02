<?php

namespace App\Services;

use App\Services\Location\LocationService;
use App\User;
use Exception;

class LoginLoggerService
{

    /**
     * @var LocationService
     */
    private $locateService;

    public function __construct(LocationService $locateService)
    {
        $this->locateService = $locateService;
    }

    /**
     * Get location details from IP through LocationService
     * Store location data and IP to login logs for user
     * @param User $user
     */
    public function store(User $user)
    {
        try{
            $location = $this->locateService->locate();
            $user->loginLogs()->create([
                'ip' => $location['ip'],
                'data' => $location['data'] ? json_encode($location['data']) : null
            ]);
        }catch (Exception $e){
            report($e);
        }
    }

    /**
     * Retrieve latest 15 login logs from user
     * Only expose certain fields to user
     * @param User $user
     * @return \Illuminate\Support\Collection
     */
    public static function MakeRecentLoginLogs(User $user)
    {
        $login_logs = collect([]);
        try{
            $user->loginLogs->take(20)->each(function ($log) use($login_logs){
                $data = $log->data ? collect(json_decode($log->data)) : null;
                $login_logs->push([
                    'ip' => in_array($log->ip, LocationService::$HOME) ? null : $log->ip,
                    'created_at' => $log->created_at->toDateTimeString(),
                    'locale_created_at' => format_date_timezone($log->created_at)->toDateTimeString(),
                    'data' => ($data ? [
                        'country' => isset($data['country']) ? $data['country'] : null,
                        'region_name' => isset($data['region_name']) ? $data['region_name'] : null,
                        'city' => isset($data['city']) ? $data['city'] : null,
                        'mobile' => isset($data['mobile']) ? $data['mobile'] : null,
                        'timezone' => isset($data['timezone']) ? $data['timezone'] : null,
                    ] : null)
                ]);
            });
        }catch (Exception $e){
            report($e);
        }
        return $login_logs;
    }

    /**
     * Retrieve all login logs from provided user
     * Return all data (meant for admins)
     * @param User $user
     * @return \Illuminate\Support\Collection
     */
    public static function AdminMakeUserLoginLogs(User $user)
    {
        $login_logs = collect([]);
        try{
            $user->loginLogs->each(function ($log) use($login_logs){
                $data = $log->data ? collect(json_decode($log->data)) : null;
                $login_logs->push([
                    'ip' => in_array($log->ip, LocationService::$HOME) ? null : $log->ip,
                    'created_at' => $log->created_at->toDateTimeString(),
                    'locale_created_at' => format_date_timezone($log->created_at)->toDateTimeString(),
                    'data' => ($data ? [
                        'continent' => isset($data['continent']) ? $data['continent'] : null,
                        'continent_code' => isset($data['continent_code']) ? $data['continent_code'] : null,
                        'country' => isset($data['country']) ? $data['country'] : null,
                        'country_code' => isset($data['country_code']) ? $data['country_code'] : null,
                        'region' => isset($data['region']) ? $data['region'] : null,
                        'region_name' => isset($data['region_name']) ? $data['region_name'] : null,
                        'city' => isset($data['city']) ? $data['city'] : null,
                        'district' => isset($data['district']) ? $data['district'] : null,
                        'zip' => isset($data['zip']) ? $data['zip'] : null,
                        'lat' => isset($data['lat']) ? $data['lat'] : null,
                        'lon' => isset($data['lon']) ? $data['lon'] : null,
                        'timezone' => isset($data['timezone']) ? $data['timezone'] : null,
                        'currency' => isset($data['currency']) ? $data['currency'] : null,
                        'isp' => isset($data['isp']) ? $data['isp'] : null,
                        'org' => isset($data['org']) ? $data['org'] : null,
                        'as' => isset($data['as']) ? $data['as'] : null,
                        'mobile' => isset($data['mobile']) ? $data['mobile'] : null,
                        'proxy' => isset($data['proxy']) ? $data['proxy'] : null,
                        'hosting' => isset($data['hosting']) ? $data['hosting'] : null
                    ] : null)
                ]);
            });
        }catch (Exception $e){
            report($e);
        }
        return $login_logs;
    }

}
