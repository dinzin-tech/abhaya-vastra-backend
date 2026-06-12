<?php

use Illuminate\Support\Facades\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Categories;
use App\Models\Prediction;
use App\Models\Rooms;
use App\Models\Team;
use App\Models\Player;
use App\Models\TeamGame;
use App\Models\Matches;
use App\Models\MatchRound;
use App\Models\MatchResult;
use App\Models\PlayerTeam;
use App\Models\Round;

/**
 * get initials of a string
 */
if (!function_exists('initials')) {
    /**
     * Get initials of a string
     * @param $string
     * @param string $glue
     * @return string
     */
    function initials($string, $glue = ' ')
    {
        $ret = [];
        $exploded = explode(' ', $string);

        if (is_array($exploded)) {
            foreach ($exploded as $word) {
                $ret[] = strtoupper($word[0]);
            }
            return implode($glue, $ret);
        }

        return $string;

    }
}

function isActiveRoute($route, $output = "active")
{
    if (Route::currentRouteName() == $route) {
        return $output;
    }

}

function getCurrentTimezoneTime($date)
{
    if(!\Session::has('ivs_user_timezone')){
        if(\Auth::check()){
            $user = \Auth::user();
            $country_codes = json_decode(\Storage::disk('local')->get('data/country_code_json.json'), true);
            $country = isset($country_codes[array_search($user->country_code, array_column($country_codes, 'dial_code'))]['code'])? $country_codes[array_search($user->country_code, array_column($country_codes, 'dial_code'))]['code'] : 'US';
            $timezone = get_time_zone($country)??'UTC';
            \Session::put('ivs_user_timezone', $timezone);
        }else{
            \Session::put('ivs_user_timezone', 'Asia/Kolkata');
        }
        
    }
    $timezone = \Session::get('ivs_user_timezone');
    $date = Carbon::createFromFormat('Y-m-d H:i:s', $date, 'Asia/Kolkata')
    ->setTimezone($timezone);
    return date("Y-m-d H:i:s", strtotime($date));
}

function getCurrentTimezoneTimeByCountryCode($date, $user)
{
    if(!$user || ($user && !$user->country_code)){
        $timezone ='UTC';
    }else{
        $country_codes = json_decode(\Storage::disk('local')->get('data/country_code_json.json'), true);
        $country = isset($country_codes[array_search($user->country_code, array_column($country_codes, 'dial_code'))]['code'])? $country_codes[array_search($user->country_code, array_column($country_codes, 'dial_code'))]['code'] : 'US';
        $timezone = get_time_zone($country)??'UTC';
    }
    $date = Carbon::createFromFormat('Y-m-d H:i:s', $date, 'Asia/Kolkata')
    ->setTimezone($timezone);
    return [
        'time' => date("Y-m-d H:i:s", strtotime($date)),
        'timezone' => $timezone
    ];
}

function getUTCTimezoneTime($date)
{
    if(!\Session::has('ivs_user_timezone')){
        \Session::put('ivs_user_timezone', 'Asia/Kolkata');
    }
    $timezone = \Session::get('ivs_user_timezone');
    $date = Carbon::createFromFormat('Y-m-d H:i:s', $date, $timezone)
    ->setTimezone('UTC');
    return date("Y-m-d H:i:s", strtotime($date));
}

function areActiveRoutes(array $routes, $output = "active")
{
    foreach ($routes as $route) {
        if (Route::currentRouteName() == $route) {
            return $output;
        }

    }
}

function areActiveDynamicRoutes(array $routes, $output = "active")
{
    foreach ($routes as $route) {
        if (strpos(Request::url(), $route) !== false) {
            return $output;
        }

    }
}

function checkEmail($email) {
    $find1 = strpos($email, '@');
    $find2 = strpos($email, '.');
    return ($find1 !== false && $find2 !== false && $find2 > $find1);
 }

function get_date_diff($from_date, $to_date)
{
    $diff = strtotime($to_date) - strtotime($from_date);
    $days = floor($diff / (60 * 60 * 24));
    $hours = floor($diff / (60 * 60));
    $minutes = floor($diff / (60));
    $seconds = $diff;
    if ($seconds == 0) {
        $timediff = "Just Now";
    } else if ($seconds < 60) {
        $timediff = $seconds . " sec ago";
    } else if ($minutes < 60) {
        $timediff = $minutes . " min ago";
    } else if ($hours < 24) {
        $timediff = $hours . " hours ago";
    } else if ($days < 30) {
        $timediff = $days . " days ago";
    } else {
        $timediff = date('jS M g:i A', strtotime($from_date));
    }
    return $timediff;
}

function get_start_in($from_date, $to_date)
{
    $diff = strtotime($from_date) - strtotime($to_date);
    $days = floor($diff / (60 * 60 * 24));
    $hours = floor($diff / (60 * 60));
    $minutes = floor($diff / (60));
    $seconds = $diff;
    if ($seconds <= 0) {
        $timediff = "";
    } else if ($seconds < 60) {
        $timediff = "counter";
    } else if ($minutes < 60) {
        $timediff = "counter";
    } else if ($hours < 24) {
        $timediff = "counter";
    } else if ($days < 30) {
        $timediff = "counter";
    } else {
        $timediff = date('jS M g:i A', strtotime($from_date));
    }
    return $timediff;
}

function generateNewOtp(){
    return random_int(100000, 999999);
}
function getPriceTypeName($priceType)
{
    $priceTypeNames = [
        'kg' => 'Kilogram',
        'lb' => 'Pound',
        'g' => 'Gram',
        'bunch' =>'Bunches',
        "ct" => 'Counts (per individual item)',
        "mLs" => 'mLs',
        "gal"=> 'Gallons',
        "qtz" => 'Quarts',
        "pt" => 'Pints',
        "heads" => 'Heads (lettuce)',
        "bag"  => 'Bags',
        "ton" => 'Ton',
        "pack" => 'Pack',
        "dz"  => 'Dozen',
        "l" => 'Liter',
        "hfdz"  => 'Half-dozen',
        "bu"=> 'Bushel',
        "flat"=> 'Flat',
        // Add other mappings as needed
    ];

    return $priceTypeNames[$priceType] ?? $priceType;
}