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

$host = 'localhost';
$user_db = 'root';
$pass_db = 'g0l0vnD5';
$name_db = 'grand_tera';

$conn = mysql_connect($host,$user_db,$pass_db)
	or die ("Connection Fail 1");

$db = mysql_select_db($name_db , $conn)
	or die("Connection Fail 2");
?>
