<?php

namespace app\locker\helpers;

/**
 * Some handy static function for isolated tasks.
 *
 * @todo change these from static methods
 *
 * */
class Helpers {
    /*
      |----------------------------------------------------------------------------
      | scan array and replace &46; with . (This is a result of . being
      | reserved in Mongo) convert array to json as this is faster for
      | multi-dimensional arrays (?) @todo check this out.
      |----------------------------------------------------------------------------
     */

    public static function replaceHtmlEntity($array) {
        return json_decode(str_replace('&46;', '.', json_encode($array)));
    }

    /*
      |---------------------------------------------------------------------------
      | Mongo doesn't allow full stops in keys, so replace with html entity &46;
      |---------------------------------------------------------------------------
     */

    public static function replaceFullStopInKeys($string) {
        return str_replace('.', '&46;', $string);
    }

    /*
      |---------------------------------------------------------------------------
      | Loop through a statement and check keys for full stops, if exist, replace.
      |---------------------------------------------------------------------------
     */

    public static function replaceFullStop($array) {
        $output = '';

        if (!empty($array)) {

            foreach ($array as $key => $value) {

                if (is_array($value)) {

                    $output[$key] = Helpers::replaceFullStop($value);
                } else {

                    $new = Helpers::replaceFullStopInKeys($key);
                    $output[$new] = $value;
                }
            }
        }

        return $output;
    }

    /*
      |---------------------------------------------------------------------------
      | Loop through a statement and check for NULL values.
      |---------------------------------------------------------------------------
     */

    public static function checkForNull($array) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                Helpers::checkForNull($value);
            } else {
                if (!is_null($value)) {
                    //if the key is not extensions, then reject statement
                    //do something
                }
            }
        }
    }

    /*
      |----------------------------------------------------------------------------
      | Generate a random key
      |----------------------------------------------------------------------------
     */

    public static function getRandomValue() {
        return sha1(uniqid(mt_rand(), true));
    }

    /*
      |---------------------------------------------------------------------------
      | Get gravatar
      |---------------------------------------------------------------------------
     */

    public static function getGravatar($email, $size = '50') {
        return "https://www.gravatar.com/avatar/" . md5(strtolower(trim($email))) . "?s=" . $size;
    }

    /**
     * Cross Origin Requests in Internet Explorer
     * @see http://tincanapi.wikispaces.com/Tin+Can+API+Specification
     */
    public static function CORSIE() {
        $articulate_method = array('PUT', 'GET');
        return \Request::server('REQUEST_METHOD') == 'POST' && in_array(\Request::get('method'), $articulate_method);
    }

}
