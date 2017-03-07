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



require_once('./plum_lightpad_variables.php');



function lightpad_off($lightpad_id)
{
	return lightpad_dim($lightpad_id, 0);
}

function lightpad_on($lightpad_id)
{
	return lightpad_dim($lightpad_id, 255);
}

function lightpad_dim($lightpad_id, $level = 128)
{
	global $lightpad_set_logical_load_level_path;
	$lightpad = lightpad_information($lightpad_id);
	$command = ['level' => $level];
	$command_path = $lightpad_set_logical_load_level_path;
	return lightpad_web_request($lightpad['lightpad_ip'], $lightpad['lightpad_port'], $lightpad['logical_load_id'], $lightpad['house_access_token'], $command_path, $command);
}

function lightpad_glow_color($lightpad_id, $white = 0, $red = 255, $green = 0, $blue = 0)
{
	$command = ['config' => ['glowColor' => ['white' => $white, 'red' => $red, 'green' => $green, 'blue' => $blue]]];
	return lightpad_alter_config($lightpad_id, $command);
}

//0 to 1 float.
function lightpad_glow_intensity($lightpad_id, $intensity = 1)
{
	$command = ['config' => ['glowIntensity' => $intensity]];
	return lightpad_alter_config($lightpad_id, $command);
}

//Seconds
function lightpad_glow_timeout($lightpad_id, $timeout = 5)
{
	$command = ['config' => ['glowTimeout' => $timeout]];
	return lightpad_alter_config($lightpad_id, $command);
}

//true / false
function lightpad_glow_enabled($lightpad_id, $enabled = true)
{
	$command = ['config' => ['glowEnabled' => $enabled]];
	return lightpad_alter_config($lightpad_id, $command);
}

function lightpad_glow_force($lightpad_id, $intensity = 1, $timeout = 5000, $white = 0, $red = 0, $green = 0, $blue = 255)
{
	global $lightpad_set_logical_load_glow_path;
	$lightpad = lightpad_information($lightpad_id);
	$command = ['intensity' => $intensity, 'timeout' => $timeout, 'white' => $white, 'red' => $red, 'green' => $green, 'blue' => $blue];
	$command_path = $lightpad_set_logical_load_glow_path;
	return lightpad_web_request($lightpad['lightpad_ip'], $lightpad['lightpad_port'], $lightpad['logical_load_id'], $lightpad['house_access_token'], $command_path, $command);
}

function lightpad_alter_config($lightpad_id, $command)
{
	global $lightpad_set_logical_load_config_path;
	$lightpad = lightpad_information($lightpad_id);
	$command_path = $lightpad_set_logical_load_config_path;
	return lightpad_web_request($lightpad['lightpad_ip'], $lightpad['lightpad_port'], $lightpad['logical_load_id'], $lightpad['house_access_token'], $command_path, $command);
}

function lightpad_logical_load_metrics($lightpad_id)
{
	global $lightpad_get_logical_load_metrics_path;
	$lightpad = lightpad_information($lightpad_id);
	$command_path = $lightpad_get_logical_load_metrics_path;
	return lightpad_web_request($lightpad['lightpad_ip'], $lightpad['lightpad_port'], $lightpad['logical_load_id'], $lightpad['house_access_token'], $command_path);
}

function lightpad_information($target_lightpad_id)
{
	global $home_config, $lightpad_config;
	
	$houses = $home_config->houses;
	
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
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	
	$output = curl_exec($ch); //Perform the request, $output contains the output string.
	//print_r(curl_getinfo($ch));
	curl_close($ch); //Close cURL resource to free up system resources.
	echo '['.$output.']';
	return json_decode($output, true); //Use JSON decode to convert the retun from the lightpad into an array/object.  Add error checking here. ;)
}
?>