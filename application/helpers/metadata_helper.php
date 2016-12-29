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


/** Metadata types */
class MetadataType extends BasicEnum
{
	const MdText 		= 'text';
	const MdInt 		= 'int';
	const MdFloat 		= 'float';
	const MdDate 		= 'date';
	const MdDateTime 	= 'datetime';
}

/** Facets */
class FacetType extends BasicEnum
{
	const Checkbox 		= 'checkbox';
	const Dropdown 		= 'dropdown';
	const Slider		= 'slider';
	const DateRange		= 'date_range';
}

if (!function_exists('default_facet'))
{
	function default_facet($metadata_type)
	{
		$facet_type;
		switch ($metadata_type) {
			case MetadataType::MdText:
				$facet_type = FacetType::Checkbox;
				break;
			case MetadataType::MdInt:
			case MetadataType::MdFloat:
				$facet_type = FacetType::Slider;
				break;
			case MetadataType::MdDate:
			case MetadataType::MdDateTime:
				$facet_type = FacetType::DateRange;
				break;
			default:
				$facet_type = FacetType::Checkbox;
		}

		return $facet_type;
	}
}

if (!function_exists('facet_options'))
{
	function facet_options()
	{
		$options = array();
		foreach (FacetType::getConstants() as $_ => $value)
		{
			$options[$value] = lang('facet-' . $value);
		}
		return $options;
	}
}
