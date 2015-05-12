<?php

class EvalOptAssignmentReportItem extends AbstractReportItem {
	
	function render(){
		global $wgOut;
   
        $reviewer_id = $this->getReport()->person->getId();
        $opt_id = $this->getAttr("opt_id", "0");
        $assign_type = $this->getAttr("assign_type", "");
        $year = REPORTING_YEAR;
        
        if($opt_id == $reviewer_id && ($assign_type == "NI")){
            $output = $this->processCData("");
		    $wgOut->addHTML($output);
		    return;
        }
        $sql = "SELECT * FROM grand_eval WHERE user_id=%d AND sub_id=%d AND type='%s' AND year=%d";
        $select_q = sprintf($sql, $reviewer_id, $opt_id, $assign_type, $year);

        $checked = '';
        $result = DBFunctions::execSQL($select_q);
        if(count($result)>0){
            $checked = 'checked="checked"';
        }      

$output =<<<EOF
            <input type="checkbox" value="{$opt_id}" name="{$this->getPostId()}" {$checked} />
EOF;
        $output = $this->processCData("{$output}");
		$wgOut->addHTML($output);
	}

    function save(){
        $reviewer_id = $this->getReport()->person->getId();
        $opt_id = $this->getAttr("opt_id", "0");
        $assign_type = $this->getAttr("assign_type", "");
        $year = REPORTING_YEAR;
        
        $select_q = sprintf("SELECT * FROM grand_eval WHERE user_id=%d AND sub_id=%d AND type='%s' AND year=%d", 
                            $reviewer_id, $opt_id, $assign_type, $year);
        $sel_res = DBFunctions::execSQL($select_q);

        if(count($sel_res)>0){
            if(!isset($_POST[$this->getPostId()])){
                $sql = "DELETE FROM grand_eval WHERE user_id=%d AND sub_id=%d AND type='%s' AND year=%d";
                $delete_q = sprintf($sql, $reviewer_id, $opt_id, $assign_type, $year);
                $result = DBFunctions::execSQL($delete_q, true);
            }
        }
        else{
            if(isset($_POST[$this->getPostId()]) && $opt_id == $_POST[$this->getPostId()]){
                $sql = "INSERT INTO grand_eval(user_id, sub_id, type, year) VALUES(%d, %d, '%s', %d)";
                $insert_q = sprintf($sql, $reviewer_id, $opt_id, $assign_type, $year);
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
