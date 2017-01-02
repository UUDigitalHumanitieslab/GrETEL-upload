<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

// Copied from http://stackoverflow.com/a/254543
abstract class BasicEnum
{
    private static $constCacheArray = NULL;

    public static function getConstants()
    {
        if (self::$constCacheArray == NULL)
        {
            self::$constCacheArray = [];
        }

        $calledClass = get_called_class();
        if (!array_key_exists($calledClass, self::$constCacheArray))
        {
            $reflect = new ReflectionClass($calledClass);
            self::$constCacheArray[$calledClass] = $reflect->getConstants();
        }

        return self::$constCacheArray[$calledClass];
    }

    public static function isValidName($name, $strict = FALSE)
    {
        $constants = self::getConstants();

        if ($strict)
        {
            return array_key_exists($name, $constants);
        }

        $keys = array_map('strtolower', array_keys($constants));
        return in_array(strtolower($name), $keys);
    }

    public static function isValidValue($value, $strict = TRUE)
    {
        $values = array_values(self::getConstants());
        return in_array($value, $values, $strict);
    }
}

if (!function_exists('in_development'))
{
	/* Checks whether we are in development mode */
	function in_development()
	{
		return ENVIRONMENT === 'development';
	}
}
