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

if(!$plum_account_email || !$plum_account_password)
{
	die("Please set email and password for Plum in config.php\n");
}

build_house_config();



/******************************************************************
Builds the home config file.

Returns: N/A
******************************************************************/
function build_house_config()
{
	global $house_config, $house_config_file;
	
	echo "Connecting to Plum and pulling house information.\n";
	
	$house_config = [];
	$house_config['houses'] = [];
	
	$houses = get_houses();
	
	echo "Processing ".sizeof($houses)." house(s).\n";
	foreach($houses as $house_key => $house_id)
	{
		$house = get_house($house_id);
		unset($house['hid']);
		$rooms = $house['rids'];
		unset($house['rids']);
		
		$house_config['houses'][$house_id] = $house;
		$house_config['houses'][$house_id]['rooms'] = [];
		
		echo "Processing ".sizeof($rooms)." rooms(s).\n";
		foreach($rooms as $room_key => $room_id)
		{
			$room = get_room($room_id);
			unset($room['rid']);
			unset($room['hid']);
			$logical_loads = $room['llids'];
			unset($room['llids']);
			
			$house_config['houses'][$house_id]['rooms'][$room_id] = $room;
			$house_config['houses'][$house_id]['rooms'][$room_id]['logical_loads'] = [];
			
			echo "Processing ".sizeof($logical_loads)." logical loads(s).\n";
			foreach($logical_loads as $logical_load_key => $logical_load_id)
			{
				$logical_load = get_logical_load($logical_load_id);
				unset($logical_load['rid']);
				unset($logical_load['llid']);
				$lightpads = $logical_load['lpids'];
				unset($logical_load['lpids']);
				
				$house_config['houses'][$house_id]['rooms'][$room_id]['logical_loads'][$logical_load_id] = $logical_load;
				$house_config['houses'][$house_id]['rooms'][$room_id]['logical_loads'][$logical_load_id]['lightpads'] = [];
				
				echo "Processing ".sizeof($lightpads)." lightpads(s).\n";
				foreach($lightpads as $lightpadKey => $lightpad_id)
				{
					$lightpad = get_lightpad($lightpad_id);
					unset($lightpad['llid']);
					unset($lightpad['lpid']);
					$house_config['houses'][$house_id]['rooms'][$room_id]['logical_loads'][$logical_load_id]['lightpads'][$lightpad_id] = $lightpad;
				}
			}
		}
	}
	
	echo str_repeat("-", 82)."\n\n";
	
	if(file_put_contents($house_config_file, json_encode($house_config, JSON_PRETTY_PRINT)) === false)
	{
		die('Unable to write new config file, check permissions.');
	};
	
	echo "New house configuration saved to [".$house_config_file."].";
	echo "\n\n";
	echo "Complete.\n";
}



/******************************************************************
Requests the houses IDs from the plum server for your account.

Returns: An array of house IDs.

Example Return:
[
	"12345678-90ab-cdef-1234-567890abcdef",
	"12345678-90ab-cdef-1234-567890abcdef"
]
******************************************************************/
function get_houses()
{
	global $plum_houses_url;
	return plum_web_request($plum_houses_url, null);
}



/******************************************************************
Requests the house room IDs, location (postal code), house ID, latitude / longitude, house access token, house name and local time zone offset.

Returns:
	rids: Room IDs created in the plum phone app.
	location: 5 digit postal code.
	hid: House ID.
	latlong: Decimal degrees (DD) latitude / longitude of the house.
		Note: latlong for my house was about 750 meters off.  Could be IP based or just super rough phone GPS values.
		latitude_degrees_north: Decimal degrees latitude.
		longitude_degrees_west: Decimal degrees longitude.
			Note: the longitude_degrees_west returned a positive value instead of a negative like it should have, I don't live in China.
	house_access_token: House access token, no idea what this gets used for yet.
	house_name: The name of the house defined the the plum phone app during setup.
	local_tz: Number of seconds offset from UTC for the time zone the switch is in.
		Example: -25200 is UTC - 7h or US Mountain time. (-7 * 60 * 60 = -25200)

Example Return:
{
    "rids": [
        "12345678-90ab-cdef-1234-567890abcdef",
        "12345678-90ab-cdef-1234-567890abcdef"
    ],
    "location": "80918",
    "hid": "12345678-90ab-cdef-1234-567890abcdef",
    "latlong": {
        "latitude_degrees_north": 38.1234567,
        "longitude_degrees_west": 104.1234567
    },
    "house_access_token": "12345678-90ab-cdef-1234-567890abcdef",
    "house_name": "Home",
    "local_tz": -25200
}
******************************************************************/
function get_house($house_id)
{
	global $plum_house_url;
	return plum_web_request($plum_house_url, '{"hid": "'.$house_id.'"}');
}



/******************************************************************
Requests information about a defined room in a house.

Returns: 
	rid: Room ID.
	hid: House ID.
	llids: Logical Load IDs of the switches in the room.
	room_name: The name of the room defined during creation.

Example Return:
{
    "rid": "12345678-90ab-cdef-1234-567890abcdef",
    "hid": "12345678-90ab-cdef-1234-567890abcdef",
    "llids": [
        "12345678-90ab-cdef-1234-567890abcdef",
        "12345678-90ab-cdef-1234-567890abcdef"
    ],
    "room_name": "Master Bedroom"
}
******************************************************************/
function get_room($room_id)
{
	global $plum_room_url;
	return plum_web_request($plum_room_url, '{"rid": "'.$room_id.'"}');
}



