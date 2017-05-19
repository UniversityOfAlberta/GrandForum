<?php
    require_once("excel_reader2.php");
    require_once("commandLine.inc");
    function formatDate($date){
	$array = explode("/", $date);
	return "{$array[2]}-{$array[0]}-{$array[1]}";
    }
    if(file_exists("ps1.xls")){
	$data = explode("\n", file_get_contents("ps1.xls"));
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
    }
    if(file_exists("ps2.xls")){
	$data = explode("\n", file_get_contents("ps2.xls"));
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
    //print_r($grantArray2);
    $finalarray = array();
    foreach($grantArray1 as $element){
	foreach($grantArray2 as $element2){
	   if($element['Project ID'] == $element2['Project ID']){
               $finalarray[] = array_merge($element2, $element);
	       break;
	   }
	}
    }
    print_r($finalarray);
/*    $count = 994;
    foreach($finalarray as $grant){
	$grant['Award Begin Date'] = formatDate($grant['Award Begin Date']);
        $grant['Award End Date'] = formatDate($grant['Award End Date']);
	$id = array("34");
	$a = explode(".",$grant['Total Award']);
	$grant['Total Award'] = $a[0]; 
	$sql = "INSERT INTO grand_contributions(id, project_id, name, users, description, start_date, end_date, access_id) VALUES
		($count,'{$grant['Project ID']}', '{$grant['Description']}','".serialize($id)."','".str_replace("'","&#39;",$grant['Title'])."','{$grant['Award Begin Date']} 00:00:00','{$grant['Award End Date']} 00:00:00', 34)";
	DBFunctions::execSQL($sql, true);

	$sql2 = "INSERT INTO grand_contributions_partners(contribution_id, partner, type, subtype, cash) VALUES ($count, '{$grant['Sponsor']}', 'cash', 'cash',".str_replace(array("$",".",","),"",$grant['Total Award']).")";
//	DBFunctions::execSQL($sql2,true);

	$count++;
	}	 
    
*/
    }
?>
