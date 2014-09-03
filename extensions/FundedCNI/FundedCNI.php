<?php
$dir = dirname(__FILE__) . '/';

$wgSpecialPages['FundedCNI'] = 'FundedCNI';
$wgExtensionMessagesFiles['FundedCNI'] = $dir . 'FundedCNI.i18n.php';
$wgSpecialPageGroups['FundedCNI'] = 'network-tools';

function runFundedCNI($par) {
	FundedCNI::run($par);
}


class FundedCNI extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('FundedCNI');
		SpecialPage::SpecialPage("FundedCNI", STAFF.'+', true, 'runFundedCNI');
	}
	
	static function run(){
	    global $wgOut, $wgServer, $wgScriptPath;
        
        $startYear = 2010+1;
        $endYear = date('Y');
        
        if(isset($_POST['years'])){
            for($i = $startYear; $i <= $endYear; $i++){
                // Delete previous entries
                DBFunctions::delete('grand_funded_cni',
                                    array('year' => EQ($i)));
            }
            foreach($_POST['years'] as $year => $ids){
                foreach($ids as $id){
                    // Insert new entry
                    DBFunctions::insert('grand_funded_cni',
                                        array('user_id' => $id,
                                              'year' => $year));
                }
            }
            redirect("{$wgServer}{$_SERVER["REQUEST_URI"]}");
        }
        
        $people = Person::getAllPeopleDuring(CNI, $startYear."-01-01", $endYear."-12-31");
        
        $wgOut->addHTML("<form action='$wgServer$wgScriptPath/index.php/Special:FundedCNI' method='POST'>");
        $wgOut->addHTML("<p><input type='submit' value='Save' /></p>");
        $wgOut->addHTML("<table id='funded_cni' frame='box' rules='all' width='100%'>
            <thead>
                <tr>
                    <th>&nbsp;</td>
                    <th colspan=".($endYear-$startYear+1).">Funding Years</th>
                </tr>
                <tr>
                    <th>CNI</th>");
        for($i = $startYear; $i <= $endYear; $i++){
            $wgOut->addHTML("<th>{$i}</th>");
        }
        $wgOut->addHTML("</tr>
            </thead>
            <tbody>");
        foreach($people as $cni){
            $wgOut->addHTML("<tr>
                                <td>{$cni->getReversedName()}</td>");
            for($i = $startYear; $i <= $endYear; $i++){
                if($cni->isRoleDuring(CNI, $i."-01-01", $i."-12-31")){
                    $checked = ($cni->isFundedFor($i)) ? "checked='checked'" : "";
                    $wgOut->addHTML("<td align='center'>
                        <span style='display:none;'>$i $checked</span>
                        <input type='checkbox' name='years[$i][]' value='{$cni->getId()}' $checked />
                    </td>");
                }
                else{
                    $wgOut->addHTML("<td></td>");
                }
            }
            $wgOut->addHTML("</tr>");
        }
        
        $wgOut->addHTML("</tbody></table></form>");
        $wgOut->addHTML("<script type='text/javascript'>
            $('#funded_cni').dataTable({'iDisplayLength': -1, 
                                        'bAutoWidth': false,
                                        'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']]
                                       });
            $('#funded_cni_wrapper').css('display', 'inline-block');
        </script>");
    }
    
}

?>
