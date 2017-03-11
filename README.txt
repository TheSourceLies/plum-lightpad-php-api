/*********************************************************************************************
All scripts tested in Windows 10 64-bit while running with PHP 5.6.24 (XAMPP) or PHP 7.1.2

Getting Started:
1)	You'll probably want a WAMP / LAMP stack installed, which falls outside of this guide but
	I recommend using XAMPP for beginners running on a windows box.
	https://www.apachefriends.org/index.html
	
	At very least you need PHP installed.
	http://windows.php.net/download
	
2)	You need to have the following enabled in php.ini. (Remove the ; in front of them)
		extension=php_sockets.dll
		extension=php_curl.dll

3)	Edit the config file to match your environment:
		config.php
			Update the $plum_account_email and $plum_account_password variables with the 
			login information to plum.  These get added together and hashed using 
			base64_encode before getting sent over HTTPS to plum.
			
			Update $wait_for_this_many_lightpads_to_be_found to however many lightpads you 
			have installed in your house.

4)	Run both config builder scripts.
		config_builder_house_config.php
			This downloads all the information about your house and connected lightpads
			from the Plum web servers and puts it in a nice json format.
		
		config_builder_lightpad_config.php
			This monitors the network for lightpad heartbeats and writes their IP / port
			and ID to a file. Just a listener, you can set the 
			$wait_for_this_many_lightpads_to_be_found variable to 99 and it will 
			continually scan for IP changes / additions of lightpads and update the 
			config file accordingly.
		
		examples
			c:\php\php.exe config_builder_house_config.php
			c:\php\php.exe config_builder_lightpad_config.php
		
6)	Give it a test run.  Execute cycle_all_lights.php to verify everything is working
	correctly.
		example
			c:\php\php.exe cycle_all_lights.php
	
	Or watch the lightpad events in real time.
		example
			c:\php\php.exe plum_lightpad_stream.php
*********************************************************************************************/