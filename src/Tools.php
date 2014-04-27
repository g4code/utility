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
        $matches = self::_getSpinnerMatches($text);
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

    protected static function _getSpinnerMatches($text)
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
        $matches = self::_getSpinnerMatches($text);

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
            return array(array());
        }

        $work      = array_shift($args);
        $cartesian = call_user_func_array(__METHOD__, $args);
        $result    = array();

        foreach($work as $value) {
            foreach($cartesian as $product) {
                $result[] = array_merge(array($value), $product);
            }
        }

        return $result;
    }

    /**
     * Rearange array keys to use selected field from row element
     *
     * @param  array  $data
     * @param  string $key_name
     * @return array
     */
    public static function arrayChangeKeys($data, $key_name = 'id')
    {
        $temp = array();
        if (is_array($data)) {
            foreach ($data as $key => $row) {
                if (isset($row[$key_name])) {
                    $temp[$row[$key_name]] = $row;
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
     * @return int
     */
    public static function ts()
    {
        return isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
    }

    /**
     * Calculate time period and return it in human readable format
     *
     * @autor Dejan Samardzija
     * @param int $timestamp timestamp for data in past you want to calculate elapsed period
     */
    public static function elapsedTime($timestamp)
    {
        // @todo: deprecated

        $second = 1;
        $minute = $second * 60;
        $hour   = $minute * 60;
        $day    = $hour * 24;
        $week   = $day * 7;
        $year   = $week * 52;

        $timeSegments = array(
            'year'   => 0,
            'week'   => 0,
            'day'    => 0,
            'hour'   => 0,
            'minute' => 0,
            'second' => 0,
        );

        $elapsedTime = time() - intval($timestamp);

        if ($elapsedTime <= 0) {
            return $timeSegments;
        }

        foreach($timeSegments as $key => $value) {
            if ($elapsedTime >= $$key) {
                $timeSegments[$key] = floor($elapsedTime/$$key);
                $elapsedTime -= $timeSegments[$key] * $$key;
            }
        }

        return $timeSegments;
    }

} // end of class
