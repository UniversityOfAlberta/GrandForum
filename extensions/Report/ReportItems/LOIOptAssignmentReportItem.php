<?php

class LOIOptAssignmentReportItem extends AbstractReportItem {
	
	function render(){
		global $wgOut;

        $loi_id = $this->projectId;
        $loi = LOI::newFromId($loi_id);
        $loi_name = $loi->getName();
            
        $reviewer_id = $this->personId;
        $assign_type = 'OPT_LOI';
        $year = REPORTING_YEAR;

        $sql = "SELECT * FROM grand_eval WHERE user_id=%d AND sub_id=%d AND type='%s' AND year=%d";
        $select_q = sprintf($sql, $reviewer_id, $loi_id, $assign_type, $year);

        $checked = '';
        $result = DBFunctions::execSQL($select_q);
        if(count($result)>0){
            $checked = 'checked="checked"';
        }      

//         $output =<<<EOF
//             <div><strong>{$loi_name}: </strong> <input type="checkbox" value="{$loi_id}" name="opt_loi[]" {$checked} /></div>
// EOF;

$output =<<<EOF
            <tr>
            <th align="left">{$loi_name}:</th>
            <td><input type="checkbox" value="{$loi_id}" name="opt_loi[]" {$checked} /></td>
            </tr>
EOF;
	    
        $output = $this->processCData("{$output}");
		$wgOut->addHTML($output);
	}

    function save(){
        $loi_id = $this->projectId;
        $reviewer_id = $this->personId;
        $assign_type = 'OPT_LOI';
        $year = REPORTING_YEAR;

        $select_q = sprintf("SELECT * FROM grand_eval WHERE user_id=%d AND sub_id=%d AND type='%s' AND year=%d", 
                            $reviewer_id, $loi_id, $assign_type, $year);
        $sel_res = DBFunctions::execSQL($select_q);

        if(count($sel_res)>0){
            if(isset($_POST['opt_loi']) && is_array($_POST['opt_loi']) && !in_array($this->projectId, $_POST['opt_loi'])){

                $sql = "DELETE FROM grand_eval WHERE user_id=%d AND sub_id=%d AND type='%s' AND year=%d";
                
                $delete_q = sprintf($sql, $reviewer_id, $loi_id, $assign_type, $year);
                $result = DBFunctions::execSQL($delete_q, true);         
                
            }
        }
        else{   
            if(isset($_POST['opt_loi']) && is_array($_POST['opt_loi']) && in_array($this->projectId, $_POST['opt_loi'])){

                $sql = "INSERT INTO grand_eval(user_id, sub_id, type, year) VALUES(%d, %d, '%s', %d)";
                
                $insert_q = sprintf($sql, $reviewer_id, $loi_id, $assign_type, $year);
                $result = DBFunctions::execSQL($insert_q, true);         
                
            }
        }


        return array();
    }

	// Overloading from AbstractReportItem Sets the Blob value for this item
    function setBlobValue($value){
       
    }

    function getBlobValue(){
        
    }    
	
	
	function renderForPDF(){
	    global $wgOut;
	    $item = "";
		$wgOut->addHTML($item);
	}
    
    // Checkboxes are optional so they don't count
    function getNComplete(){
        return 0;
    }
    function getNFields(){
        return 0;
    }

	
	
}

?>
