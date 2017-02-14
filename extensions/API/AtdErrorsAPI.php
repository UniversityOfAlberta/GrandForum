<?php

class AtdErrorsAPI extends API{

    function AtdErrorsAPI(){
    }

    function processParams($params){

    }

    function encode_css($string) {
        $quoted = rawurlencode($string);
        $out = "";
        for ($i = 0, $n = 0; $i < strlen($quoted); $i += LENGTH, $n++) {
                $out .= "#c" . $n . "{background:url(" . PREFIX . substr($quoted, $i, LENGTH) . ");}\n";
        }
        return $out;
    }

    function doAction($noEcho=false){
//        $content = preg_replace('/[^A-Za-z.,\']/', ' ',utf8_encode(htmlspecialchars_decode(trim(file_get_contents('php://input')), ENT_QUOTES)));
        $content = trim(file_get_contents('php://input'));
        $encode = @$_GET['encode'];
        $curl_url = "http://162.246.157.115/checkDocument";
        $curl_post_fields_array = array('data'=> $content);
        $fields_string = "";
        foreach($curl_post_fields_array as $key=>$value) {
                $fields_string .= $key.'='.$value.'&';
        }
        rtrim($fields_string, '&');
        $curl_header = array('Content-type: application/x-www-form-urlencoded');
        $curl_array = array(
            CURLOPT_URL => $curl_url,
            CURLOPT_HTTPHEADER => $curl_header,
            CURLOPT_POSTFIELDS =>$fields_string,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => "cmput401:tasha"
        );
        $curl = curl_init();
        curl_setopt_array($curl, $curl_array);
        $data = curl_exec($curl);
        $result = '';
        if ($error = curl_error($curl)){
            $result = $error;
        }
        curl_close($curl);
        if(empty($error)){
            $result = $data;
        }
        if($encode){
            echo $this->encode_css($result);
        }
        echo $result;
   }

   function isLoginRequired(){
       return true;
   }
}
?>
