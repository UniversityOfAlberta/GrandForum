<?php

require_once("DashboardTableTypes.php");
require_once("MultiDashboardTable.php");

class DashboardTable extends QueryableTable{
    
    var $obj;
    var $counters = array();
    
    function __construct(){
        $this->id = "dashboard".QueryableTable::$idCounter;
        $argv = func_get_args();
        switch(func_num_args()){
            case 2:
                self::DashboardTable($argv[0], $argv[1]);
                break;
            case 3:
                self::DerivedDashboardTable($argv[0], $argv[1], $argv[2]);
                break;
            case 4:
                self::DashboardTable($argv[0], $argv[1], $argv[2], $argv[3]);
                break;
        }
    }
    
    function copy(){
        $copy = new $this->class($this->structure, $this->xls, $this->obj);
        return $copy;
    }
    
    // Creates a new DashboardTable instance with the given person ID, structure type, and data set
    private function DashboardTable($structure, $obj, $start=null, $end=null){
        global $dashboardStructures;
        $this->QueryableTable();
        $this->obj = $obj;
        if(is_callable($dashboardStructures[$structure])){
            if($start != null && $end != null){
                $this->structure = $dashboardStructures[$structure]($start, $end);
            }
            else{
                $this->structure = $dashboardStructures[$structure]();
            }
        }
        else{
            $this->structure = $dashboardStructures[$structure];
            if($start != null && $end != null){
                foreach($this->structure as $rowN => $row){
                    foreach($row as $colN => $cell){
                         if(!is_numeric($cell)){
                            $splitRow = explode('(', $cell);
                            $type = $splitRow[0];
                        }
                        else{
                            $type = $cell;
                        }
                        if($type >= 0){
                            if(strstr($cell, ")") !== false){
                                $cell = str_replace_first(")", ",$start,$end)", $cell);
                            }
                            else{
                                $cell = $cell."($start,$end)";
                            }
                            $this->structure[$rowN][$colN] = $cell;
                        }
                    }
                }
            }
        }
        $this->structure = $this->preprocessStructure($this->structure);
        $data = array();
        foreach($this->structure as $rowN => $row){
            foreach($row as $colN => $cell){
	            $params = array();
	            if(!is_numeric($cell)){
	                $params = $this->parseParams($cell);
	                $splitCell = explode('(', $cell);
                    $cell = $splitCell[0];
	                $this->structure[$rowN][$colN] = $cell;
	            }
	            $cellValue = $this->processCell($cell, $params, "", $rowN, $colN);
	            if(!($cellValue instanceof NACell)){
	                $this->xls[$rowN][$colN] = $cellValue;
	            }
            }
        }
    }
    
    // Used for derived tables
    private function DerivedDashboardTable($structure, $matrix, $obj){
        $this->QueryableTable();
        $this->obj = $obj;
        $this->structure = $this->preprocessStructure($structure);
        $this->errors = array();
        $this->xls = array();
        foreach($this->structure as $rowN => $row){
            foreach($row as $colN => $cell){
		        if(isset($matrix[$rowN][$colN])){
		            $params = array();
		            $origCellValue = $matrix[$rowN][$colN];
		            if(!is_numeric($cell)){
                        $splitCell = explode('(', $cell);
		                $cell = $splitCell[0];
		                $params = explode(',', str_replace(', ', ',', str_replace(')', '', $splitCell[1])));
		            }
		            else if($origCellValue instanceof Cell && count($origCellValue->params) > 0){
		                $params = $origCellValue->params;
		            }
		            $cellValue = $this->processCell($cell, $params, $origCellValue, $rowN, $colN);
		            if(!($cellValue instanceof NACell)){
		                $this->xls[$rowN][$colN] = $cellValue;
		            }
		        }
            }
        }
    }
    
