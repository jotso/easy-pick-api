<?php

namespace Lib;

use MiddleWare\EnvironmentMiddleWare;

class Utils
{

    /**
     * Return the field value if present, otherwise return null.
     *
     * @param $field
     * @param $array
     * @return string|null
     */
    public static function arrayValue($field, array $array)
    {
        return array_key_exists($field, $array) ? $array[$field] : null;
    }

    /**
     * Url prefix based on the global 'env' variable.
     *
     * @param $env
     * @return string
     */
    public static function getEnvPrefix($env)
    {
        if (EnvironmentMiddleWare::ENV_LIVE === $env) {
            return "";
        }
        return "$env.";
    }

    /**
     * Is the APP_ENV environment variable set to phpunit.
     *
     * @return bool
     */
    public static function isPhpunit()
    {
        return false !== getenv('APP_ENV') && EnvironmentMiddleWare::ENV_PHPUNIT === getenv('APP_ENV');
    }

    public static function dump($var)
    {
        echo '<pre>';
        var_dump($var);
        die;
    }
}