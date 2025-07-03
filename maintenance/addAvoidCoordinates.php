<?php

require_once('commandLine.inc');

$data = DBFunctions::select(array('grand_avoid_resources'),
                            array('*'));
                            
foreach($data as $row){
    echo "{$row['PhysicalAddress1']} {$row['PhysicalCity']} {$row['PhysicalStateProvince']} ... ";
    $json = json_decode(@file_get_contents("https://api.geoapify.com/v1/geocode/search?text=".urlencode("{$row['PhysicalAddress1']} {$row['PhysicalCity']} {$row['PhysicalStateProvince']}")."&apiKey={$config->getValue('geoapifyAPI')}"));
    if(isset($json->features[0])){
        DBFunctions::update('grand_avoid_resources',
                            array('lat' => $json->features[0]->properties->lat,
                                  'lon' => $json->features[0]->properties->lon),
                            array('id' => $row['id']));
        echo "LAT: {$json->features[0]->properties->lat}, LON: {$json->features[0]->properties->lon}";
    }
    else{
        echo "Not Found";
    }
    echo "\n";
}

?>