    // Renders the QueryableTable as an html table.  Cells are formatted based on their type
    function render($sortable=false, $showManageProducts=false){
        global $wgServer, $wgScriptPath, $config;
        $ret = array();
        $sort = "";
        if($sortable){
            $sort = "class='sortable'";
        }
        $ret[] = "<script type='text/javascript'>
            $(document).ready(function(){
                $('#{$this->id} a.details_div_lnk').click(function(e){
                    var that = this;
                    $('#{$this->id}details_div').html($('#' + $(this).attr('name')).html());
                    $('#{$this->id}details_div').append('<input type=\"button\" class=\"up_div\" value=\"&uarr;\" /><input type=\"button\" class=\"hide_div\" value=\"X\" />');
			        $('#{$this->id}details_div').slideDown(200);
			        $('#{$this->id}details_div table').dataTable({
			                                                        'bPaginate': 'false',
			                                                        'dom': 'Blfrtip',
                                                                    'buttons': [
                                                                        'excel', 'pdf'
                                                                    ]
			                                                     });
			        $('html,body').animate({scrollTop:
			                                $('#{$this->id}details_div').offset().top}, 1000);
			        $('input[type=button].hide_div').click(function(e){
			            $(this).parent().slideUp(200);
			            $('html,body').animate({scrollTop:
			                                $('#{$this->id}').offset().top}, 500);
			        });
			        
			        $('input[type=button].up_div').click(function(e){
			            $('html,body').animate({scrollTop:
			                                $('#{$this->id}').offset().top}, 500);
			        });
			    });
			});
        </script>";
        $ret[] = "<div style='max-width:900px;'><table class='dashboard wikitable' id='{$this->id}' style='width:100%;background:#ffffff;border-style:solid;' cellspacing='1' cellpadding='3' frame='box' rules='all' $sort>\n";
        foreach($this->xls as $rowN => $row){
            $ret[] = "<tr>\n";
            $i = 0;
            foreach($row as $colN => $cell){
                $style = "";
                $class = "";
                $Cell = $cell;
                $cell = $Cell->render();
                $style = $Cell->style;
                $span = 1;
                if(!isset($row[$colN + 1])){
                    $span = max(1, $this->nCols() - $colN);
                }
                if($Cell->span != null){
                    $span = $Cell->span;
                    $class .= " explicitSpan";
                }
                $ret[] = "<td nowrap='nowrap' style='white-space:nowrap;$style;' colspan='$span' class='smaller $class'>$cell</td>\n";
                ++$i;
            }
            $ret[] = "</tr>\n";
        }
        $ret[] = "</table></div>\n";
        $ret[] = "<div id='{$this->id}details_div' style='display:none;border:1px solid #ccc;margin-top:10px;margin-bottom:10px;max-width:878px;padding:10px;position:relative;'></div>\n";
        if($showManageProducts){
            $ret[] = "<a class='button' target='_blank' href='$wgServer$wgScriptPath/index.php/Special:ManageProducts'>Manage ".Inflect::pluralize($config->getValue('productsTerm'))."</a><br />\n";
        }
        return implode("", $ret);
	}
	
