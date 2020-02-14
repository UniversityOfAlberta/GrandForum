<?php
require_once('commandLine.inc');
global $wgUser;

$wgUser = User::newFromId(1);
$user = Person::newFromEmail("mrcross@alaska.edu");
$sop =  SOP::newFromUserId($user->getId());
$gsms_data = GsmsData::newFromUserId($user->getId()); 
$gsms_id = $gsms_data->gsms_id;
$url = "https://gars.ualberta.ca/ois/new/";
$ccid = explode("@", $user->getEmail());
$ccid = @$ccid[0];
//set POST variables
$fields = array(
               'ccid' => $ccid,
               'sop' => array(implode("\n", $sop->getContent()))
                );
//url-ify the data for the POST
$fields_string = json_encode($fields);

//open connection
$ch = curl_init();
//set the url, number of POST vars, POST data
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($fields_string))
                    );
//execute post
$result = curl_exec($ch);

//close connection
curl_close($ch);
$response = json_decode($result);
print($response->link);
if($response !== false && $response != null){
    $url = $response->link;
    $token = $response->applicantToken;
    $gsms_data->ois_id = $token;
    $gsms_data->update();
}
?>
