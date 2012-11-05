<?php


//DQL FUNCTIONS
//VERSION 2.0.2
//2006-08-01



/********* CONSTANTS *******************

THESE CONSTANTS MUST BE SET BEFORE ATTEMPTING TO 
CALL THE DB FUNCTIONS.  THEY CAN BE LEFT IN PLACE
HERE OR PLACED IN AN INCLUDE ELSEWHERE, IDEALLY
OUTSIDE OF YOUR WEB ROOT.


//MYSQL CONNECTION DETAILS
define('DB_HOST','');
define('DB_USER','');
define('DB_PASS','');

//THE DATABASE TO CONNECT TO
define('DB_NAME','');

//WHETHER OR NOT TO USE A PERSISTENT CONNECTION
define('DB_USE_PERSISTENT',false);

//WHETHER OR NOT TO OUTPUT DEBUG CODE
define('DB_DEBUG',false);

*****************************************/




function DBconnect()
	{
	//SET STATIC FLAG
	static $DBconnected = false;
	//CHECK IF CONNECTION ALREADY EXISTS
	if (!$DBconnected)
		{
		//CHECK THAT THE DB VARIABLES HAVE BEEN SET
		$DBconfigured = false;
		if (defined('DB_HOST') && defined('DB_USER') && defined('DB_PASS') && defined('DB_NAME'))
			{
			$DBconfigured = true;
			}
		//MAKE CONNECTION TO DATABASE IF CONFIGURED
		if ($DBconfigured)
			{
			if (DB_USE_PERSISTENT)
				{
				$DBconnection = mysql_pconnect(Config::get('db')->host, Config::get('db')->user, Config::get('db')->password);
				}
			else
				{
				$DBconnection = mysql_connect(Config::get('db')->host, Config::get('db')->user, Config::get('db')->password);
				}
			if ($DBconnection && mysql_select_db(Config::get('db')->database, $DBconnection))
				{
				$DBconnected = true;
				}
			//IF WE'RE NOT CONNECTED AT THIS STAGE, EMAIL THE ADMINISTRATOR WITH A WARNING
			if (!$DBconnected && DB_ALERTS)
				{
				DBalert();
				}
			}
		}
	return $DBconnected;
	}




/* 
THE VERBOSE FLAG ENABLES A DETAILED OUTPUT WHETHER THE QUERY IS SUCCESSFUL OR NOT
TO TEST THE OUTPUT WITH VERBOSE ON, USE $DBresult['success']
WITH VERBOSE OFF (DEFAULT), IF THE QUERY FAILS OR RETURNS NO RESULTS, THE FUNCTION
WILL RETURN BOOLEAN FALSE
*/

function DBquery($query, $verbose=false)
	{
	//SET COUNTER FOR NUMBER OF QUERIES EXECUTED BY SCRIPT
	static $queryCount = 0;
	$queryCount++;
	//INITIALISE OUTPUT
	$DBresult = array(
		'count' => $queryCount, 
		'query' => $query,
		'success' => false,
		'num_rows' => 0,
		'error' => '',
		'verbose' => intval($verbose)
		);
	//TRIM WHITESPACE FROM THE QUERY ENDS
	$query = trim($query);
	//MAKE SURE THE QUERY IS OF A SUPPORTED TYPE
	if (preg_match('/^(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|REPLACE|ALTER)/',$query,$match))
		{
		//SAVE THE QUERY TYPE
		$queryType = $match[1];
		//CONNECT TO THE DATABASE
		if (DBconnect())
			{
			//EXECUTE QUERY
			if ($queryID = mysql_query($query))
				{
				//UPDATE BOOLEAN FLAG
				$DBresult['success'] = true;
				//COLLECT DATA ACCORDING TO QUERY TYPE
				switch ($queryType)
					{
					case 'SELECT':
						$DBresult['num_rows'] = mysql_num_rows($queryID);
						$DBresult['num_fields'] = mysql_num_fields($queryID);
						$DBresult['records_assoc'] = array();
						$DBresult['records_num'] = array();
						if ($DBresult['num_rows'])
							{
							//COLLECT RECORDS WITH NUMERIC INDEX
							while ($record = mysql_fetch_row($queryID))
								{
								$DBresult['records_num'][] = $record;
								}
							//RESET POINTER
							mysql_data_seek($queryID,0);
							//COLLECT RECORDS WITH ASSOCIATIVE INDEX
							while ($record = mysql_fetch_assoc($queryID))
								{
								$DBresult['records_assoc'][] = $record;
								}
							break;
							}
					case 'INSERT':
						$DBresult['num_rows'] = mysql_affected_rows();
						$DBresult['insert_id'] = mysql_insert_id();
						break;
					case 'UPDATE':
					case 'DELETE':
					case 'REPLACE':
						$DBresult['num_rows'] = mysql_affected_rows();
						break;
					}
				}
			else
				{
				//IF THE QUERY FAILS, SAVE THE ERROR
				$DBresult['error'] = mysql_error();
				}
			}
		else
			{
			//OUTPUT AN ERROR TO SHOW FAILED CONNECTION
			$DBresult['error'] = 'unable to connect to the database';
			}
		}
	else
		{
		$DBresult['error'] = 'Only the following operations are permitted: SELECT, INSERT, UPDATE, DELETE';
		}
	//OUTPUT DEBUG CODE IF DEBUG IS ON
	if (Config::get('db')->debug)
		{
		echo '<pre>';
		print_r($DBresult);
		echo '</pre>';
		}
	//RETURN QUERY OUTPUT
	if ($verbose || ($DBresult['success'] && $DBresult['num_rows'])
		|| ($DBresult['success'] && $queryType == 'UPDATE'))
		{
		return $DBresult;
		}
	else
		{
		return false;
		}
	}



function DBalert()
	{
	//SET FLAG SO THAT EMAIL ONLY FIRES ONCE PER INTERVAL
	static $DBalerted = false;
	$flag = SYSTEM_PATH.DB_ALERT_FLAG;
	if (file_exists($flag))
		{
		$DBalerted = file_get_contents($flag);
		}
	//IF THE FLAG IF FALSE OR HAS EXPIRED, SEND AN EMAIL
	if (!$DBalerted || ((time() - $DBalerted) > DB_ALERT_INTERVAL))
		{
		if (mail(DB_ADMIN_EMAIL,strtoupper(DB_NAME).' - UNABLE TO CONNECT!',
		'We tried but were unable to connect to the database '.DB_NAME.' at '.$_SERVER['HTTP_HOST'].'.
The problem occured at '.date('H:i:s').' on '.date('l jS F Y')))
			{
			//ONCE THE EMAIL HAS BEEN SENT, UPDATE THE FLAG WITH THE NEW TIMESTAMP
			if ($handle = fopen($flag,'w'))
				{
				fwrite($handle,time());
				fclose($handle);
				}
			}
		}
	}




?>