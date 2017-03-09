<?PHP
/********************************************************************************************
Credit Where Credit Is Due

This php code and documentation is based HEAVILY off of the awesome work done by mikenmat
and would not have been possible otherwise. (Ok, maybe but with a lot of pain and suffering)

This guy is probably also the reason the PIR events were added to the lightpad output stream
by the developers, which is what I wanted most. Thanks!!

mikenemat/plum-probe
https://github.com/mikenemat/plum-probe
********************************************************************************************/
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
require_once('./config.php');



if(!$lightpad_config || !$house_config)//Check to make sure both config files have been created.
{
	die('You must run both the lighpad and house config builders to be able to control lightpads.');
}



/****************************************************************************
Turns a lightpad on.

Arguments:
	(string) $lightpad_id - The 36 digit lightpad id from the lightpad.conf.json file.
		Example: 12345678-90ab-cdef-1234-567890abcdef
Returns:
	http return code
		204 - Command received, no content.  This is a successful request.
		401 - Unauthorized. Incorrect house_access_token.
****************************************************************************/
function lightpad_off($lightpad_id)
{
	return lightpad_dim($lightpad_id, 0);
}



/****************************************************************************
Turns a lightpad off.

Arguments:
	(string) $lightpad_id - The 36 digit lightpad id from the lightpad.conf.json file.
		Example: 12345678-90ab-cdef-1234-567890abcdef
Returns:
	http return code
		204 - Command received, no content.  This is a successful request.
		401 - Unauthorized. Incorrect house_access_token.
****************************************************************************/
function lightpad_on($lightpad_id)
{
	return lightpad_dim($lightpad_id, 255);
}



/****************************************************************************
Dims a lightpad to the specified level.

Arguments:
	(string) $lightpad_id - The 36 digit lightpad id from the lightpad.conf.json file.
		Example: 12345678-90ab-cdef-1234-567890abcdef
	(int 0-255) $level - The power level to dim to. 0 is off, 128 is 50% and 255 is 100%
		Example: 100
Returns:
	http return code
		204 - Command received, no content.  This is a successful request.
		401 - Unauthorized. Incorrect house_access_token.
****************************************************************************/
function lightpad_dim($lightpad_id, $level = 128)
{
	global $lightpad_set_logical_load_level_path;
	$lightpad = lightpad_information($lightpad_id);
	$command = ['level' => $level];
	$command_path = $lightpad_set_logical_load_level_path;
	return lightpad_web_request($lightpad['lightpad_ip'], $lightpad['lightpad_port'], $lightpad['logical_load_id'], $lightpad['house_access_token'], $command_path, $command);
}



/****************************************************************************
Adjusts the color of the glow ring when motion is detected.

Arguments:
	(string) $lightpad_id - The 36 digit lightpad id from the lightpad.conf.json file.
		Example: 12345678-90ab-cdef-1234-567890abcdef
	(int 0-255) $white - The amount of white in the color ring.
	(int 0-255) $red - The amount of red in the color ring.
	(int 0-255) $green - The amount of green in the color ring.
	(int 0-255) $blue - The amount of blue in the color ring.
Returns:
	http return code
		204 - Command received, no content.  This is a successful request.
		401 - Unauthorized. Incorrect house_access_token.
****************************************************************************/
function lightpad_glow_color($lightpad_id, $white = 0, $red = 255, $green = 0, $blue = 0)
{
	$command = ['config' => ['glowColor' => ['white' => $white, 'red' => $red, 'green' => $green, 'blue' => $blue]]];
	return lightpad_alter_config($lightpad_id, $command);
}



/****************************************************************************
Adjusts the intensity (brightness) of the glow ring when motion is detected.

Arguments:
	(string) $lightpad_id - The 36 digit lightpad id from the lightpad.conf.json file.
		Example: 12345678-90ab-cdef-1234-567890abcdef
	(float 0-1) $intensity - How bright the color ring will glow.  0 is off, 1 is maximum brightness.
		Example: 0.5
Returns:
	http return code
		204 - Command received, no content.  This is a successful request.
		401 - Unauthorized. Incorrect house_access_token.
****************************************************************************/
function lightpad_glow_intensity($lightpad_id, $intensity = 1)
{
	$command = ['config' => ['glowIntensity' => $intensity]];
	return lightpad_alter_config($lightpad_id, $command);
}



