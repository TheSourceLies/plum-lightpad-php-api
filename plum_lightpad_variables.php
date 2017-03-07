<?PHP

$lightpad_config_file = "./lightpad_config.json";//Path to the lightpad_config.json file.
$lightpad_config = json_decode(file_get_contents($lightpad_config_file));//Load the lightpad config file.
$home_config_file = "./home_config.json";//Path to the home_config.json file.
$home_config = json_decode(file_get_contents($home_config_file));//Load the home config file.
//FIX TAG: Add error checking for config file load.

$lightpad_agent_header = 'User-Agent';//User agent header.
$lightpad_agentHeader_value = 'Plum/2.3.0 (iPhone; iOS 9.2.1; Scale/2.00)'; //User agent header, based off of the phone app for plum.
$lightpad_house_access_token_header = 'X-Plum-House-Access-Token'; //House access token header.

$lightpad_set_logical_load_level_path = '/v2/setLogicalLoadLevel';
	//{"level":255,"llid":12345678-90ab-cdef-1234-567890abcdef}
	//{"level":0,"llid":12345678-90ab-cdef-1234-567890abcdef}
	
$lightpad_set_logical_load_config_path = '/v2/setLogicalLoadConfig';
	//{"config":{"glowIntensity":1},"llid":12345678-90ab-cdef-1234-567890abcdef}
	//{"config":{"glowIntensity":0.5},"llid":12345678-90ab-cdef-1234-567890abcdef}
	//{"config":{"glowTimeout":5},"llid":12345678-90ab-cdef-1234-567890abcdef}
	//{"config":{"glowTimeout":1},"llid":12345678-90ab-cdef-1234-567890abcdef}
	//{"config":{"glowEnabled":true},"llid":12345678-90ab-cdef-1234-567890abcdef}
	//{"config":{"glowEnabled":false},"llid":12345678-90ab-cdef-1234-567890abcdef}
	//{"config":{"glowColor":{"red":128,"white":0,"blue":0,"green":128}},"llid":12345678-90ab-cdef-1234-567890abcdef}
	
$lightpad_set_logical_load_glow_path = '/v2/setLogicalLoadGlow';
	//{"intensity":1,"timeout":5000,"red":255,"white":0,"blue":0,"green":0,"llid":llid":12345678-90ab-cdef-1234-567890abcdef}
	//{"intensity":0.5,"timeout":8000,"red":0,"white":0,"blue":128,"green":128,"llid":llid":12345678-90ab-cdef-1234-567890abcdef}
$lightpad_get_logical_load_metrics_path = '/v2/getLogicalLoadMetrics';
	//{"llid":12345678-90ab-cdef-1234-567890abcdef}



?>