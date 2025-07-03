<?php

class UploadICSAPI extends API{

    var $structure = null;

    function __construct(){
        
    }

    function processParams($params){
        
    }
    
    /**
     * Creates a new Product if it doesn't already exist
     * @param Person $person The Person creating the product
     * @param array $paper The array containing the ics data for the Product
     * @return Product the new Product
     */
    function createMeeting($person, $event){
        $category = "Activity";
        $type = "Meeting";

        $checkProduct = Product::newFromCCVId(@$event['UID']);
        if($checkProduct->getId() != 0){
            // Make sure that this entry was not already entered
            return null;
        }

        if(is_array(@$event['DTSTART'])){
            $year = @substr("{$event['DTSTART'][0]['VALUE']}", 0, 4);
            $month = @substr("{$event['DTSTART'][0]['VALUE']}", 4, 2);
            $day = @substr("{$event['DTSTART'][0]['VALUE']}", 6, 2);
        }
        else{
            $year = @substr("{$event['DTSTART']}", 0, 4);
            $month = @substr("{$event['DTSTART']}", 4, 2);
            $day = @substr("{$event['DTSTART']}", 6, 2);
        }

        $structure = $this->structure['categories'][$category]['types'][$type];
        $product = new Product(array());
        $product->title = str_replace("&#39;", "'", @$event['SUMMARY']);
        $product->category = $category;
        $product->type = $type;
        $product->ccv_id = @$event['UID'];
        $product->date = "$year-$month-$day";
        $product->description = @$event['DESCRIPTION'];
        $product->data = array();
        $product->projects = array();
        $product->authors = array();
        if(!isset($_POST['id'])){
            $product->access_id = $person->getId();
        }
        else{
            $product->access_id = 0;
        }
        $product->access = "Forum";
        $attendees = @$event['ATTENDEE'];
        if(is_array($attendees)){
            foreach($attendees as $attendee){
                if(@$attendee['PARTSTAT'] == 'ACCEPTED' || @$attendee['PARTSTAT'] == 'NEEDS-ACTION'){
                    $p = Person::newFromNameLike(@str_replace('"', '', $attendee['CN']));
                    if($p == null || $p->getId() == 0){
                        $p = Person::newFromEmail(str_replace("mailto:", "", @$attendee['VALUE']));
                    }
                    if($p != null && $p->getId() != 0){
                        $product->authors[] = $p;
                    }
                    else{
                        $obj = new stdClass;
                        $obj->name = trim($attendee['CN']);
                        $obj->fullname = trim($attendee['CN']);
                        $product->authors[] = $obj;
                    }
                }
            }
        }
        $product->data['location'] = @$event['LOCATION'];
        $product->data['organizer'] = @$event['ORGANIZER'][0]['CN'];
        $product->data['status'] = @$event['STATUS'];
        $status = $product->create();
        if($status){
            $product = Product::newFromId($product->getId());
            return $product;
        }
        else{
            return null;
        }
    }

    function doAction($noEcho=false){
        global $wgMessage;
        $me = Person::newFromWgUser();
        if(isset($_POST['id']) && $me->isRoleAtLeast(MANAGER)){
            $person = Person::newFromId($_POST['id']);
        }
        else{
            $person = $me;
        }
        $ics = $_FILES['ics'];
        if($ics['type'] == 'text/calendar' && $ics['size'] > 0){
            $createdProducts = array();
            $errorProducts = array();
            $json = array('created' => array(),
                          'error' => array());
            $this->structure = Product::structure();
            $file_contents = file_get_contents($ics['tmp_name']);
            $data = icsToArray($file_contents);
            foreach($data as $event){
                $product = $this->createMeeting($person, $event);
                if($product != null){
                    $createdProducts[] = $product;
                }
                else{
                    $errorProducts[] = $event;
                }
            }
            foreach($createdProducts as $product){
                $json['created'][] = $product->toArray();
            }
            foreach($errorProducts as $product){
                $json['error'][] = $product;
            }
            $obj = json_encode($json);
            echo <<<EOF
            <html>
                <head>
                    <script type='text/javascript'>
                        parent.icsUploaded($obj, "");
                    </script>
                </head>
            </html>
EOF;
            exit;
        }
        else{
            echo <<<EOF
            <html>
                <head>
                    <script type='text/javascript'>
                        parent.icsUploaded([], "The uploaded file was not in ICS format");
                    </script>
                </head>
            </html>
EOF;
            exit;
        }
    }
    
    function isLoginRequired(){
        return true;
    }
}
?>
