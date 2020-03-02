<?php

use Carbon\Carbon;

/**
 * @return Illuminate\Database\Eloquent\Model
 * Return the model set as the active profile
 * in the application, or null
 */

if (!function_exists('messenger_profile')) {
    function messenger_profile() {
        return config('messenger.profile.model');
    }
}

/**
 * @return string;
 * Return the active alias for the current messenger profile
 */
if (!function_exists('messenger_alias')) {
    function messenger_alias() {
        return config('messenger.profile.alias');
    }
}

/**
 * @return string;
 * Return the alias for the model we want to check
 */
if (!function_exists('get_messenger_alias')) {
    function get_messenger_alias($model) {
        $alias = array_search(get_class($model), config('messenger.models'));
        if($alias){
            return strtolower($alias);
        }
        return null;
    }
}

/**
 * @return string;
 * Return the alias for the model we want to check
 */
if (!function_exists('get_alias_class')) {
    function get_alias_class($alias) {
        if(array_key_exists($alias, config('messenger.models')) && class_exists(config('messenger.models')[$alias])){
            return config('messenger.models')[$alias];
        }
        return null;
    }
}

/**
 * @param $setModel
 * @return Illuminate\Database\Eloquent\Model | false
 * Set model as the active profile in the
 * application
 */
if (!function_exists('set_messenger_profile')) {
    function set_messenger_profile($setModel) {
        if(!$setModel) return false;
        $alias = array_search(get_class($setModel), config('messenger.models'));
        if($alias){
            config([
                'messenger.profile.model' => $setModel,
                'messenger.profile.alias' => strtolower($alias)
            ]);
            return $setModel;
        }
        return false;
    }
}

/**
 * @return Jenssegers\Agent\Agent;
 * return useragent
 */
if (!function_exists('agent')) {
    function agent() {
        return app('agent');
    }
}

/**
 * @param $date
 * @return Carbon
 * Change UTC time to user locale timezone
 */
if (!function_exists('format_date_timezone')) {
    function format_date_timezone(Carbon $date) {
        try{
            if(messenger_profile()) return $date->timezone(messenger_profile()->messenger->timezone ?? 'America/New_York');
        }catch (Exception $e){
            report($e);
        }
        return $date;
    }
}
