<?php
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['FeatureRequestViewer'] = 'FeatureRequestViewer'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['FeatureRequestViewer'] = $dir . 'FeatureRequestViewer.i18n.php';
$wgHooks['BeforePageDisplay'][] = 'printVote';
$wgSpecialPageGroups['FeatureRequestViewer'] = 'other-tools';

function printVote( &$out, &$sk ) {
	global $wgOut, $wgArticle, $wgScriptPath, $wgUser;
	
	if($wgArticle != null && count($wgArticle->getUsedTemplates()) > 0){
		$templates = $wgArticle->getUsedTemplates();
		if($templates[0]->getText() == "FeatureRequest"){
		    $data = DBFunctions::select(array('grand_feature_votes'),
		                                array('COUNT(*)' => 'count'),
		                                array('votes_p_id' => EQ($wgArticle->getID()),
		                                      'votes_u_id' => EQ($wgUser->getId())));
			if($data[0]['count'] > 0){
				printApproves();
			}
			else{
				if(isset($_POST['vote'])){
					$votesApprove = 0;
					$votesDisapprove = 0;
					if($_POST['vote'] == "Approve"){
						$votesApprove = 1;
					}
					else if($_POST['vote'] == "Disapprove"){
						$votesDisapprove = 1;
					}
					DBFunctions::insert('grand_feature_votes',
					                    array('votes_p_id' => $wgArticle->getID(),
					                          'votes_u_id' => $wgUser->getId(),
					                          'votes_approve' => $votesApprove,
					                          'votes_disapprove' => $votesDisapprove));
					printApproves();
				}
				else{
					$out->addHTML("<br /><hr />Do you aprove of this request?<br /><form method='post' action='$wgScriptPath/index.php/{$wgArticle->getTitle()->getNsText()}:{$wgArticle->getTitle()->getText()}'><input width='50' type='submit' name='vote' value='Approve' /> <input width='50' type='submit' name='vote' value='Disapprove' />");
				}
			}
		}
	}
	return true;
}

