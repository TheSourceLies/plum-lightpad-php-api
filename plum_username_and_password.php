<?PHP

$plum_account_email = "nobody@email.com"; //Your plum account email address.
$plum_account_password = "badPassword123"; //Your plum account password.
$authorization_base64 = base64_encode($plum_account_email.':'.$plum_account_password); //Base 64 encoded email:password

?>