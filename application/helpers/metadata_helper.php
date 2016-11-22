<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/** Metadata types */
class MetadataType
{
	const MdText 		= 'text';
	const MdInt 		= 'int';
	const MdFloat 		= 'float';
	const MdDate 		= 'date';
	const MdDateTime 	= 'datetime';
}

/** Facets */
class FacetType
{
	const Checkbox 		= 'checkbox';
	const Dropdown 		= 'dropdown';
	const Slider		= 'slider';
	const DateRange		= 'date_range';


	static function getConstants()
	{
		$oClass = new ReflectionClass(__CLASS__);
		return $oClass->getConstants();
	}
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
