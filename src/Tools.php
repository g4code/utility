<?php
/**
 * Tools
 *
 * Helpers and various methods that are commonly used
 *
 * @package    G4
 * @author     Dejan Samardzija, samardzija.dejan@gmail.com
 * @copyright  Little Genius Studio www.littlegeniusstudio.com All rights reserved
 * @version    1.0
 */

namespace G4\Utility;

class Tools
{
    /**
     * Word randomizer
     *
     * it will use specific sentence format that will always return different output and thus make illusion of "real" usage
     * example: {This|Here} is a {new|good|random} test sentence that {you {need to|should} have spun|needs to be spun}
     *
     * @param  string $text
     * @return string
     */
    public static function wordRandomizer($text)
    {
        $matches = self::getSpinnerMatches($text);
        $new = '';
        foreach ($matches as $row) {
            if(!preg_match('/[{}]/', $row)) {
                $all = explode('|', $row);
                shuffle($all);
                $new .= reset($all);
                continue;
            }
            $new .= $row;
        }

        // go into recursion
        while (preg_match('/[{}]/', $new)) {
            $new = self::wordRandomizer($new);
        }
        return $new;
    }

    private static function getSpinnerMatches($text)
    {
        return preg_split('/{([^{}]*?)}/i', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    }

    /**
     * Get all combinations for spinner sentence
     * @param string $text
     * @return array
     */
    public static function getSpinnerCombinations($text)
    {
        $matches = self::getSpinnerMatches($text);

        foreach ($matches as $row) {
            if(!preg_match('/[{}]/', $row)) {
                $all[] = explode('|', $row);
            }
        }

        $combinations = call_user_func_array(__CLASS__ . '::arrayCartesian', $all);
        foreach($combinations as $item) {
            $out[] = implode('', $item);
        }

        return array_values($out);
    }

    /**
     * Calculate cartesian product of multiple arrays
     * @return multitype:multitype:
     */
    public static function arrayCartesian() {
        $args = func_get_args();

        if(count($args) == 0) {
            return [[]]; // array with empty array is expected "empty" result
        }

        $work      = array_shift($args);
        $cartesian = call_user_func_array(__METHOD__, $args);
        $result    = [];

        foreach($work as $value) {
            foreach($cartesian as $product) {
                $result[] = array_merge([$value], $product);
            }
        }

        return $result;
    }

    /**
     * Rearange array keys to use selected field from row element
     *
     * @param  array  $data
     * @param  string $keyName
     * @return array
     */
    public static function arrayChangeKeys($data, $keyName = 'id')
    {
        $temp = [];
        if (is_array($data)) {
            foreach ($data as $key => $row) {
                if (isset($row[$keyName])) {
                    $temp[$row[$keyName]] = $row;
                } else {
                    $temp[] = $row;
                }
            }
        }
        return $temp;
    }

    /**
     * Pluck an array of values from an array.
     *
     * taken from Laravel FW
     * @see: http://laravel.com/api/source-function-array_pluck.html#271-285
     *
     * @param  array   $array
     * @param  string  $key
     * @return array
     */
    public static function arrayPluck($array, $key)
    {
        return array_map(function($value) use ($key) {
            return is_object($value) ? $value->$key : $value[$key];
        }, $array);
    }

    /**
     * Return current timestamp*
     * It will return server's REQUEST_TIME if available, or time()
     *
     * it will only calculate once, and for all other calls just return static value
     *
     * @return int
     */
    public static function ts()
    {
        static $time = null;

        if(null !== $time) {
            return $time;
        }

        $time = isset($_SERVER['REQUEST_TIME'])
            ? $_SERVER['REQUEST_TIME']
            : time();

        return $time;
    }

    /**
     * Calculate time period and return it in separated time segments
     *
     * @param int $timestamp timestamp for data in past you want to calculate elapsed period
     * @return array
     */
    public static function elapsedTime($timestamp)
    {
        // @todo: deprecated - use timeDiff instead of this

        $dateDiff = self::timeDiff($timestamp);

        unset ($dateDiff['month']);

        $dateDiff['week'] = floor($dateDiff['days_total'] / 7);
        $dateDiff['day']  = floor($dateDiff['days_total'] % 7);

        return $dateDiff;
    }

    /**
     * Return real users IP address even if behind proxy
     *
     * @param bool $allowPrivateRange
     * @param array $prependHeaders if list of additional headers is set, prepend them to list and test first
     * (used for example on CloudFlare)
     *
     * @return mixed - string containing IP address or (bool) false if not found
     */
    public function getRealIP($allowPrivateRange = false, array $prependHeaders = null)
    {
        static $realIp = null;

        if(null !== $realIp) {
            return $realIp;
        }

        $headers = [
            'HTTP_CF_CONNECTING_IP', //
            'REMOTE_ADDR',
            'CLIENT_IP',
            'FORWARDED',
            'FORWARDED_FOR',
            'FORWARDED_FOR_IP',
            'HTTP_CLIENT_IP',
            'HTTP_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED_FOR_IP',
            'HTTP_PC_REMOTE_ADDR',
            'HTTP_PROXY_CONNECTION',
            'HTTP_VIA',
            'HTTP_X_FORWARDED',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED_FOR_IP',
            'HTTP_X_IMFORWARDS',
            'HTTP_X_PROXY_CONNECTION',
            'VIA',
            'X_FORWARDED',
            'X_FORWARDED_FOR',
        ];

        if($prependHeaders !== null && !empty($prependHeaders)) {
            $headers = array_merge($prependHeaders, $headers);
        }

        foreach($headers as $row) {
            if(!array_key_exists($row, $_SERVER)) {
                continue;
            }

            foreach(explode(',', $_SERVER[$row]) as $ip) {
                $tmpIp = trim ($tmpIp);
                $portPos = stripos ($tmpIp, ':');

                if(false !== $portPos) {
                    $tmpIp = substr ($tmpIp, 0, $portPos);
                }

                $flag = $allowPrivateRange
                    ? FILTER_FLAG_NO_RES_RANGE
                    : FILTER_FLAG_NO_RES_RANGE | FILTER_FLAG_NO_PRIV_RANGE;

                if(false === filter_var($ip, FILTER_VALIDATE_IP, $flag)) {
                    continue;
                }

                return $realIp = $tmpIp;
            }
        }

        return $realIp = false;
    }
    
    /**
     * Calculates time difference between current time and specified timestamp
     * and returns array with human readable properties of time interval
     *
     * @param integer $timestamp
     * @return array
     */
    public static function timeDiff($timestamp)
    {
        $dateRef = new \DateTime('@' . $timestamp);
        $dateNow = new \DateTime();
    
        $dateDiff = $dateNow->diff($dateRef);
    
        return [
        'past_time'  => $dateDiff->invert,
        'year'       => $dateDiff->y,
        'month'      => $dateDiff->m,
        'day'        => $dateDiff->d,
        'hour'       => $dateDiff->h,
        'minute'     => $dateDiff->i,
        'second'     => $dateDiff->s,
        'days_total' => $dateDiff->days,
        ];
    }

}
