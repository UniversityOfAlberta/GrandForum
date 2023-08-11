<?php

    class AvoidResourcesAPI extends API{  


        function processParams($params){

        }

        function filter_CFN($json_obj){
            $newjson = array();
            for($x = 0; $x <= count($json_obj)-1; $x++){
                $result_obj = $json_obj[$x];
                if(strpos($result_obj["Categories"], 'CFN') !== false){
                    $newjson[] = $result_obj;
                }
            }
            return $newjson;
        }

        function callAPI($cat="CFN-ACT-EX-DANCE", $key=""){
            global $config;
            
            if($config->getValue('211Key') != ""){
                $long = -77.2922286;
                $lat = 45.1397821;
                $distance = 10000;
                if(isset($_GET['long']) && isset($_GET['lat'])){
                    $long = $_GET['long'];
                    $lat = $_GET['lat'];
                    $distance = 10;
                }
                $cacheKey = "$cat-$long-$lat";
                if($key == ""){
                    if(Cache::exists($cacheKey)){
                        return Cache::fetch($cacheKey);
                    }
                    $postData = array(
                            "Dataset"=>"on",
                            "Lang"=>"en",
                            "Search"=>"match",
                            "MatchMode"=>"category",
                            "MatchTerms"=>$cat,
                            "SearchType"=>"proximity",
                            "Distance"=>$distance,
                            "Latitude"=>$lat,
                            "Longitude"=>$long,
                            "SortOrder"=>"distance",
                            "PageIndex"=>0,
                            "PageSize"=>1000,
                            "Fields"=>"Eligibility,AgencyDescription,ParentAgency,PhysicalAddress1,EmailAddressMain,WebsiteAddress,Categories"
                    );
                }
                else{
                    $postData = array(
                        "Dataset"=>"on",
                        "Lang"=>"en",
                        "SearchType"=>"proximity",
                        "Distance"=>$distance,
                        "Latitude"=>$lat,
                        "Longitude"=>$long,
                        "SortOrder"=>"distance",
                        "PageIndex"=>0,
                        "PageSize"=>1000,
                        "Search"=>"term",
                        "Term"=> $key,
                        "Fields"=>"Eligibility,AgencyDescription,ParentAgency,PhysicalAddress1,EmailAddressMain,WebsiteAddress,Categories"
                    );
                }
                
                // Create the context for the request
                $context = stream_context_create(array(
                    'http' => array(
                        'method' => 'POST',
                        'header' => "Content-Type: application/json",
                        'content' => json_encode($postData)
                    )
                ));
                
                $response = @file_get_contents("https://data.211support.org/api/v2/search?key={$config->getValue('211Key')}", FALSE, $context);
            
                if($response === FALSE){
                    die('Error');
                }
                $responsedata = json_decode($response, TRUE);
                Cache::store($cacheKey, $responsedata["Records"]);
            }
            else{
                // Just open a spreadsheet
                $contents = array_filter(explode("\n", file_get_contents("extensions/Reporting/Report/SpecialPages/AVOID/PharmacyMap/programs.csv")));
                $data = array();
                $programs = array();
                foreach($contents as $row){
                    $row = str_getcsv($row);
                    $data[] = $row;
                    $cats = explode(";", $row[18]);
                    $category = $cats[count($cats)-1];
                    $website = (strstr($row[12], "http") === false) ? "http://{$row[12]}" : $row[12];
                    if(trim($cat) == trim($category)){
                        $programs[] = array(
                            "id"                        => md5($row[18].$row[0].$row[1]),
                            "PublicName"                => trim($row[0])." (".trim($row[1]).")",
                            "Description"               => $row[3],
                            "AgencyDescription"         => $row[3],
                            "Latitude"                  => $row[10],
                            "Longitude"                 => $row[11],
                            "PhoneNumbers"              => [["Phone" => $row[2], "Name" => "Office", "Description" => "", "Type" => ""]],
                            "Website"                   => $website,
                            "WebsiteAddress"            => $website,
                            "Email"                     => $row[13],
                            "Hours"                     => $row[5],
                            "PhysicalAddress1"          => $row[6],
                            "MailingAddressCity"        => $row[7],
                            "PhysicalAddressProvince"   => $row[8],
                            "PhysicalAddressCountry"    => "Canada",
                            "PhysicalAddressPostalCode" => $row[9],
                            "Categories"                => $row[18]
                        );
                    }
                }
                $responsedata = array("Records" => $programs);
            }
            
            return $responsedata["Records"];
        }
        

        function doAction($noEcho=false){
            global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config, $wgLang,$wgRequest,$wgOut, $wgMessage;
            //get user
            header("Content-type: text/json");
            $user = Person::newFromId($wgUser->getId());

            if(isset($_GET['cat'])){
                $cat = $_GET['cat'];
                $myJSON =json_encode($this->callAPI($cat));
            }
            elseif(isset($_GET['key'])){
                $key = $_GET['key'];
                $myJSON =$this->callAPI("", $key);
                $myJSON = json_encode($this->filter_CFN($myJSON));
            }
            else{ 
                $myJSON =json_encode($this->callAPI());
            }
            echo $myJSON;
            exit;
        }

        function isLoginRequired(){
            return true;
        }
    }
?>