	// Similar to render(), but used specifically for when printing PDFs
	// The Dashboard details are expanded below the table
	function renderForPDF($table=true, $details=true){
	    $dir = dirname(__FILE__);
	    require_once($dir . '/../../../Classes/SmartDomDocument/SmartDomDocument.php');
	    $me = Person::newFromWgUser();
	    $html = "";
	    if($table){
	        $dom = new SmartDOMDocument();
	        $dom->loadHTML($this->render());
	        
	        $scripts = $dom->getElementsByTagName("script");
	        foreach($scripts as $script){
	            $script->parentNode->removeChild($script);
	        }
	        
	        $tabs = $dom->getElementsByTagName("table");
	        foreach($tabs as $tab){
	            if($tab->parentNode->tagName == "div"){
	                foreach($tab->getElementsByTagName("td") as $td){
	                    $td->removeAttribute('width');
	                    $td->removeAttribute('nowrap');
	                    $td->setAttribute('style', $td->getAttribute('style'));
	                }
	                foreach($tab->getElementsByTagName("table") as $t){
	                    $t->setAttribute('style', $t->getAttribute('style').'border-spacing:0px;');
	                    foreach($t->getElementsByTagName("td") as $td){
	                        $td->setAttribute('style', $td->getAttribute('style')."padding:".max(1, floor((0.5*DPI_CONSTANT)))."px 0;");
	                        $td->setAttribute('style', str_replace("width:16px;", "", $td->getAttribute('style')));
	                        $td->setAttribute('style', str_replace("width:50%;", "", $td->getAttribute('style')));
	                    }
	                }
	                
	                $tab->removeAttribute('rules');
	                $tab->removeAttribute('boxes');
	                $tab->removeAttribute('frame');
	                $tab->setAttribute('style', "width:100%;page-break-inside:avoid;background-color:#000000;border-color:#000000;margin-bottom:15px;border-spacing:".max(1, (0.5*DPI_CONSTANT))."px;");
	                $tab->setAttribute('width', '100%');
                    $tab->setAttribute('cellpadding', '1');
                    $tab->setAttribute('cellspacing', '1');
                    $tab->parentNode->removeAttribute('style');
                }
            }
            $html = "$dom";
	    }
	    if($details){
	        $rowIt = -1;
	        foreach($this->xls as $rowN => $row){
	            $rowIt++;
                if($rowIt <= 1 || $rowIt >= (($this->nRows()-1))){
                    continue;
                }
                $tmpHtml = "";
                if(isset($this->xls[$rowN][0])){
                    $tmpHtml = "<h2>".str_replace("<br />", "", $this->xls[$rowN][0]->toString())." Dashboard Details</h2>\n";
                }
                $details = "";
                foreach($row as $colN => $cell){
                    if($cell instanceof DashboardCell){
                        foreach($cell->values as $type => $values){
                            $extra = ($type == "All") ? "" : ' / '.$type;
                            $details .= "<h3 style='margin:0;'>{$cell->label}$extra</h3><div><ul>\n";
                            $firstTimeType = array();
                            foreach($values as $item){
                                $items = (is_array($item)) ? $item : array($item);
                                foreach($items as $item){
                                    $row = new SmartDomDocument();
                                    $row->loadHTML($cell->detailsRow($item));
                                    $tds = $row->getElementsByTagName('td');
                                    for($i=0; $i<$tds->length; $i++){
                                        $td = $tds->item($i);
                                        if($td->getAttribute('class') == 'pdfnodisplay'){
                                            $td->nodeValue = '';
                                            $td->appendChild(new DOMElement('span', ''));
                                        }
                                        else{
                                            $td->appendChild(new DOMElement('span', ''));
                                        }
                                    }
                                    $tds = $row->getElementsByTagName('td');
                                    for($i=0; $i<$tds->length; $i++){
                                        $td = $tds->item($i);
                                        $i--;
                                        DOMRemove($td);
                                    }
                                    if(($cell->label == "HQP") && $cell instanceof PersonHQPCell){
                                        $hqp = Person::newFromId($item);
                                        $position = $hqp->getPosition();
                                        $position = ($position != "") ? $position : "Other";
                                        if(!isset($firstTimeType[$position])){
                                            if(count($firstTimeType) > 0){
                                                $details .= "</ul></li>\n";
                                            }
                                            $details .= "<li>{$position}s<ul>";
                                            $firstTimeType[$position] = true;
                                        }
                                    }
                                    if($cell instanceof PublicationCell){
                                        $paper = Paper::newFromId($item);
                                        $type = $paper->getType();
                                        if(!isset($firstTimeType[$type])){
                                            if(count($firstTimeType) > 0){
                                                $details .= "</ul></li>\n";
                                            }
                                            $details .= "<li>$type<ul>";
                                            $firstTimeType[$type] = true;
                                        }
                                    }
                                    $details .= "<li>".$row."</li>\n";
                                }
                            }
                            if($cell instanceof PublicationCell){
                                $details .= "</ul></li>\n";
                            }
                            if(($cell->label == "HQP") && $cell instanceof PersonHQPCell){
                                $details .= "</ul></li>\n";
                            }
                            $details .= "</ul></div>\n";
                        }
                    }
                }
                $tmpHtml .= "$details\n";
                if($rowIt > 2){
                    $html .= "<div class='pagebreak'></div>";
                }
                $html .= "<div class='pdfDetailsDiv'>$tmpHtml</div>\n";
            }
        }
	    return $html;
	}
    
    static function union_tables($tables){
        return QueryableTable::union_t($tables, "DashboardTable");
    }
    
    static function join_tables($tables){
        return QueryableTable::join_t($tables, "DashboardTable");
    }
}
?>
