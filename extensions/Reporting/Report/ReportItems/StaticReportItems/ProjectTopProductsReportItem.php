<?php

class ProjectTopProductsReportItem extends StaticReportItem {

	private function getTable(){
	    $max = $this->getAttr("max", 10);
	    $project = Project::newFromId($this->projectId);
	    $products = $project->getTopProducts();
	    $date = date('M j, Y', strtotime($project->getTopProductsLastUpdated()));
		$table = "<table class='dashboard' cellspacing='1' cellpadding='3' rules='all' frame='box' style='border: none;'>
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
            $year = substr($product->getDate(), 0, 4);
            if($year == "0000"){
                $year = "";
            }
            if($year == YEAR){
                $year = "<b><u>$year</u></b>";
            }
            if($lastYear != "---" && $year != $lastYear){
                $table .= "<tr><td colspan='3' style='background:#000000;'></td></tr>";
            }
            $table .= "<tr>
                           <td align='center'>{$year}</td>
                           <td>{$product->getCategory()}</td>
                           <td>{$product->getProperCitation()}</td>
                       </tr>";
            $lastYear = $year;
            $i++;
        }
        $table .= "</table>
                   <i>Last updated on: $date</i>";
        return $table;
	}
	
	function render(){
		global $wgOut, $wgUser;
		$item = $this->getTable();
        $item = $this->processCData($item);
		$wgOut->addHTML($item);
	}
	
	function renderForPDF(){
	    global $wgOut, $wgUser;
	    $item = $this->getTable();
	    $item = $this->processCData($item);
		$wgOut->addHTML($item);
	}

}

?>
