<?php

namespace Mothership\Framework\Functions;

class Text
{
	public function toTitleCase($string)
	{
		$string = strtolower($string);
		$string = join("'", array_map('ucwords', explode("'", $string)));
		$string = join("-", array_map('ucwords', explode("-", $string)));
		$string = join("(", array_map('ucwords', explode("(", $string)));
		$string = join("Mac", array_map('ucwords', explode("Mac", $string)));
		$string = join("Mc", array_map('ucwords', explode("Mc", $string)));

		$ignores = array(
			' a ', 
			' or ', 
			' if ', 
			' it ', 
			' and ', 
			' or ', 
			' nor ', 
			' but ', 
			' so ', 
			' is ', 
			' the ', 
			' are ',
		);

		$string = str_replace(array_map('ucwords', $ignores), $ignores, $string);
		
		return $string;
	}

	/**
	 * Returns a standard plural letter if the input
	 * amount would require pluralisation.
	 * 
	 * @param 	int $amount	Input amount
	 * @return 	string 		Returns the standard plural letter or an empty string
	 */
	public function plural($amount)
	{
		return ($amount == 1) ? '' : 's';
	}

	public function pluralise($string, $amount)
	{
		return $string . $this->plural($amount);
	}

	public function parsePossessive($string)
	{
		return $string . (substr($string, -1) === 's' ? "'" : "'s");
	}

	/**
	 * Obfuscates an input string as HTML ASCII entities.
	 * 
	 * @param 	string $string 	Input string
	 * @return 	string 			Obfuscated string
	 */
	public function obfuscate($string)
	{
		$output = '';
		$length = strlen($str);

		for($i = 0; $i < $length; $i++) {
			// GET CHARACTER ASCII CODE AND USE AS HTML ENTITY
			$output .= '&#' . ord($str[$i]) . ';';
		}

		return $output;
	}

	public function toSlug($string, $checkExists = true)
	{
		$return = str_replace(
			array(
				'&',
				'/',
				'+',
				'=',
				'@',
				'!',
			), 
			array(
				'and',
				'or',
				'plus',
				'equals',
				'at',
				'',
			),
			$string
		);
		
		$return = preg_replace('/[^a-z0-9\s]/i', '', $return);
		
		$return = str_replace(' ', '-', strtolower($return));
		
		if($checkExists) {
			$i = 1;
			while($this->slugExists($return)) {
				$newReturn = $return.'-'.$i;
				$i++;
			}
			$return = $newReturn;
		}
		
		return $return;
	}

	public function slugExists($slug)
	{
		$db = new DBquery;
		$db->query('SELECT id FROM entries WHERE slug = ' . $db->escape($slug));
		
		return ($check->numrows() != 0 || file_exists(PUBLIC_PATH . $slug));
	}

	public function camelCapsToString($string)
	{
		$string = ucfirst(str_replace('ID', 'Id', $string));
		if ($words = preg_match_all('/([A-Z]{1}[a-z0-9]+)/', $string, $matches)) {
			$string = array_shift($matches[1]);
			while ($word = array_shift($matches[1])) {
				$string .= ' ' . strtolower($word);
			}
		}

		return $string;
	}

	public function toCamelCaps($string, $uppercaseID = true)
	{
		$map = array(
			'link'                          => 'link',
			'order_id'                      => 'orderID',
			'priority'                      => 'priority',
			'catalogue_id'                  => 'catalogueID',
			'hide_if_purchased'             => 'hideIfPurchased',
			'start_date'                    => 'startDate',
			'end_date'                      => 'endDate',
			'deleted'                       => 'deleted',
			'click_count'                   => 'clickCount',
			'image_location'                => 'imageLocation',
			'alt_text'                      => 'altText',
			'title'                         => 'title',
			'id'                            => 'id',
			'product_id'                    => 'productID',
			'version_id'                    => 'versionID',
			'product_name'                  => 'productName',
			'category_id'                   => 'categoryID',
			'brand_id'                      => 'brandID',
			'product_year'                  => 'productYear',
			'tax_code'                      => 'taxCode',
			'colour'                        => 'colour',
			'size'                          => 'size',
			'variant'                       => 'variant',
			'bundle_id'                     => 'bundleID',
			'bundle_name'                   => 'bundleName',
			'bundle_valid_from'             => 'bundleValidFrom',
			'bundle_valid_to'               => 'bundleValidTo',
			'display_name'                  => 'displayName',
			'price'                         => 'price',
			'short_description'             => 'shortDescription',
			'description'                   => 'description',
			'weight'                        => 'weight',
			'default_cross_sell'            => 'defaultCrossSell',
			'bodypart_id'                   => 'bodypartID',
			'sizegroup_id'                  => 'sizegroupID',
			'fabric'                        => 'fabric',
			'features'                      => 'features',
			'sizing'                        => 'sizing',
			'export_value'                  => 'exportValue',
			'export_description'            => 'exportDescription',
			'export_manufacture_country_id' => 'exportManufactureCountryID',
			'season'                        => 'season',
			'care_instructions'             => 'careInstructions',
			'picking_description'           => 'pickingDescription',
		);

		if (isset($map[$string])) {
			return $map[$string];
		}

		$words = preg_split('/(_| |\-)/', strtolower($string));
		$string = array_shift($words);
		
		while ($word = array_shift($words)) {
			$string .= ucfirst($word);
		}

		if ($uppercaseID) {
			$string = str_replace('Id', 'ID', $string);
		}

		return $string;
	}
}