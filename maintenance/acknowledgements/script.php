<?php

require_once( '../commandLine.inc' );

echo "Creating Tables\n";
DBFunctions::execSQL("DROP TABLE IF EXISTS `grand_acknowledgements`", true);
DBFunctions::execSQL("CREATE TABLE IF NOT EXISTS `grand_acknowledgements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(256) NOT NULL,
  `university` varchar(256) NOT NULL,
  `date` varchar(256) NOT NULL,
  `supervisor` varchar(256) NOT NULL,
  `md5` varchar(256) NOT NULL,
  `pdf` longblob NOT NULL,
  `uploaded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `md5` (`md5`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1", true);
echo "\tDone!\n\n";

echo "Processing HQP.csv ";
$string = file_get_contents("HQP.csv");
$i = 0;
$exploded = explode("\n", $string);
foreach($exploded as $line){
    $split = str_getcsv($line, ",", "\"");
    if(count($split) == 5){
        $person = Person::newFromNameLike($split[0]);
        $name = addslashes($split[0]);
        $university = addslashes($split[1]);
        $date = addslashes($split[2]);
        $pdf = file_get_contents('Individual Files/HQP/'.$split[3].'.pdf');
        $md5 = md5($pdf);
        $pdf = mysql_real_escape_string($pdf);
        $supervisor = addslashes($split[4]);
        
        if($person == null || $person->getName() == ""){
            $id = -1;
        }
        else{
            $id = $person->getId();
        }
        $sql = "INSERT INTO `grand_acknowledgements`
                       (`user_id`, `user_name`, `university` , `date` ,`supervisor`,  `md5`,  `pdf`)
                VALUES ('$id'    , '$name'    , '$university', '$date','$supervisor', '$md5', '$pdf')";
        DBFunctions::execSQL($sql, true);
    }
    $i++;
    if($i % round(count($exploded)/100) == 0){
        echo ".";
    }
}
echo "!\nDone!\n\n";

echo "Processing NI.csv  ";
$string = file_get_contents("NI.csv");
$i = 0;
$exploded = explode("\n", $string);
foreach($exploded as $line){
    $split = str_getcsv($line, ",", "\"");
    if(count($split) == 4){
        $person = Person::newFromNameLike($split[0]);
        $name = addslashes($split[0]);
        $university = addslashes($split[1]);
        $date = addslashes($split[2]);
        $pdf = file_get_contents('Individual Files/NI/'.$split[3].'.pdf');
        $md5 = md5($pdf);
        $pdf = mysql_real_escape_string($pdf);
        
        if($person == null || $person->getName() == ""){
            $id = -1;
        }
        else{
            $id = $person->getId();
        }
        $sql = "INSERT INTO `grand_acknowledgements`
                       (`user_id`, `user_name`, `university` , `date` ,`supervisor`,  `md5`,  `pdf`)
                VALUES ('$id'    , '$name'    , '$university', '$date','', '$md5', '$pdf')";
        DBFunctions::execSQL($sql, true);
    }
    $i++;
    if($i % ceil(count($exploded)/100) == 0){
        echo ".";
    }
}
echo "!\nDone!\n\n";
flush();

?>
