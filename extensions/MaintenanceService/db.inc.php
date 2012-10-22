<?php
/*  PostgreSQL  */
/*$host = 'localhost';
//$user_db = 'postgres';
//$pass_db = 'dieguinho';
//$name_db = 'postgres';

//$conn = pg_connect("host=$host dbname=$name_db user=$user_db password=$pass_db")
    or die('Could not connect: ' . pg_last_error());
*/

/*  Information for the Database */
global $wgDBserver, $wgDBuser, $wgDBpassword, $wgDBname;

$host = $wgDBserver;
$user_db = $wgDBuser;
$pass_db = $wgDBpassword;
$name_db = $wgDBname;

$conn = mysql_connect($host,$user_db,$pass_db)
	or die ("Connection Fail");

$db = mysql_select_db($name_db , $conn)
	or die("Connection Fail");
?>
