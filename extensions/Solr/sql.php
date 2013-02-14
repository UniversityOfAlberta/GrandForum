 <?php 

$table = $_GET['table'];
$id = explode(",",$_GET['id']); // 0:field_name  1:key
$fields = $_GET['fields'];

// split fields by ','
$bits = explode(",", $fields);

// if .blob, add CAST(CHARS....) as ...
$sub = ".blob";
foreach ($bits as &$bit){
  if (substr( $bit, strlen( $bit ) - strlen( $sub ) ) == $sub){ // ends with
    $label = substr($bit, 0, -5);
    $bit = "CAST(".$label." AS CHAR(10000) CHARACTER SET utf8) as ".$label;
  } 
}
unset($bit);


// SQL query
$query = "SELECT ".implode(", ", $bits)." FROM ".$table." WHERE ".$id[0]."=".$id[1];
//print $query;

// Connect to DB
mysql_connect("127.0.0.1", "mhuggett", "ifeelGRAND") or die(mysql_error()); 

mysql_select_db("grand_giga_test") or die(mysql_error()); 


$data = mysql_query($query) 
or die(mysql_error()); 

print json_encode(mysql_fetch_array($data));


//Print "<table border cellpadding=3>"; 
//while($info = mysql_fetch_array( $data )) { 
//	Print "<tr>"; 
//	Print "<th>Name:</th> <td>".$info['name'] . "</td> "; 
//	Print "<th>Color:</th> <td>".$info['fav_color'] . "</td> "; 
//	Print "<th>Food:</th> <td>".$info['fav_food'] . "</td> "; 
//	Print "<th>Pet:</th> <td>".$info['pet'] . " </td></tr>"; 
//} 
//Print "</table>"; 
 ?> 
