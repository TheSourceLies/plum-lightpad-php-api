<?php
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

$wait_for_this_many_lightpads_to_be_found = 3;//Continue running until you find this many lightpads.  Set to a high value to run forever.

$server_adapter_ip = "0.0.0.0";//IP or 0.0.0.0 to listen on all interfaces.
$lightpad_udp_heartbeat_port = 43770;//Port the lightpads use to broadcast UDP status.  Lightpads send out a heartbeat once every ~5 minutes.
$buffer_length = strlen("PLUM 8888 12345678-90ab-cdef-1234-567890abcdef 8443");//Set the buffer length to what we expect to receive from a lightpad.
$lightpad_config_file = "./lightpad_config.json";//Path to output the lightpad_config.json file.

if(!($sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)))//Create UDP IPV4 socket.
{
	$error_code = socket_last_error();//Capture errors if any.
	$error_message = socket_strerror($error_code);//Convert error to string for output.
	
	die("Couldn't create socket: [$error_code] $error_message \n");//Exit, error creating socket.
}
 
echo "UDP listening socket created. \n";

if(!socket_bind($sock, $server_adapter_ip, $lightpad_udp_heartbeat_port))// Bind socket to all adapters, port.
{
	$error_code = socket_last_error();//Capture errors if any.
	$error_message = socket_strerror($error_code);//Convert error to string for output.
	
	die("Could not bind socket : [$error_code] $error_message \n");//Unable to bind socket, might be in use by another process.
}

echo "UDP listenting socket bound to ".$server_adapter_ip." / ".$lightpad_udp_heartbeat_port.". \n";

socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);//The SO_REUSEADDR socket option allows a socket to forcibly bind to a port in use by another socket.
socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);//Socket semantics require that an application set the SO_BROADCAST option on before attempting to send a datagram to a base or broadcast address. This protects the application from accidentally sending a datagram to many systems.
socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>360,"usec"=>0));//Set a 6 minute timeout to avoid a complete hang if there is not lightpad traffic.

//socket_sendto($sock, "PLUM", strlen("PLUM"), 0, '255.255.255.255', 43770);

//Example Message: PLUM 8888 12345678-90ab-cdef-1234-567890abcdef 8443

$lightpads = array();

echo "\n\nThis script will run until ".$wait_for_this_many_lightpads_to_be_found." lightpads are found, edit config to find more or less.\n";
echo "Typically lightpads send out a UDP heartbeat with their information once every 5 minutes.\nPlease wait at least that long before giving up.\n";
echo "If you don't detect any lightpads ensure you are on the same wireless network and have good wireless connectivity to all switches.\n\n";


while(sizeof($lightpads) < $wait_for_this_many_lightpads_to_be_found)//Run forever.
{
    echo "Waiting for data ... \n";
    
    $r = socket_recvfrom($sock, $buffer, $buffer_length, 0, $remote_ip, $remote_port);//Wait for data.
    $lightpad_data = explode(" ", $buffer);//Split the result into an array, should have 4 parts, PLUM, 8888, <lightpad_id>, <destination_port>
	if(sizeof($lightpad_data) === 4)//If the message splits into exactly 4 parts, not the best way to confirm a plum packet.
	{
		$lightpad = array(
			'port' => (int)$lightpad_data[3],
			'ip' => $remote_ip,
			'lastUpdate' => time()
		);//Set a new lightpad variable with the port, ip and a unix timestamp of when we received the data.
		
		if(isset($lightpads[$lightpad_data[2]]))//If we already found this lightpad before and have it stored in $lightpads[] array.
		{
			$lightpads[$lightpad_data[2]] = $lightpad;//Update the data in $lightpads, just in case the IP changed and to refresh the timestamp.
			echo "Updated Lightpad: ".$lightpad_data[2]." - ".$lightpad['ip']."\n";
		}
		else//If we havn't found this lightpad before.
		{
			$lightpads[$lightpad_data[2]] = $lightpad;//Add it to the $lightpad[] array.
			echo "Added Lightpad: ".$lightpad_data[2]." - ".$lightpad['ip']."\n";
		}
		
		$line_length = 82;//Used to add the ----- in the output.
		echo str_repeat("-", $line_length)."\n";//Echo a long line of -----.
		echo "Total Lightpads: ".sizeof($lightpads).", Looking For: ".($wait_for_this_many_lightpads_to_be_found - sizeof($lightpads))." More\n";//Output how many lightpads we have found.
		echo str_repeat("-", $line_length)."\n";//Echo a long line of -----.
		
		foreach($lightpads as $key => $value)//Scroll through every lightpad we've already found.
		{
			echo "IP: ".$value['ip'].", ID: ".$key.", Updated: ".$value['lastUpdate']."\n";//Echo out the IP, ID and last updated timestamp.
		}
		echo str_repeat("-", $line_length)."\n\n";//Echo a long line of -----.
		
		file_put_contents($lightpad_config_file, json_encode($lightpads, JSON_PRETTY_PRINT));//Write out the $lightpadsp[] array to the config file in JSON format, with pretty (easy to read) formatting.
	}
}

if(sizeof($lightpads) == $wait_for_this_many_lightpads_to_be_found)//Check and see if we found all the lightpads we were looking for.
{
	echo "\n\nAll lightpads found!\n\n";
}
else
{
	echo "\n\nScript timed out due to lack of lightpad data, check network connectivity and try again.\n\n";
}

socket_close($sock);//All done, close the socket.
?>