<?php
$dir = dirname(__FILE__) . '/';

$wgHooks['UnknownAction'][] = 'getack';

$wgSpecialPages['EthicsTable'] = 'EthicsTable';
$wgExtensionMessagesFiles['EthicsTable'] = $dir . 'EthicsTable.i18n.php';
$wgSpecialPageGroups['EthicsTable'] = 'network-tools';

function runEthicsTable($par) {
	EthicsTable::run($par);
}


class EthicsTable extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('EthicsTable');
		SpecialPage::SpecialPage("EthicsTable", HQP.'+', true, 'runEthicsTable');
	}
	
	static function run(){
	    global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgMessage;
	   
	    if(isset($_GET['getTable'])){
            $table_type = $_GET['getTable'];
            $excel = "";
            if($table_type == "HQP"){
                $excel = EthicsTable::hqpTable();
            }else if($table_type == "NI"){
                $excel = EthicsTable::niTable();
            }
            $wgOut->disable();
            ob_clean();

            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");;
            header("Content-Disposition: attachment;filename={$table_type}_Table.xls"); 
            header("Content-Transfer-Encoding: binary ");
            echo $excel;
            exit;
        }
	    
	    $wgOut->setPageTitle("TCPS2 Tutorial Completion");
	    
		EthicsTable::ethicsTable();
	
	  
	    $wgOut->addScript("<script type='text/javascript'>
                                $(document).ready(function(){
	                                $('.indexTable').dataTable({'iDisplayLength': 100,
	                                                            'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']]});
                                    $('.dataTables_filter input').css('width', 250);
                                    $('#ackTabs').tabs();
                                    
                                    $('input[name=date]').datepicker();
                                    $('input[name=date]').datepicker('option', 'dateFormat', 'dd-mm-yy');
                                });
                            </script>");

        $wgOut->addHTML("<p><a target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:EthicsTable?getTable=NI'>[Download NI Table]</a>  <a target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:EthicsTable?getTable=HQP'>[Download HQP Table]</a></p>");

    }
    
    static function ethicsTable(){
    	global $wgOut;

    	$table =<<<EOF
    	<table class='indexTable' style='background:#ffffff;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
        <thead>
            <tr bgcolor='#F2F2F2'>
                <th>University</th>
                <th>Ethics Meter(percentage)</th>
                <th>Ethics Meter(numeric)</th>
            </tr>
        </thead>
        <tbody>
EOF;


    	$hqps = Person::getAllPeople('HQP');
    	$universities = array();
    	$total = array("ethical"=>0, "nonethical"=>0);

    	foreach($hqps as $hqp){
    		$uni = $hqp->getUni();
    		$uni = ($uni == "")? "Unknown" : $uni;

    		if(!array_key_exists($uni, $universities)){
                $universities[$uni] = array("ethical"=>0, "nonethical"=>0);
            }    	
		       	
		    $ethics = $hqp->getEthics();
        	if($ethics['completed_tutorial'] == 1){
        		$universities[$uni]['ethical']++;
        		$total['ethical']++;
        	}
        	else{
        		$universities[$uni]['nonethical']++;
        		$total['nonethical']++;
        	}
    	}
    	
    	$all_hqp_num = count($hqps);
    	$perc = round(($total['ethical'] / $all_hqp_num )*100, 1);

    	$wgOut->addHTML("<p style='font-size: 120%; padding: 15px 0 20px 0;'><b>A total of {$total['ethical']} out of {$all_hqp_num} ({$perc}%) have completed the TCPS2 tutorial across all universities.</b></p>");

    	$wgOut->addHTML($table);

    	foreach($universities as $uni => $stats){
    		$ethical_num = $stats['ethical'];
    		$total_num = $stats['ethical'] + $stats['nonethical'];
    		if($total_num > 0){
    			$percentage = ($ethical_num / $total_num)*100;
    			$percentage = round($percentage, 1);
    		}
    		else{
    			$percentage = 0;
    		}
    		$row =<<<EOF
    			<tr>
    			<td>{$uni}</td>
    			<td align='right'>{$percentage}</td>
    			<td>{$ethical_num} out of {$total_num}</td>
    			</tr>
EOF;

			$wgOut->addHTML($row);
    	}

    	$wgOut->addHTML("</tbody></table>");
    }

    static function niTable(){
        global $wgOut;

        //EXCEL
        $phpExcel = new PHPExcel();
        $styleArray = array(
            'font' => array(
                'bold' => true,
            )
        );
        //Get the active sheet and assign to a variable
        $foo = $phpExcel->getActiveSheet();
         
        //add column headers, set the title and make the text bold
        $foo->setCellValue("A1", "HQP Name")
            ->setCellValue("B1", "TCPS2 Ratio")
            ->setTitle("Ethical HQP")
            ->getStyle("A1:B1")->applyFromArray($styleArray);
        
        $row_count = 2;
        
        $cnis = Person::getAllPeople(CNI);
        $pnis = Person::getAllPeople(PNI);
        $all_nis = array_merge($cnis, $pnis);

        $sorted_nis = array();
        foreach($all_nis as $ni){
            $name = $ni->getReversedName();
            $sorted_nis[$name] = $ni->getId();
        }  
        ksort($sorted_nis);

        foreach($sorted_nis as $name => $id){
            $ni = Person::newFromId($id);
            $students = $ni->getStudents();
            $total_students = count($students);
            $ethical_students = 0;
            foreach ($students as $s){
                $ethics = $s->getEthics();
                if($ethics['completed_tutorial'] == 1){
                    $ethical_students++;
                }
                
            }

            if($ethical_students > 0){
                $foo->setCellValue("A{$row_count}", $name)->setCellValue("B{$row_count}", "{$ethical_students} / {$total_students}");
                $row_count++;
            }
        }   

        $foo->getColumnDimension("A")->setWidth(40);
        $foo->getColumnDimension("B")->setWidth(40);        
        $phpExcel->setActiveSheetIndex(0);

        ob_start();
        $objWriter = PHPExcel_IOFactory::createWriter($phpExcel, "Excel5");
        $objWriter->save("php://output");
        $excel_content = ob_get_contents();
        ob_end_clean();

        return $excel_content;
    }

    static function hqpTable(){
        global $wgOut;

        //EXCEL
        $phpExcel = new PHPExcel();
        $styleArray = array(
            'font' => array(
                'bold' => true,
            )
        );
        //Get the active sheet and assign to a variable
        $foo = $phpExcel->getActiveSheet();
         
        //add column headers, set the title and make the text bold
        $foo->setCellValue("A1", "NI Name")
            ->setCellValue("B1", "TCPS2")
            ->setTitle("Ethical HQP")
            ->getStyle("A1:B1")->applyFromArray($styleArray);
        
        $row_count = 2;
        
        $hqps = Person::getAllPeople('HQP');

        $sorted_hqp = array();
        foreach($hqps as $hqp){
            $name = $hqp->getReversedName();
            $sorted_hqp[$name] = $hqp->getId();
        }  
        ksort($sorted_hqp);

        foreach($sorted_hqp as $name => $id){
            $hqp = Person::newFromId($id);
            //$name = $hqp->getReversedName();
            $ethics = $hqp->getEthics();

            if($ethics['completed_tutorial'] == 1){
                $foo->setCellValue("A{$row_count}", $name)->setCellValue("B{$row_count}", "Completed");
                $row_count++;
            }
        }   

        $foo->getColumnDimension("A")->setWidth(40);
        $foo->getColumnDimension("B")->setWidth(40);        
        $phpExcel->setActiveSheetIndex(0);

        ob_start();
        $objWriter = PHPExcel_IOFactory::createWriter($phpExcel, "Excel5");
        $objWriter->save("php://output");
        $excel_content = ob_get_contents();
        ob_end_clean();

        return $excel_content;
    }

    
}

?>