/****************************************************************************
Adjusts the how long the glow ring lights up when motion is detected.

Arguments:
	(string) $lightpad_id - The 36 digit lightpad id from the lightpad.conf.json file.
		Example: 12345678-90ab-cdef-1234-567890abcdef
	(int 0-?) $timeout - Number of seconds to remain on.
		Example: 10
Returns:
	http return code
		204 - Command received, no content.  This is a successful request.
		401 - Unauthorized. Incorrect house_access_token.
****************************************************************************/
function lightpad_glow_timeout($lightpad_id, $timeout = 5)
{
	$command = ['config' => ['glowTimeout' => $timeout]];
	return lightpad_alter_config($lightpad_id, $command);
}



/****************************************************************************
Enables / disables the glow ring lighting up when motion is detected.

Arguments:
	(string) $lightpad_id - The 36 digit lightpad id from the lightpad.conf.json file.
		Example: 12345678-90ab-cdef-1234-567890abcdef
	(boolean) $enabled - true to enable, false to disable.
		Example: false
Returns:
	http return code
		204 - Command received, no content.  This is a successful request.
		401 - Unauthorized. Incorrect house_access_token.
****************************************************************************/
function lightpad_glow_enabled($lightpad_id, $enabled = true)
{
	$command = ['config' => ['glowEnabled' => $enabled]];
	return lightpad_alter_config($lightpad_id, $command);
}



/****************************************************************************
Turns the glow ring on to a specific color for a specific time.  Motion events or subsequent calls
to this function will override this command.
Use lightpad_glow_enabled($lightpad_id, false) to temporarily disable motion events changing the color.

Arguments:
	(string) $lightpad_id - The 36 digit lightpad id from the lightpad.conf.json file.
		Example: 12345678-90ab-cdef-1234-567890abcdef
	(float 0-1) $intensity - How bright the color ring will glow.  0 is off, 1 is maximum brightness.
		Example: 0.5
	(int) $timeout - milliseconds for the glow pad to remain on.
		Example: 30000 is 30 seconds.
	(int 0-255) $white - The amount of white in the color ring.
	(int 0-255) $red - The amount of red in the color ring.
	(int 0-255) $green - The amount of green in the color ring.
	(int 0-255) $blue - The amount of blue in the color ring.
Returns:
	http return code
		204 - Command received, no content.  This is a successful request.
		401 - Unauthorized. Incorrect house_access_token.
****************************************************************************/
function lightpad_glow_force($lightpad_id, $intensity = 1, $timeout = 5000, $white = 0, $red = 0, $green = 0, $blue = 255)
{
	global $lightpad_set_logical_load_glow_path;
	$lightpad = lightpad_information($lightpad_id);
	$command = ['intensity' => $intensity, 'timeout' => $timeout, 'white' => $white, 'red' => $red, 'green' => $green, 'blue' => $blue];
	$command_path = $lightpad_set_logical_load_glow_path;
	return lightpad_web_request($lightpad['lightpad_ip'], $lightpad['lightpad_port'], $lightpad['logical_load_id'], $lightpad['house_access_token'], $command_path, $command);
}



/****************************************************************************
Assistant function to others listed above, builds out the web request
by finding the lightpad in the config and send out the logical load id and
house access token as part of the request.
****************************************************************************/
function lightpad_alter_config($lightpad_id, $command)
{
	global $lightpad_set_logical_load_config_path;
	$lightpad = lightpad_information($lightpad_id);
	$command_path = $lightpad_set_logical_load_config_path;
	return lightpad_web_request($lightpad['lightpad_ip'], $lightpad['lightpad_port'], $lightpad['logical_load_id'], $lightpad['house_access_token'], $command_path, $command);
}



/****************************************************************************
Requests the current power load metrics from a logical load. (Switch or group of switches)

Arguments:
	(string) $lightpad_id - The 36 digit lightpad id from the lightpad.conf.json file.
		Example: 12345678-90ab-cdef-1234-567890abcdef
	
Returns:
	(json) Information of the logical loads power usage.  <--Will expand later
****************************************************************************/
function lightpad_logical_load_metrics($lightpad_id)
{
	global $lightpad_get_logical_load_metrics_path;
	$lightpad = lightpad_information($lightpad_id);
	$command_path = $lightpad_get_logical_load_metrics_path;
	return lightpad_web_request($lightpad['lightpad_ip'], $lightpad['lightpad_port'], $lightpad['logical_load_id'], $lightpad['house_access_token'], $command_path);
}



