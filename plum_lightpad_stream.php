<?php
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
require_once('./config.php');
require_once('./plum_lightpad_functions.php');

if(!$lightpad_config || !$house_config)//Check to make sure both config files have been created.
{
	die('You must run both the lighpad and house config builders before monitoring output streams.');
}



//-------------------------------------------------------------------------------------------//
//------------------------These Functions Are Called During An Event-------------------------//
//-------------------------------------------------------------------------------------------//
function lightpad_motion($lightpad_information, $signal)//Triggered when motion is detected by a switch.
{
	echo "Motion Detected: [".$lightpad_information['house_name']." - ".$lightpad_information['room_name']." - ".$lightpad_information['logical_load_name']."]\n";

	//Example - If motion detected on downstairs or theatre switches, toggle the lights.
	//if($lightpad_information['logical_load_name'] == 'Downstairs' || $lightpad_information['logical_load_name'] == 'Theatre')
	//{
		//lightpad_off($lightpad_information['lightpad_id']);
		//sleep(1);
		//lightpad_on($lightpad_information['lightpad_id']);
	//}
}

function lightpad_dimmer($lightpad_information, $level)//Triggered when the dimmer is changed on a switch.
{
	echo "Dimmer Changed To ".round($level / 255 * 100)."%: [".$lightpad_information['house_name']." - ".$lightpad_information['room_name']." - ".$lightpad_information['logical_load_name']."]\n";
}

function lightpad_power($lightpad_information, $watts)//Triggered when the watt usage is changed on a switch.
{
	echo "Power Changed To ".$watts." Watts: [".$lightpad_information['house_name']." - ".$lightpad_information['room_name']." - ".$lightpad_information['logical_load_name']."]\n";
}
//-------------------------------------------------------------------------------------------//
//------------------------These Functions Are Called During An Event-------------------------//
//-------------------------------------------------------------------------------------------//






$sockets = [];//Setup the sockets array.
foreach($lightpad_config as $lightpad_id => $lightpad_info)//For every lightpad in the config.
{
	$sockets[$lightpad_id] = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);//Create a new TCP socket.

	if ($sockets[$lightpad_id] === false)//Check to make sure it was created correctly.
	{
		echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";//Spit out an error if not.
	}
	else
	{
		echo "Socket For: ".$lightpad_info->ip." Created.\n";//Or claim success.
	}

	$connect_result = socket_connect($sockets[$lightpad_id], $lightpad_info->ip, $stream_port);//Connect the socket to the stream port.
	if ($connect_result === false)//Check to make sure it connected.
	{
		echo "socket_connect() failed.\nReason: ($connect_result) " . socket_strerror(socket_last_error($socket)) . "\n";//Spit out an error if not.
	}
	else
	{
		echo "Socket For: ".$lightpad_info->ip." Connected.\n";//Or claim success.
	}

	socket_set_nonblock($sockets[$lightpad_id]);//Set non blocking of the socket so we can read without waiting when something does change.
}

echo "Listening for changes ...\n\n";
while(true)
{
    $read = $sockets;
	$write = null;
	$except = null;
	$timeout = null;
	$buffer = 2048;//Leave room for a lot of messages, max is usually at most 3.

	if(socket_select($read, $write, $except, $timeout) > 0);//Monitor all the connections to lightpads and wait until one has sent us a message.
	{
		foreach($sockets as $lightpad_id => $socket)//Check every lightpad.
		{
			$message = socket_read($socket, $buffer, PHP_BINARY_READ);//Check and see if there is a message waiting for us.
			if($message)//If there is a message...
			{
				$message = str_replace("\n", '', $message);//Remove newlines.
				$message = str_replace("\r", '', $message);//Remove carriage returns.
				$updates = explode(".", $message);//Set up an array and split on .
				array_pop($updates);//Get rid of the last empty entry in the array.
				$lightpad_information = lightpad_information($lightpad_id);//Pull all the information for this lightpad from the config.

				foreach($updates as $key => $update)//For every message from the lightpad
				{
					$data = json_decode($update);//Turn it into an object.
					if($data)//If it decoded properly.
					{
						switch($data->type)
						{
							case 'pirSignal'://Motion detection event.
								lightpad_motion($lightpad_information, $data->signal);//Call the lightpad_motion function to process it.
								break;
							case 'dimmerchange'://The dimmer level was changed.
								lightpad_dimmer($lightpad_information, $data->level);//Call the lightpad_dimmer function to process it.
								break;
							case 'power'://The power usage was changed.
								lightpad_power($lightpad_information, $data->watts);//Call the lightpad_power function to process it.
								break;
						}
					}
					else
					{
						echo "Message \"".$update."\" did not JSON decode!";//Junk or partial message. Discard.
					}
				}
			}
		}
	}
}

foreach($sockets as $lightpad_id => $socket)//Some cleanup that will never really get called at this point.
{
	echo "Closing socket...";
	socket_close($socket);
	echo "OK.\n\n";
}
?>
