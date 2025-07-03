<?php

class MultiColumnVerticalCheckBox extends VerticalCheckBox {
    
    function __construct($id, $name, $value, $options, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $options, $validations);
    }
    
    function render(){
        $html = "";
        $rows = $this->options;
        $nPerCol = ceil(count($rows)/3);
		$remainder = count($rows) % 3;
		if($remainder == 0){
			$j = $nPerCol;
			$k = $nPerCol*2;
			$jEnd = $nPerCol*2;
			$kEnd = $nPerCol*3;
		}
		else if($remainder == 1){
			$j = $nPerCol;
			$k = $nPerCol*2 - 1;
			$jEnd = $nPerCol*2 - 1;
			$kEnd = $nPerCol*3 - 2;
		}
		else if($remainder == 2){
			$j = $nPerCol;
			$k = $nPerCol*2;
			$jEnd = $nPerCol*2;
			$kEnd = $nPerCol*3 - 1;
		}
		for($i = 0; $i < $nPerCol; $i++){
			if(isset($rows[$i])){
				$col1[] = $rows[$i];
			}
			if(isset($rows[$j]) && $j < $jEnd){
				$col2[] = $rows[$j];
			}
			if(isset($rows[$k]) && $k < $kEnd){
				$col3[] = $rows[$k];
			}
			$j++;
			$k++;
		}
		
		$rows = array();
		$i = 0;
		foreach($col1 as $row){
			if(isset($col1[$i])){
				$rows[] = $col1[$i];
			}
			if(isset($col2[$i])){
				$rows[] = $col2[$i];
			}
			if(isset($col3[$i])){
				$rows[] = $col3[$i];
			}
			$i++;
		}
        
        $html .= "<table border='0' cellspacing='0' width='500'>
				<tr>\n";
        $i = 0;
        foreach($rows as $row){
            $checked = "";
            if(count($this->value) > 0){
                foreach($this->value as $value){
                    if($value == $row){
                        $checked = " checked";
                        break;
                    }
                }
            }
            if($i % 3 == 0){
				$html .= "</tr><tr>\n";
			}
            $html .= "<td style='padding:0;'><input {$this->renderAttr()} type='checkbox' name='{$this->id}[]' value='{$row}' $checked/>{$row}</td>";
            $i++;
        }
        $html .= "</table>";
        return $html;
    }
    
}


?>
