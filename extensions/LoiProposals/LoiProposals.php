<?php
$dir = dirname(__FILE__) . '/';

//$wgHooks['UnknownAction'][] = 'getack';

$wgSpecialPages['LoiProposals'] = 'LoiProposals';
$wgExtensionMessagesFiles['LoiProposals'] = $dir . 'LoiProposals.i18n.php';
$wgSpecialPageGroups['LoiProposals'] = 'grand-tools';

function runLoiProposals($par) {
	LoiProposals::run($par);
}

class LoiProposals extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('LoiProposals');
		SpecialPage::SpecialPage("LoiProposals", RMC.'+', true, 'runLoiProposals');
	}
	
	static function run(){
	    global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgMessage;
		
	    
		if (isset($_GET['getpdf'])) {
			$filename = $_GET['getpdf'];
			$filepath = "/local/data/www-root/grand_forum/data/loi_proposals/loi/{$filename}";
			//echo $filepath;
			if (file_exists($filepath)) {
				$wgOut->disable();
            	ob_clean();
            	//flush();
			    header("Pragma: public");
	            header("Expires: 0");
	            header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
	            header("Content-Type: application/force-download");
	            header("Content-Type: application/octet-stream");
	            header("Content-Type: application/download");
			    header('Content-Disposition: attachment; filename='.$filename);
			    header('Content-Transfer-Encoding: binary');
			   
			    readfile($filepath);
			    exit;
			}	
		}

	    $wgOut->setPageTitle("LOI Proposals");
	    $wgOut->addHTML("");
	    $html =<<<EOF
	    <div id='ackTabs'>
        <ul>
            <li><a href='#lois'>LOI Proposals</a></li>
            <!--<li><a href='#ni'>CV</a></li>-->
        </ul>
	    <div id="lois" style='width: 1100px;overflow: scroll;'>
	    <table class='indexTable' style='background:#ffffff; width: 1500px; table-layout: auto;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
            <thead>
                <tr bgcolor='#F2F2F2'>
                    <th>Name</th>
                    <th>Full Name</th>
                    <th>Type</th>
                    <th>Related LOI</th>
                    <th style="width: 200px;">Description</th>
                    <th>Lead</th>
                    <th>Co-Lead</th>
                    <th>Champion</th>
                    <th>Primary Challenge</th>
                    <th>Secondary Challenge</th>
                    <th>LOI File</th>
                    <th>Supplemental</th>
                </tr>
            </thead>
            <tbody>
EOF;

		$query = "SELECT * FROM grand_loi WHERE year=2013";
		$data = DBFunctions::execSQL($query);
		foreach($data as $row){
			$name 	= $row['name'];
			$full_name = $row['full_name'];
			$type = $row['type'];
			$related_loi = $row['related_loi'];
			$description = $row['description'];
			$lead 	= $row['lead'];
			$colead = $row['colead'];
			$champion = $row['champion'];
			$primary_challenge = $row['primary_challenge'];
			$secondary_challenge = $row['secondary_challenge'];
			$loi_pdf = $row['loi_pdf'];
			$supplemental_pdf = $row['supplemental_pdf'];
			if(!empty($loi_pdf)){
				$loi_pdf = "<a target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:LoiProposals?getpdf={$loi_pdf}'>{$loi_pdf}</a>";
			}else{
				$loi_pdf = "N/A";
			}

			if(!empty($supplemental_pdf)){
				$supplemental_pdf = "<a target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:LoiProposals?getpdf={$supplemental_pdf}'>{$supplemental_pdf}</a>";
			}else{
				$supplemental_pdf = "N/A";
			}

			$html .=<<<EOF
				<tr>
				<td>{$name}</td>
				<td>{$full_name}</td>
				<td>{$type}</td>
				<td>{$related_loi}</td>
				<td>{$description}</td>
				<td>{$lead}</td>
				<td>{$colead}</td>
				<td>{$champion}</td>
				<td>{$primary_challenge}</td>
				<td>{$secondary_challenge}</td>
				<td>{$loi_pdf}</td>
				<td>{$supplemental_pdf}</td>
				</tr>
EOF;
		}

		$html .=<<<EOF
		</tbody>
		</table>
		</div>
		<script type='text/javascript'>
            $(document).ready(function(){
                $('.indexTable').dataTable({
                	"bAutoWidth": false
    			});
                $('#ackTabs').tabs();
            });
        </script>
EOF;
		$wgOut->addHTML($html);
	}

	// static function createTab(){
	// 	global $notifications, $wgServer, $wgScriptPath;
		
		
	// 	$selected = "";
		
	// 	    echo "<li class='top-nav-element $selected'>\n";
	// 	    echo "	<span class='top-nav-left'>&nbsp;</span>\n";
	// 	    echo "	<a id='lnk-notifications' class='top-nav-mid' href='$wgServer$wgScriptPath/index.php/Special' class='new'>My&nbsp;Notifications</a>\n";
	// 	    echo "	<span class='top-nav-right'>&nbsp;</span>\n";
		
	// }

}

?>