/******************************************************************
Requests information about a defined logical load in a room.

Returns: 
	rid: Room ID.
	lpids: Lightpad IDs, in an array.  Multiple if you have 2 or more lightpads joined together in a 3way configuration.
	logical_load_name: The name of the logical load (switch) defined during creation.
	llid: Logical Load IDs of the switche in the room.

Example Return:
{
    "rid": "12345678-90ab-cdef-1234-567890abcdef",
    "lpids": [
        "12345678-90ab-cdef-1234-567890abcdef"
    ],
    "logical_load_name": "Downstairs",
    "llid": "12345678-90ab-cdef-1234-567890abcdef"
}
******************************************************************/
function get_logical_load($logical_load_id)
{
	global $plum_logical_load_url;
	return plum_web_request($plum_logical_load_url, '{"llid": "'.$logical_load_id.'"}');
}



/******************************************************************
Requests information about a provisioned lightpad.

Returns: 
	config:
        slowFadeTime: 15000,
        touchRate: 0.5,
        cluster: Always 'production' unless you're a plum developer.
        uuid: Unique universal identifier, currently the same as the lightpad id.
        logRemote: false,
        forceGlow: true/false (boolean), true if color ring is set to always be on.
        occupancyAction: Future release PIR (motion sensor) even triggering.  Example, turn on the lights when you enter the room.
        occupancyTimeout: Future release, number of seconds before the lightpad decides nobody is in the room anymore.
        fadeOffTime: Time in milliseconds to take while transitioning from on to off.
        glowIntensity: 0 to 1, how bright the color ring glows when motion is detected or forced on.
        name: Future release use, currently blank.
        fadeOnTime: Time in milliseconds to take while transitioning from off to on.
        defaultLevel: 0 to 255, initial % of power to set the lights to on a single touch.
        glowTracksDimmer: false,
        dimEnabled: true/false (boolean), true if switch is used as a dimmer, false if switch is just a normal on off switch.
        glowFade: Time in milliseconds to take while transitioning the glow ring from on to off.
        amqpEnabled: Advanced Message Queuing Protocol enabled, protocol used for switch to switch communications.
        trackingSpeed: 1000,
        minimumLevel: 40,
        versionLocked: false/true, true if software updates for the lightpad are disabled.
        glowTimeout: 5, seconds of no motion detected before the lightpad ring turns off.
        rememberLastDimLevel: false,
        serialNumber: 9 
        pirSensitivity: 175,
        glowEnabled: true,
        glowColor:
            white: 0,
            red: 142,
            green: 0,
            blue: 255
        maxWattage: 420
    is_provisioned: true,
    llid: 12345678-90ab-cdef-1234-567890abcdef
    custom_gestures: 0,
    lightpad_name: 12345678-90ab-cdef-1234-567890abcdef
    lpid: 12345678-90ab-cdef-1234-567890abcdef

Example Return:
{
    "config": {
        "slowFadeTime": 15000,
        "touchRate": 0.5,
        "cluster": "production",
        "uuid": "12345678-90ab-cdef-1234-567890abcdef",
        "logRemote": false,
        "forceGlow": false,
        "occupancyAction": "undefined",
        "occupancyTimeout": 600,
        "fadeOffTime": 500,
        "glowIntensity": 1,
        "name": "",
        "fadeOnTime": 500,
        "defaultLevel": 128,
        "glowTracksDimmer": false,
        "dimEnabled": true,
        "glowFade": 1000,
        "amqpEnabled": true,
        "trackingSpeed": 1000,
        "minimumLevel": 40,
        "versionLocked": false,
        "glowTimeout": 5,
        "rememberLastDimLevel": false,
        "serialNumber": "161100147",
        "pirSensitivity": 175,
        "glowEnabled": true,
        "glowColor": {
            "white": 0,
            "red": 142,
            "green": 0,
            "blue": 255
        },
        "maxWattage": 420
    },
    "is_provisioned": true,
    "llid": "12345678-90ab-cdef-1234-567890abcdef",
    "custom_gestures": 0,
    "lightpad_name": "12345678-90ab-cdef-1234-567890abcdef",
    "lpid": "12345678-90ab-cdef-1234-567890abcdef"
}
******************************************************************/
function get_lightpad($lightpad_id)
{
	global $plum_lightpad_url;
	return plum_web_request($plum_lightpad_url, '{"lpid": "'.$lightpad_id.'"}');
}



/******************************************************************
Sends request to the plum web servers and decodes/returns the result
******************************************************************/
function plum_web_request($url, $json_post)
{
	global $user_agent_header, $user_agent_header_value, $authorization_header, $authorization_header_value;
	
	$ch = curl_init(); //Initialize cURL.
	curl_setopt($ch, CURLOPT_URL, $url); //Set the plum get house url.
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //Disable cURL ssl verifier so we don't have to explicitly trust the page.
	$header_array = array($user_agent_header.": ".$user_agent_header_value, $authorization_header.": ".$authorization_header_value);//Setup the useragent and auth headers.
	if($json_post)//Add an additional json post to the plum rest interface if requesting a house, room, logical load or lightpad.
	{
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json_post);
	}
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header_array); //Add the headers to the cURL request.
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
		die('Unable to authenticate with Plum servers, bad username or password?');
	}
	
	return json_decode($output, true); //Use JSON decode to convert the retun from plum into an array/object.  Add error checking here. ;)
}
?>