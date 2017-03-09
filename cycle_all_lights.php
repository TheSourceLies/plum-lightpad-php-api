<?PHP
//Open source api and examples for managing Plum Lightpads via PHP.

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
header('Content-type: text/javascript');
require_once('./plum_lightpad_functions.php');

foreach($lightpad_config as $lightpad_id => $lightpad_data)
{
	$lightpad_information = lightpad_information($lightpad_id);
	
	echo "Cycling Light: ".$lightpad_information['house_name']." - ".$lightpad_information['room_name']." - ".$lightpad_information['logical_load_name']."\n";
	
	lightpad_off($lightpad_id);
	sleep(2);
	lightpad_on($lightpad_id);
}

/*Some Example Commands
lightpad_off($lightpad_id);
lightpad_on($lightpad_id);
lightpad_glow_color($lightpad_id, 0, 255, 0, 0);
lightpad_glow_intensity($lightpad_id, 1);
lightpad_glow_timeout($lightpad_id, 5);
lightpad_glow_enabled($lightpad_id, true);
lightpad_dim($lightpad_id, 50);
sleep(600);
lightpad_off($lightpad_id);
lightpad_glow_force($lightpad_id, 1, 30000, 255, 0, 0, 0);
echo lightpad_logical_load_metrics($lightpad_id);
*/
?>