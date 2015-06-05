<?php
/**
 * Cleaner
 *
 * Helpers and various methods that are commonly used
 *
 * @package    G4
 * @author     Dejan Samardzija, samardzija.dejan@gmail.com
 */

namespace G4\Utility;

use G4\DI\Container as DI;

class Cleaner
{
    public function __construct()
    {
        if(!defined('DEBUG') || DEBUG !== true) {
            throw new \Exception("Still experimental, not safe to be used in production");
        }
    }

    /**
     * Takes variable and returns the same if is set otherwise returns $default
     *
     * @param mixed $variable - variable
     * @param mixed $default - default value if variable not set
     * @param mixed $allowed - array of allowed values
     * @param bool $escape - escape quotes
     * @return mixed
     */
    public function get($variable, $default = '', $allowed = '', $escape = true)
    {
        // if this is an array or object, don't destroy it, just return
        if(isset($variable) && (is_array($variable) || is_object($variable))) {
            return $variable;
        }

        // now check if is set?
        $variable = isset($variable) ? trim($variable) : $default;

        // is in allowed range?
        if(!empty($allowed) && is_array($allowed)) {
            if(!in_array($variable, $allowed)) {
                $variable = reset($allowed);
            }
        }

        return $escape ? addslashes($variable) : $variable;
    }

    /**
     * Same as _get, but it rawurldecodes the string
     *
     * @param mixed $variable - variable
     * @param mixed $default - default value if variable not set
     * @param mixed $allowed - array of allowed values
     * @param bool $escape - escape quotes
     * @return string
     */
    public function getHtml($variable, $default = '', $allowed = "", $escape = true)
    {
        return (string) rawurldecode($this->get($variable, $default, $allowed, $escape));
    }

    /**
     * Same as _getHtml, but it also strips tags
     *
     * @param mixed $variable - variable
     * @param mixed $default - default value if variable not set
     * @param mixed $allowed - array of allowed values
     * @param bool $escape - escape quotes
     * @return string
     */
    public function getString($variable, $default = '', $allowed = "", $escape = true)
    {
        return (string) strip_tags($this->getHtml($variable, $default, $allowed, $escape));
    }

    /**
     * Same as _get but casts return value to integer
     *
     * @access public
     * @param mixed $variable - variable
     * @param mixed $default - default value if variable not set
     * @param mixed $allowed - array of allowed values
     * @return integer
     */
    public function getInt($variable, $default = 0, $allowed = "")
    {
        return intval($this->get($variable, $default, $allowed, false));
    }

    /**
     * Same as _get but casts return value to float
     *
     * @access public
     * @param mixed $variable - variable
     * @param float $default - default value if variable not set
     * @param mixed $allowed - array of allowed values
     * @return float
     */
    public function getFloat($variable, $default = 0.0, $allowed = "")
    {
        return floatval($this->get($variable, $default, $allowed, false));
    }

    /**
     * Same as _get but casts return value to bollean
     *
     * @access public
     * @param mixed $variable - variable
     * @return bool
     */
    public function getBool($variable, $default = false)
    {
        return (bool) $this->get($variable, $default, array(false, true), false);
    }

    /**
     * Takes variable and returns the same if is set otherwise returns $default
     * escapes it to use with mysql
     *
     * @access public
     * @param mixed $variable - variable
     * @param mixed $default - default value if variable not set
     * @param mixed $allowed - array of allowed values
     * @param bool $paranoid - if paranoid, strip tags
     * @return mixed
     */
    public function escape($variable, $default = "", $allowed = "", $paranoid = true)
    {
        $variable = $paranoid
            ? $this->getString($variable, $default, $allowed, false)
            : $this->get($variable, $default, $allowed, false);

        if(!DI::has('db')) {
            throw new \Exception('DB object not set');
        }

        $db = DI::get('db');
        // @todo: should be something like this for mysql
        // return $db->getConnection()->real_escape_string($variable);
        return $db->realEscape($variable);
    }

    /**
     * Takes variable and returns the same if is set otherwise returns $default
     * escapes it to use with mysql
     *
     * @access public
     * @param mixed $variable - variable
     * @param mixed $default - default value if variable not set
     * @param mixed $allowed - array of allowed values
     * @param bool $paranoid - if paranoid, strip tags
     * @return mixed
     */
    public function escapeHTML($variable, $default = "", $allowed = "", $paranoid = true)
    {
        $variable = $paranoid
            ? $this->htmlEncode(rawurldecode($this->get($variable, $default, $allowed, false)), ENT_NOQUOTES, 'UTF-8', false)
            : $this->get($variable, $default, $allowed, false);

        if(!DI::has('db')) {
            throw new \Exception('DB object not set');
        }

        $db = DI::get('db');
        // @todo: should be something like this for mysql
        // return $db->getConnection()->real_escape_string($variable);
        return $db->realEscape($variable);
    }

    /**
     * Replacement for htmlspecialchars
     *
     * @param string $string
     * @param int $quoteStyle default ENT_QUOTES
     * @param string $charset default UTF-8
     * @param bool $doubleEncode - when double_encode is turned off PHP will not encode existing html entities
     * @return string
     */
    public function htmlEncode($string, $quoteStyle = ENT_QUOTES, $charset = 'UTF-8', $doubleEncode = false)
    {
        return htmlspecialchars((string) $string, $quoteStyle, $charset, $doubleEncode);
    }

    /**
     * Reverse of htmlEncode
     *
     * @param mixed $string
     * @param mixed $quoteStyle default ENT_QUOTES
     * @return string
     */
    public function htmlDecode($string, $quoteStyle = ENT_QUOTES)
    {
        return htmlspecialchars_decode((string) $string, $quoteStyle);
    }
}
