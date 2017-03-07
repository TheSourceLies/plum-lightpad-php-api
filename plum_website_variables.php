<?PHP

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

$home_config = [];
$home_config_file = "./home_config.json";
$home_config = json_decode(file_get_contents($home_config_file));//Load the lightpad config file.
?>