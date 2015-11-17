<?php
    class UploadGrantXlsAPI extends API{
	var $grants = array();
	function processParams($params){
	    //TODO
	}

	function formatDate($date){
            $array = explode("/", $date);
            return "{$array[2]}-{$array[0]}-{$array[1]} 00:00:00";
    	}

	function setGrantInfo($xls_content, $xls2_content){
            $data = $xls_content;
            $flag = false;
            $grantArray1 = array();
            $linecount = 1;
            $array = array();
            foreach($data as $lines){
                if (preg_match('/Grants Life Cycle/', $lines) && $linecount >5){
                    $flag = true;
                    $count = 0;
                    $row = array();
            	}
           	elseif($linecount ==5){
              	    preg_match_all('/\<th\>(.+?)\<\/th\>/',$lines, $array);
                    unset($array[1][0]);
            	}
            	if($flag){
               	    $row[$array[1][$count+1]] = str_replace(array("<td>","</td>"), "",trim($lines));
                    $count++;
                    if($count > 10){
                 	$flag = false;
                  	$grantArray1[] = $row;
              	    }
            	}	
            	$linecount++;
	    }
      	    $data = $xls2_content;
            $flag = false;
            $linecount = 1;
            $array = array();
            $projectId = "";
            $grantArray2 = array();
            $ELEMENTS = array("Project ID", "Sponsor", "Holder", "Award Begin Date", "Role");
            foreach($data as $lines){
                if (preg_match('/Grants Life Cycle/', $lines) && $linecount >5){
                    $flag = true;
                    $count = 0;
                    $row = array();
                }	
                elseif($linecount ==5){
                    preg_match_all('/\<th\>(.+?)\<\/th\>/',$lines, $array);
                    unset($array[1][0]);
                }
                if($flag){
                    if(in_array($array[1][$count+1], $ELEMENTS)){
               	        $row[$array[1][$count+1]] = str_replace(array("<td>","</td>"), "",trim($lines));
                    }
                    $count++;
                    if($count > 17){
                        $flag = false;
                        $grantArray2[] = $row;
                    }
                }
                $linecount++;
	    }
    	    $finalarray = array();
    	    foreach($grantArray1 as $element){
        	foreach($grantArray2 as $element2){
           	    if($element['Project ID'] == $element2['Project ID']){
               		$finalarray[] = array_merge($element2, $element);
               		break;
           	    }
        	}
    	    }
	    $this->grants = $finalarray;
	    return $this->grants[0]['Total Award'];
	}

	function createGrantInfo($person, $grants){
	    foreach($grants as $grant){
		$grant['Award Begin Date'] = $this->formatDate($grant['Award Begin Date']);		
		$grant['Award End Date'] = $this->formatDate($grant['Award End Date']);
		$contribution = new Contribution(array());
		$contribution->name = $grant['Description'];
		$contribution->project_id = $grant['Project ID'];
		$contribution->description = $grant['Title'];
		$contribution->start_date = $grant['Award Begin Date'];
		$contribution->end_date = $grant['Award End Date'];
	        if($grant['Role'] == "Principal Investigator"){
		    $contribution->pi = $person->getId();
		}
		$contribution->people = array($person->getId());
		$partner = new Partner(array());
		$partner->organization = $grant['Sponsor'];
		$contribution->partners = array($partner);
		$id = md5(serialize($partner));
		$contribution->type = array("$id"=>'cash');
		$contribution->subtype = array("$id"=>'cash');
		$contribution->cash = array("$id"=>str_replace(array(',','$','.'), "", $grant['Total Award']));
		$contribution->kind = array("$id"=>0);
		$contribution->unknown = array("$id"=>0);
		$contribution->create();
	    }
	   return "YES";
	}

	function checkXlsFile($xls){
            $data = explode("\n", file_get_contents($xls['tmp_name']));
            $flag = false;
            $grantArray1 = array();
            $linecount = 1;
            $array = array();
            foreach($data as $lines){
                if (preg_match('/Grants Life Cycle/', $lines) && $linecount >5){
                    $flag = true;
                    $count = 0;
                    $row = array();
                }
                elseif($linecount ==5){
                    preg_match_all('/\<th\>(.+?)\<\/th\>/',$lines, $array);
                    unset($array[1][0]);
                }
                if($flag){
                    $row[$array[1][$count+1]] = str_replace(array("<td>","</td>"), "",trim($lines));
                    $count++;
                    if($count > 10){
                        $flag = false;
                        $grantArray1[] = $row;
                    }
                }   
            $linecount++;
            }
            if(isset($grantArray1[0]['Percent Spent'])){
		return true;
            }
	    return false;
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
	    $xls = $_FILES['grant'];
	    $xls2 = $_FILES['grant2'];
	    $hi = "nope";
	    if((isset($xls['type']) && isset($xls2['type'])) &&
	        $xls['type'] == "application/vnd.ms-excel" &&
                $xls2['type'] == "application/vnd.ms-excel" &&
	        $xls['size'] > 0 &&
	        $xls2['size'] > 0){
		if($this->checkXlsFile($xls)){
		    $xls = $_FILES['grant2'];
		    $xls2 = $_FILES['grant'];
		}
                $xls_content = explode("\n", file_get_contents($xls['tmp_name']));
		$xls2_content = explode("\n", file_get_contents($xls2['tmp_name']));
		$hi = $this->setGrantInfo($xls_content, $xls2_content);
		if(count($this->grants) >0){
		    $hi = $this->createGrantInfo($person, $this->grants);
		}
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
            echo <<<EOF
            <html>
                <head>
                    <script type='text/javascript'>
                                            parent.ccvUploaded([], "The uploaded files were not in .xls format");
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
