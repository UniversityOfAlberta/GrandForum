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
            if($key == ""){
                if(Cache::exists($cat)){
                    return Cache::fetch($cat);
                }
                $postData = array(
                        "Dataset"=>"on",
                        "Lang"=>"en",
                        "Search"=>"match",
                        "MatchMode"=>"category",
                        "MatchTerms"=>$cat,
                        "SearchType"=>"proximity",
                        "Distance"=>10000,
                        "Latitude"=>45.1397821,
                        "Longitude"=>-77.2922286,
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
                    "Distance"=>10000,
                    "Latitude"=>45.1397821,
                    "Longitude"=>-77.2922286,
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
            Cache::store($cat, $responsedata["Records"]);
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
