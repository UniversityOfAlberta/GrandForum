<?php

class ProjectTopProductsReportItem extends StaticReportItem {

	private function getTable($pdf=false){
	    $max = $this->getAttr("max", 10);
	    $project = Project::newFromId($this->projectId);
	    $products = $project->getTopProducts();
	    $lastUpdated = $project->getTopProductsLastUpdated();
        if($lastUpdated != ""){
            $date = date('M j, Y', strtotime($lastUpdated));
        }
        else{
            $date = "Never";
        }
		$table = "<div><table id='top_prods' class='dashboard' cellspacing='1' cellpadding='3' rules='all' frame='box' style='border: none;'>
                    <tr>
                        <td align='center'><b>Year</b></td>
                        <td align='center'><b>Category</b></td>
                        <td align='center'><b>Product</b></td>
                    </th>";
        $i = 0;
        $lastYear = "---";
        foreach($products as $product){
            if($i == $max)
                break;
            $date = substr($product->getDate(), 0, 10);
            $year = substr($product->getDate(), 0, 4);
            if($year == "0000"){
                $year = "";
            }
            if($date >= YEAR.REPORTING_CYCLE_START_MONTH && $date <= (YEAR+1).REPORTING_NCE_END_MONTH){
                $year = "<b><u>$year</u></b>";
            }
            if($lastYear != "---" && $year != $lastYear){
                if($pdf){
                    $table .= "<tr><td colspan='3' style='background:#808080;'></td></tr>";
                }
                else{
                    $table .= "<tr><td colspan='3' style='background:#DDDDDD;'></td></tr>";
                }
            }
            $table .= "<tr>
                           <td align='center'>{$year}</td>
                           <td>{$product->getCategory()}</td>
                           <td>{$product->getCitation()}</td>
                       </tr>";
            $lastYear = $year;
            $i++;
        }
        $table .= "</table>
                   <i>Last updated on: $date</i></div>";
        return $table;
	}
	
	function renderWidget(){
        $project = Project::newFromId($this->projectId);
        $tab = new ProjectDashboardTab($project, array('isLead' => true));
        $tab->showEditTopProducts($project, array('isMe' => true));
        
        $html = "<div id='top_widget' style='display:none;' title='Edit Top Research Outcomes'>";
        $html .= $tab->html;
        $html .= "</div>";
        return $html;
    }
	
	function render(){
		global $wgOut, $wgUser;
		$project = Project::newFromId($this->projectId);
		$item = $this->getTable(false);
		$item .= $this->renderWidget();
        $item .= "<br /><button id='edit_top' type='button'>Edit Top Research Outcomes</button>";
        $item .= "<script type='text/javascript'>
            $('#edit_top').click(function(){
                $(this).remove();
                $('#top_prods').parent().slideToggle();
                $('#top_widget').slideToggle();
                $('select.chosen:visible').chosen();
                $('select.chosen').each(function(i, el){
                    var prevVal = $(el).val();
                    if(prevVal != ''){
                        $('option[value=' + prevVal + ']', $('select.chosen').not(el)).prop('disabled', true);
                    }
                    $('select.chosen').trigger('chosen:updated');
                    $(el).change(function(e, p){
                        var id = $(this).val();
                        if(prevVal != ''){
                            $('option[value=' + prevVal + ']', $('select.chosen').not(this)).prop('disabled', false);
                        }
                        if(id != ''){
                            $('option[value=' + id + ']', $('select.chosen').not(this)).prop('disabled', true);
                        }
                        $('select.chosen').trigger('chosen:updated');
                        prevVal = id;
                    });
                });
            });
            
            $('#top_widget h2').remove();
            $('input[value=Cancel]').remove();
            $('button[name=submit]').attr('type', 'button');
            $('button[name=submit]').prop('disabled', false);
            $('button[name=submit]').click(function(){
                $('button[name=submit]').prop('disabled', true);
                needsOpening = true;
                $('#Dashboard').click();
            });
            if(typeof(needsOpening) != 'undefined' && needsOpening){
                _.delay(function(){
                    $('.toggleHeader').eq(1).click();
                }, 250);
            }
            needsOpening = false;
        </script>";
        $item = $this->processCData($item);
		$wgOut->addHTML($item);
	}
	
	function renderForPDF(){
	    global $wgOut, $wgUser;
	    $item = $this->getTable(true);
	    $item = $this->processCData($item);
		$wgOut->addHTML($item);
	}
	
	function save(){
        $project = Project::newFromId($this->projectId);
        $tab = new ProjectDashboardTab($project, array('isLead' => true));
        $tab->handleEdit();
        return array();
    }
    
    function getNFields(){
        return 0;
    }

}

?>