function printApproves(){
	global $wgOut, $wgArticle;
	
	$data = DBFunctions::select(array('grand_feature_votes'),
	                            array('SUM(votes_approve)' => 'approve',
	                                  'SUM(votes_disapprove)' => 'disapprove'),
	                            array('votes_p_id' => EQ($wgArticle->getID())));
	$approve = $data[0]['approve'];
	$disapprove = $data[0]['disapprove'];
	if($approve == null){
		$approve = 0;
	}
	if($disapprove == null){
		$disapprove = 0;
	}
	$wgOut->addHTML("<br /><hr />
			<table border='0'>
				<tr>
					<td align='right'>
						<b>Approvals:</b>
					</td>
					<td>
						&nbsp;$approve
					</td>
				</tr>
				<tr>
					<td align='right'>
						<b>Disapprovals:</b>
					</td>
					<td>
						&nbsp;$disapprove
					</td>
				</tr>
			</table>");
}	

function runFeatureRequestViewer($par) {
  FeatureRequestViewer::run($par);
}

class FeatureRequestViewer extends SpecialPage{

	function FeatureRequestViewer() {
		wfLoadExtensionMessages('FeatureRequestViewer');
		SpecialPage::SpecialPage("FeatureRequestViewer", HQP.'+', true, 'runFeatureRequestViewer');
	}

	function run($par){
		global $wgOut, $wgUser, $wgLocalTZoffset, $wgScriptPath, $wgTitle;
		$ugTable = getTableName("user_groups");
		$userTable = getTableName("user");
		$nsTable = getTableName("an_extranamespaces");
		$tempTable = getTableName("templatelinks");
		$pageTable = getTableName("page");
		$revTable = getTableName("revision");
	
		$sql = "SELECT DISTINCT nsName, page_title, page_id
			FROM $ugTable g, $nsTable ns, $tempTable tl, $pageTable p
			WHERE g.ug_user = '{$wgUser->getId()}'
			AND (g.ug_group = ns.nsName OR ns.nsName = 'FeatureRequest')
			AND ns.nsId = p.page_namespace
			AND p.page_id = tl.tl_from
			AND tl.tl_title = 'FeatureRequest'
			ORDER BY p.page_id DESC";
		$data = DBFunctions::execSQL($sql);
		$wgOut->addHTML("<a href='$wgScriptPath/index.php/Special:FeatureRequest'>Request a Feature</a><br /><br />");
		if(DBFunctions::getNRows() > 0){
			$wgOut->addHTML("<table class='wikitable sortable' bgcolor='#aaaaaa' cellspacing='1' cellpadding='2' style='text-align:center;'>
						<tr bgcolor='#F2F2F2'>
							<th> Namespace </th><th> Title </th><th> Submitted By </th><th> Submission Date </th><th> Last Modified </th><th> Approvals </th><th> Disaprovals </th>
						</tr>");
			foreach($data as $row){
				$wgOut->addHTML("<tr bgcolor='#FFFFFF'>
							<td>{$row['nsName']}</td><td align='left'><a href='../index.php/{$row['nsName']}:{$row['page_title']}'>".str_replace("_", " ", $row['page_title'])."</td>");
				$data2 = DBFunctions::select(array('mw_revision' => 'r',
				                                   'mw_user' => 'u'),
				                             array('u.user_name',
				                                   'r.rev_timestamp'),
				                             array('r.rev_page' => EQ($row['page_id']),
				                                   'u.user_id' => EQ(COL('r.rev_user'))),
				                             array('r.rev_id' => 'ASC'));
				$last = count($data2)-1;
				$userName = $data2[0]['user_name'];
				$firstTS = FeatureRequestViewer::date($data2[0]['rev_timestamp'], true);
				$lastTS = FeatureRequestViewer::date($data2[$last]['rev_timestamp'], true);
		
				$yearFirst = substr($firstTS, 0, 4);
				$monthFirst = substr($firstTS, 4, 2);
				$dayFirst = substr($firstTS, 6, 2);
				$hourFirst = substr($firstTS, 8, 2);
				$minuteFirst = substr($firstTS, 10, 2);
				$secondFirst = substr($firstTS, 12, 2);
		
				$yearLast = substr($lastTS, 0, 4);
				$monthLast = substr($lastTS, 4, 2);
				$dayLast = substr($lastTS, 6, 2);
				$hourLast = substr($lastTS, 8, 2);
				$minuteLast = substr($lastTS, 10, 2);
				$secondLast = substr($lastTS, 12, 2);
		
		        $data2 = DBFunctions::select(array('grand_feature_votes'),
		                                     array('SUM(votes_approve)' => 'approve',
		                                           'SUM(votes_disapprove)' => 'disapprove'),
		                                     array('votes_p_id' => EQ($row['page_id'])));
				$approve = $data2[0]['approve'];
				$disapprove = $data2[0]['disapprove'];
				if($approve == null){
					$approve = 0;
				}
				if($disapprove == null){
					$disapprove = 0;
				}
		
				$wgOut->addHTML("<td>$userName</td><td>$yearFirst-$monthFirst-$dayFirst&nbsp;&nbsp;&nbsp;$hourFirst:$minuteFirst:$secondFirst</td><td>$yearLast-$monthLast-$dayLast&nbsp;&nbsp;&nbsp;$hourLast:$minuteLast:$secondLast</td><td>$approve</td><td>$disapprove</td></tr>");
			}
			$wgOut->addHTML("</table>");
		}
		else {
			$wgOut->addHTML("There have been no Feature Requests");
		}
	}
	
	function date( $ts, $adj = false) {
	 	$ts = wfTimestamp( TS_MW, $ts );
		if ( $adj ) {
			$ts = Language::userAdjust( $ts, false);
		}
		return $ts;
	}
}

?>
