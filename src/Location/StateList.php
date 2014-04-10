<?php

namespace Message\Cog\Location;

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;

class StateList extends ChoiceList {

	protected $_states = [
		'US' => [
			'AL' => 'Alabama',
			'AK' => 'Alaska',
			'AZ' => 'Arizona',
			'AR' => 'Arkansas',
			'CA' => 'California',
			'CO' => 'Colorado',
			'CT' => 'Connecticut',
			'DC' => 'District of Columbia',
			'DE' => 'Delaware',
			'FL' => 'Florida',
			'GA' => 'Georgia',
			'HI' => 'Hawaii',
			'ID' => 'Idaho',
			'IL' => 'Illinois',
			'IN' => 'Indiana',
			'IA' => 'Iowa',
			'KS' => 'Kansas',
			'KY' => 'Kentucky',
			'LA' => 'Louisiana',
			'ME' => 'Maine',
			'MD' => 'Maryland',
			'MA' => 'Massachusetts',
			'MI' => 'Michigan',
			'MN' => 'Minnesota',
			'MS' => 'Mississippi',
			'MO' => 'Missouri',
			'MT' => 'Montana',
			'NE' => 'Nebraska',
			'NV' => 'Nevada',
			'NH' => 'New Hampshire',
			'NJ' => 'New Jersey',
			'NM' => 'New Mexico',
			'NY' => 'New York',
			'NC' => 'North Carolina',
			'ND' => 'North Dakota',
			'OH' => 'Ohio',
			'OK' => 'Oklahoma',
			'OR' => 'Oregon',
			'PA' => 'Pennsylvania',
			'RI' => 'Rhode Island',
			'SC' => 'South Carolina',
			'SD' => 'South Dakota',
			'TN' => 'Tennessee',
			'TX' => 'Texas',
			'UT' => 'Utah',
			'VT' => 'Vermont',
			'VA' => 'Virginia',
			'WA' => 'Washington',
			'WV' => 'West Virginia',
			'WI' => 'Wisconsin',
			'WY' => 'Wyoming',
		],
		'CA' => [
			'AB' => 'Alberta',
			'BC' => 'British Columbia',
			'MB' => 'Manitoba',
			'NB' => 'New Brunswick',
			'NL' => 'Newfoundland and Labrador',
			'NS' => 'Nova Scotia',
			'NT' => 'Northwest Territories',
			'NU' => 'Nunavut',
			'ON' => 'Ontario',
			'PE' => 'Prince Edward Island',
			'QC' => 'Quebec',
			'SK' => 'Saskatchewan',
			'YT' => 'Yukon Territory',
		],
		'MX' => [
			'DF' => 'Distrito Federal',
			'AG' => 'Aguascalientes',
			'BC' => 'Baja California',
			'BS' => 'Baja California Sur',
			'CM' => 'Campeche',
			'CO' => 'Coahuila',
			'CL' => 'Colima',
			'CS' => 'Chiapas',
			'CH' => 'Chihuahua',
			'DG' => 'Durango',
			'GT' => 'Guanajuato',
			'GR' => 'Guerrero',
			'HG' => 'Hidalgo',
			'JA' => 'Jalisco',
			'ME' => 'Estado de México',
			'MI' => 'Michoacán',
			'MO' => 'Morelos',
			'NA' => 'Nayarit',
			'NL' => 'Nuevo León',
			'OA' => 'Oaxaca',
			'PU' => 'Puebla',
			'QE' => 'Querétaro',
			'QR' => 'Quintana Roo',
			'SL' => 'San Luis Potosí',
			'SI' => 'Sinaloa',
			'SO' => 'Sonora',
			'TB' => 'Tabasco',
			'TM' => 'Tamaulipas',
			'TL' => 'Tlaxcala',
			'VE' => 'Veracruz',
			'YU' => 'Yucatán',
			'ZA' => 'Zacatecas',
		],
	];

	public function __construct(array $options = array(), array $preferredChoices = array())
	{
		$choices = $this->all();

		$labels = $choices;

		parent::__construct($choices, $labels, $preferredChoices);
	}

	public function all()
	{
		return $this->_states;
	}

	public function getByID($countryID, $stateID)
	{
		return isset($this->_states[$countryID][$stateID]) ? $this->_states[$countryID][$stateID] : false;
	}
}