/****************************************************************************
Reads the house_config and lightpad_configs and returns all the nescessary information
to send a command to the switch

Arguments:
	(string) $lightpad_id - The 36 digit lightpad id from the lightpad.conf.json file.
		Example: 12345678-90ab-cdef-1234-567890abcdef
	
Returns:
	(array)
		[
			'house_id' => house_id,
			'house_access_token' => house_access_token,
			'house_name' => house_name,
			'room_id' => room_id,
			'room_name' => room_name,
			'logical_load_id' => logical_load_id,
			'logical_load_name' => logical_load_name,
			'lightpad_id' => lightpad_id,
			'lightpad_port' => port,
			'lightpad_ip' => ip
		]
****************************************************************************/
function lightpad_information($target_lightpad_id)
{
	global $house_config, $lightpad_config;
	
	$houses = $house_config->houses;
	
	foreach($houses as $house_id => $house_array)
	{
		$rooms = $house_array->rooms;
		
		foreach($rooms as $room_id => $room_array)
		{
			$logical_loads = $room_array->logical_loads;
			
			foreach($logical_loads as $logical_load_id => $logical_load_array)
			{
				$lightpads = $logical_load_array->lightpads;
				
				foreach($lightpads as $lightpad_id => $lightpad_array)
				{
					if($target_lightpad_id == $lightpad_id)
					{
						$lightpad = $lightpad_config->$lightpad_id;
						
						return [
							'house_id' => $house_id,
							'house_access_token' => $house_array->house_access_token,
							'house_name' => $house_array->house_name,
							'room_id' => $room_id,
							'room_name' => $room_array->room_name,
							'logical_load_id' => $logical_load_id,
							'logical_load_name' => $logical_load_array->logical_load_name,
							'lightpad_id' => $lightpad_id,
							'lightpad_port' => $lightpad->port,
							'lightpad_ip' => $lightpad->ip
						];
					}
				}
			}
		}
	}
	
	die("Unable to find lightpad with ID ".$lightpad_id."\n");
	//return false;
}



/******************************************************************
Sends request to the lightpad web server and decodes/returns the
result.
******************************************************************/
function lightpad_web_request($lightpad_ip, $lightpad_port, $lightpad_logical_load_id, $house_access_token, $command_path, $command = null)
{
	global $lightpad_agent_header, $lightpad_agent_header_value, $lightpad_house_access_token_header;
	
	$lightpad_url = 'https://'.$lightpad_ip.':'.$lightpad_port.$command_path;
	
	//echo $url."\n".$jsonPost."\n".$lightpad_house_access_token_headerValue;
	
	$ch = curl_init(); //Initialize cURL.
	curl_setopt($ch, CURLOPT_URL, $lightpad_url); //Set the plum get lightpad url.
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); //Disable cURL ssl verifier so we don't have to explicitly trust the page.
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); //Disable cURL ssl host verifier: SSL: certificate subject name '*' does not match target host name
	$header_array = [
		"Content-Type: application/json",
		$lightpad_agent_header.": ".$lightpad_agent_header_value,
		$lightpad_house_access_token_header.": ".hash("sha256", $house_access_token)
	];//Setup the useragent and auth headers.
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header_array); //Add the headers to the cURL request.
	if($command)//Add an additional json post to the plum rest interface.
	{
		$command['llid'] = $lightpad_logical_load_id;
		//{"level":0, "llid":"'.$lightpad['logical_load_id'].'"}
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($command));
	}
	else
	{
		$command = ['llid' => $lightpad_logical_load_id];
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($command));
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set the cURL return of the page to a string.
	
	// Debugging Options
	// curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8080');
	// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	// curl_setopt($ch, CURLOPT_HEADER, 1);
	// curl_setopt($ch, CURLOPT_VERBOSE, 1);
	
	$output = curl_exec($ch); //Perform the request, $output contains the output string.
	
	$return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	curl_close($ch); //Close cURL resource to free up system resources.
	
	if($return_code == 401)
	{
		echo("Unable to authenticate with lightpad, bad username or password?");
	}
	elseif($return_code == 204)
	{
		echo "Command received by lightpad.\n";
	}
	
	if(!$output)
	{
		return $return_code;
	}
	else
	{
		return json_decode($output, true); //Use JSON decode to convert the retun from the lightpad into an array/object.  Add error checking here. ;)
	}
}
?>