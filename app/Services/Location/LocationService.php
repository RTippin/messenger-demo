<?php

namespace App\Services\Location;

use Illuminate\Http\Request;
use Exception;
use Torann\GeoIP\Facades\GeoIP;

class LocationService
{

    /**
     * @var Request
     */
    private $request;

    /**
     * IPs we consider default when local or not found
     * @var array
     */
    public static $HOME = [
        '127.0.0.0',
        '127.0.0.1',
        '10.0.0.1'
    ];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Use GeoIP helper to run query on IP using IP-API service
     * If IP matches found in self::$Home, return default
     * @mixin GeoIP
     * @return array
     */
    public function locate()
    {
        try{
            if(in_array($this->request->ip(), self::$HOME)){
                return $this->default();
            }
            $IP_API = geoip()->getLocation($this->request->ip())->toArray();
            if(in_array($IP_API['ip'], self::$HOME)){
                return $this->default();
            }
            return $this->success($IP_API);
        }
        catch (Exception $e){
            report($e);
        }
        return $this->default();
    }

    /**
     * @param $IP_API
     * @return array
     */
    private function success($IP_API)
    {
        return [
            'ip' => $this->request->ip(),
            'timezone' => $IP_API['timezone'],
            'data' => $IP_API
        ];
    }

    /**
     * @return array
     */
    private function default()
    {
        return [
            'ip' => $this->request->ip(),
            'timezone' => 'America/New_York',
            'data' => null
        ];
    }

}
