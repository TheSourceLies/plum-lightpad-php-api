<?PHP
//Open source api and examples for managing Plum Lightpads via PHP.

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

header('Content-type: text/javascript');

require_once('./plum_website_functions.php');
require_once('./plum_lightpad_functions.php');

//buildHomeConfig(); <--You need to run this once!

//Just some examples until I build a nice terminal / web front end.

$lightpad_id = '12345678-90ab-cdef-1234-567890abcdef';

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
?>