<?php
$dir = dirname(__FILE__) . '/';

$wgHooks['UnknownAction'][] = 'getack';

$wgSpecialPages['EthicsTable'] = 'EthicsTable';
$wgExtensionMessagesFiles['EthicsTable'] = $dir . 'EthicsTable.i18n.php';
$wgSpecialPageGroups['EthicsTable'] = 'grand-tools';

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
	   
	    
	    
	    $projects = Project::getAllProjectsDuring();
	    
	    $people = Person::getAllPeople();
	   
	    
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

    	$wgOut->addHTML("<p style='font-size: 120%; padding: 15px 0 20px 0;'><b>A total of {$total['ethical']} out of {$all_hqp_num} ({$perc}%) have completed the TCPS2 turorial across all universities.</b></p>");

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
    			<td>{$percentage}</td>
    			<td>{$ethical_num} out of {$total_num} have completed the TCPS2 turorial</td>
    			</tr>
EOF;

			$wgOut->addHTML($row);
    	}

    	$wgOut->addHTML("</tbody></table>");
    }

    
}

?>
