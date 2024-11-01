<?php

    class AISearchAPI extends RESTAPI{  

        function doGET(){
            $this->throwError("Use POST instead", 500);
        }
        
        function doPOST(){
            $data = array(
                "queryText" => $_POST['queryText']
            );
            $json = json_encode($data);
            $url = 'http://129.128.215.129:5000/nlp_query';
            $ch = curl_init($url);
             
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json)
            ));
            
            $response = curl_exec($ch);
            header('Content-Type: application/json');
            echo $response;
            exit;
        }
        
        function doPUT(){
            return $this->doPUT();
        }
        
        function doDELETE(){
            return $this->doDELETE();
        }

        function isLoginRequired(){
            return false;
        }
    }
    
?>
