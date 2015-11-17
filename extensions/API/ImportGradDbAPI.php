<?php

class ImportGradDbAPI extends API{
    var $generalInfo = array();
    var $individualInfo = array();
 
    function processParams($params){
    }
	
    function setGradDbInfo($person, $data){
	$url = $data;
    	$regex = '/Graduated\<\/th\>\<\/tr\>\<\/tr\>\<\/tr\>(.+?)\<\/table\>/';
      	//only taking table that has info in it
    	preg_match_all($regex, $url, $url);
      	//take each row
    	$regex = '/\<tr\>(.+?)\<\/tr\>/';
    	preg_match_all($regex, $url[1][0], $Array);
    	$rows = $Array[1];
    	$students = array();
    	foreach($rows as $row){
            $regex = '/\<td(.+?)\>(.+?)\\/td\>/';
            preg_match_all($regex, $row, $Array);
            $studentrow = $Array[2];
            $students[] = $studentrow;
    	}
        $keys = array('id','name','program','awards','role','start_date','end_date','graduated');
        $parsed_array = array();
        foreach($students as $student){
            $data = array();
            $i = 0;
            $supervisorsArray = array();
            foreach($student as $info){
            	if($keys[$i] == 'program'){
                    $data[$keys[$i]] = str_replace("<","", $info)." Student";
                }
                else{
                     $data[$keys[$i]] = str_replace("<","",$info);
                     if($keys[$i] == 'graduated'){
                         $supervisorsArray[] = array('name'=>$person->getRealName(), 'type'=>$data['role'], 'start_date'=>$data['start_date'], 'end_date'=>$data['end_date']);
                         $data['supervisors'] = $supervisorsArray;
                     }
            	}
                $i++;
            }
            $parsed_array[] = $data;
        }
        foreach($parsed_array as $student){
	    return "hii";
	    $this->generalInfo[] = $student;
    	}
	return "hmm";
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
	$hi = "hello";
	if($_POST['login'] != "" && $_POST['password'] != ""){
	    $hi = $_POST['password'];
            $content  = file_get_contents("https://graddb.cs.ualberta.ca/Prod/FECrep.cgi?oracle.login={$_POST['login']}&oracle.password={$_POST['password']}&button=View%20Report");
	    //$hi = $this->setGradDbInfo($person, $content);
            
            $contents  = explode("\n",file_get_contents("https://graddb.cs.ualberta.ca/Prod/FECrep.cgi?oracle.login={$_POST['login']}&oracle.password={$_POST['password']}&button=View%20Report"));
	    $hi = $contents[82];

	    echo <<<EOF
            <html>
                <head>
                    <script type='text/javascript'>
                                            parent.ccvUploaded([], "$hi");

		    </script>
                </head>
            </html>
EOF;
            exit;
	}
	else{
	    $hi = "Please provide both a username and password.";
            echo <<<EOF
            <html>
                <head>
                    <script type='text/javascript'>
                                            parent.ccvUploaded([], "$hi");

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
