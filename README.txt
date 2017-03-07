/*********************************************************************************************
All scripts tested in Windows 10 64-bit while running with PHP 5.6.24.

Getting Started:
1)	You'll probably want a WAMP / LAMP stack installed, which falls outside of this guide but
	I recommend using XAMPP for beginners running on a windows box.
	https://www.apachefriends.org/index.html
2)	You need to have extension=php_sockets.dll enabled in php.ini
3)	Edit the following config files to match your environment:
		plum_username_and_password.php - Update the $plum_account_email and $plum_account_password
			variables with the login information to plum.  These get added together and
			hashed using base64_encode before getting sent over HTTPS to plum.
		lightpad_scanner_and_config_builder.php - Update $wait_for_this_many_lightpads_to_be_found to
			however many lightpads you have installed in your house.
4)	Run lightpad_scanner_and_config_builder.php to detect all of your installed lightpads.
	This will generate a file called lightpad_config.json in the same directory as the script.
	You can alther the file path at the top of the php.  Ensure the file is writable by php.
	Example: d:\Dropbox\Programming\plum>"c:\xampp\php\php.exe" temp_socket_listener.php
5)	Run build_home_config() from plum_website_functions.php to generate the home_config.json
	file.
6)	Test - run some functions from plum_lightpad_functions.. more on this later.



*********************************************************************************************/