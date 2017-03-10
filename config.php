<?PHP
$plum_account_email = "nobody@email.com"; //Your plum account email address.

$plum_account_password = "badPassword123"; //Your plum account password.

if($plum_account_email == "nobody@email.com" || $plum_account_password == "badPassword123")
{
	die("You must edit config.php and change the username and pasword to YOUR Plum account information.\n");
}

$lightpad_config_file = "./lightpad_config.json";//Path to the lightpad_config.json file.
$house_config_file = "./house_config.json";//Path to the house_config.json file.

$wait_for_this_many_lightpads_to_be_found = 3;//Continue running the lightpad scanner until you find this many lightpads.  Set to a high value to run forever.

//-------------------------------------------------------------------------------------------------------//
//-----------------------Only Edit Below Here If The Lightpad Config Builder Fails-----------------------//
//-------------------------------------------------------------------------------------------------------//
$server_adapter_ip = "0.0.0.0";//IP or 0.0.0.0 to listen on all interfaces.
$lightpad_udp_heartbeat_port = 43770;//Port the lightpads use to broadcast UDP status.  Lightpads send out a heartbeat once every ~5 minutes.

//-------------------------------------------------------------------------------------------//
//-----------------------Only Edit Below Here If They Update Their API-----------------------//
//-------------------------------------------------------------------------------------------//
$authorization_base64 = base64_encode($plum_account_email.':'.$plum_account_password); //Base 64 encoded email:password

if(file_exists($lightpad_config_file))
{
	$lightpad_config = json_decode(file_get_contents($lightpad_config_file));//Load the lightpad config file.
}
else
{
	$lightpad_config = false;
}

if(file_exists($house_config_file))
{
	$house_config = json_decode(file_get_contents($house_config_file));//Load the home config file.
}
else
{
	$house_config = false;
}

//FIX TAG: Add error checking for config file load.

//-----------------------Lightpad Config-----------------------//
$lightpad_agent_header = 'User-Agent';//User agent header.
$lightpad_agentHeader_value = 'Plum/2.3.0 (iPhone; iOS 9.2.1; Scale/2.00)'; //User agent header, based off of the phone app for plum.
$lightpad_house_access_token_header = 'X-Plum-House-Access-Token'; //House access token header.

$lightpad_set_logical_load_level_path = '/v2/setLogicalLoadLevel';//Path to configure light output level.
$lightpad_set_logical_load_config_path = '/v2/setLogicalLoadConfig';//Path to configure switch settings.
$lightpad_set_logical_load_glow_path = '/v2/setLogicalLoadGlow';//Path to force ring glow.
$lightpad_get_logical_load_metrics_path = '/v2/getLogicalLoadMetrics';//Path to pull power usage from switch.

$buffer_length = strlen("PLUM 8888 12345678-90ab-cdef-1234-567890abcdef 8443");//Set the buffer length to what we expect to receive from a lightpad.

//-----------------------Plum Website Config-----------------------//
$user_agent_header = 'User-Agent';//User agent header.
$user_agent_header_value = 'Plum/2.3.0 (iPhone; iOS 9.2.1; Scale/2.00)';//User agent header, based off of the phone app for plum.
$authorization_header = 'Authorization';//Authorization header.
$authorization_header_value = "Basic " . $authorization_base64; //Authorization type and base64 encoded email:password.

$plum_url = 'https://production.plum.technology/v2/'; //Base URL of plum API.
$plum_houses_url = $plum_url.'getHouses'; //Url of plum to obtain array of house IDs. Example return: ["451e6b3b-935b-4638-a781-7038a83309a8", "451e6b3b-935b-4638-a781-70acac3309a8"]
$plum_house_url = $plum_url.'getHouse'; //Url of plum to obtain array of house info.
$plum_room_url = $plum_url.'getRoom'; //Url of plum to obtain array of room info.
$plum_logical_load_url = $plum_url.'getLogicalLoad'; //Url of plum to obtain array of logical load info.
$plum_lightpad_url = $plum_url.'getLightpad'; //Url of plum to obtain array of lightpad info.
?>