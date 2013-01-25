<?php

use Message\Cog\Config\Group;

$example  = new Group;

$example->name                  = 'Message';
$example->shortName             = 'message';
$example->domain                = 'message.co.uk';
$example->url                   = 'live.message.co.uk';
$example->email                 = 'debug@message.co.uk';
$example->telephone             = '+44 12345 6789';
$example->fax                   = null;
$example->vatRegistrationNumber = '0123456789';
$example->companyNumber         = '1234567';
$example->facebook              = 'message';
$example->twitter               = 'messagedigital';
$example->gaCode                = 'MESSAGE_123';

$example->address = (object) array(
	'line1'       => 'Atlas Chambers',
	'line2'       => '33 West Street',
	'town'        => 'Hove',
	'postcode'    => 'BN1 2RE',
	'countryCode' => 'GB',
	'country'     => 'United Kingdom',
);

$example->gateway = (object) array(
	'useLocalPayment' => true,
	'sagepay'         => (object) array(
		'vendor'          => 'message_live',
		'vpsProtocol'     => 1.445,
		'encryptionKey'   => 1234567,
		'paymentUrl'      => 'https://test.sagepay.com/gateway/service/vspserver-register.vsp',
		'refundUrl'       => 'https://test.sagepay.com/gateway/service/refund.vsp',
		'redirectUrlBase' => 'http://beta.message.co.uk',
	),
);

$example->updates = (object) array(
	'email'  => true,
	'postal' => false,
);

$example->admins = array(
	'Mark Bobkins',
	'Bob Smith',
);


$example1 = new Group;

$example1->key  = 'value';
$example1->key1 = array(
	'I\'m a list',
	'I\'m another thing in the list',
	'List',
);
$example1->key2 = 654;
$example1->key3 = 'Test';
$example1->key4 = array(
	'Server 6 is the place to be'
);


$example2 = new Group;

$example2->hello   = 'there';
$example2->is      = 'it';
$example2->me      = 'I\'m';
$example2->looking = 'to the left';
$example2->no      = array(
	'or is it',
);


$example3 = new Group;

$example3->var1 = 'value1';
$example3->var2 = array(
	'A list',
	'Of values',
	'Also applies',
);
$example3->var3 = 654;

return array(
	'example'  => $example,
	'example1' => $example1,
	'example2' => $example2,
	'example3' => $example3,
);