<?php

require_once('commandLine.inc');

$wgUser = User::newFromId(1);

$people = Person::getAllPeople();

$date = date('Y-m-d', time() - 3600*24*7*2);

function executeFitBitAPI($url, $person){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: Bearer {$person->getExtra('fitbit')}"
    ));
    
    //execute post
    $result = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return json_decode($result, true);
}

foreach($people as $person){
    if($person->getExtra('fitbit') != "" && time() < $person->getExtra('fitbit_expires')){
        DBFunctions::delete('grand_fitbit_data',
                            array('user_id' => $person->getId(),
                                  'date' => "{$date}"));
        
        echo "{$person->getName()}\n";
        
        $steps = 0;
        $distance = 0;
        $active = 0;
        $sleep = 0;
        $water = 0;
        $fibre = 0;
        $protein = 0;
        
        // Steps
        $data = executeFitBitAPI("https://api.fitbit.com/1/user/-/activities/steps/date/{$date}/{$date}.json", $person);
        $steps = 0;
        foreach($data['activities-steps'] as $value){
            $steps += intval($value['value']);
        }
        
        // Distance
        $data = executeFitBitAPI("https://api.fitbit.com/1/user/-/activities/distance/date/{$date}/{$date}.json", $person);
        foreach($data['activities-distance'] as $value){
            $distance += intval($value['value']);
        }
        
        // Minutes Active
        $data1 = executeFitBitAPI("https://api.fitbit.com/1/user/-/activities/minutesLightlyActive/date/{$date}/{$date}.json", $person);
        $data2 = executeFitBitAPI("https://api.fitbit.com/1/user/-/activities/minutesFairlyActive/date/{$date}/{$date}.json", $person);
        $data3 = executeFitBitAPI("https://api.fitbit.com/1/user/-/activities/minutesVeryActive/date/{$date}/{$date}.json", $person);
        foreach(array_merge($data1['activities-minutesLightlyActive'], 
                            $data2['activities-minutesFairlyActive'],
                            $data3['activities-minutesVeryActive']) as $value){
            $active += intval($value['value']);
        }
        
        // Sleep
        $data = executeFitBitAPI("https://api.fitbit.com/1.2/user/-/sleep/date/{$date}/{$date}.json", $person);
        foreach($data['sleep'] as $value){
            $sleep += intval($value['duration']);
        }
        
        // Water
        $data = executeFitBitAPI("https://api.fitbit.com/1/user/-/foods/log/water/date/{$date}/{$date}.json", $person);
        foreach($data['foods-log-water'] as $value){
            $water += intval($value['value']);
        }
        
        // Fibre & Protein
        $data = executeFitBitAPI("https://api.fitbit.com/1/user/-/foods/log/date/{$date}.json", $person);
        $fibre = floatval($data['summary']['fiber']);
        $protein = floatval($data['summary']['protein']);
        
        DBFunctions::insert('grand_fitbit_data',
                            array('user_id' => $person->getId(),
                                  'date' => $date,
                                  'steps' => $steps,
                                  'distance' => $distance,
                                  'active' => $active,
                                  'sleep' => $sleep,
                                  'water' => $water,
                                  'fibre' => $fibre,
                                  'protein' => $protein));
    }
}

?>
