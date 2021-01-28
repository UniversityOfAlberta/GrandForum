<?php
require_once('MyPolls.php');

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['MyPolls'] = 'MyPolls'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['MyPolls'] = $dir . 'MyPolls.i18n.php';
$wgSpecialPageGroups['MyPolls'] = 'other-tools';


function runMyPolls($par) {
  MyPolls::execute($par);
}

class MyPolls extends SpecialPage{

	function __construct() {
		SpecialPage::__construct("MyPolls", null, true, 'runMyPolls');
	}
	
	function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(HQP) || $person->isCandidate());
    }

	function execute($par){
		global $wgUser, $wgOut, $wgServer, $wgScriptPath, $wgTitle;
		$rows = DBFunctions::select(array('grand_poll_collection'),
		                            array('collection_id'));
		$collections = array();
		foreach($rows as $row){
			$collection = PollCollection::newFromId($row['collection_id']);
			$canUserViewPoll = $collection->canUserViewPoll($wgUser);
			if($canUserViewPoll){
				$collections[] = $collection;
			}
		}
		if(count($collections) > 0){
			$wgOut->addHTML("<table class='wikitable sortable' cellpadding='5' cellspacing='1' style='background:#CCCCCC;'>
						<tr style='background:#EEEEEE;'>
							<th class='en'>Name</th> <th class='en'>Number of Votes</th> <th class='en'>Creation Date</th> <th class='en'>Expiration Date</th>
                                                        <th class='fr'>Nom</th> <th class='fr'>Nombre de Votes</th> <th class='fr'>Date de création</th> <th class='fr'>Date d'expiration</th>

						</tr>");
			foreach($collections as $collection){
				$created = date("Y-m-d G:H:i", $collection->created);
				$expiration = $collection->getExpirationDate("Y-m-d G:H:i");
				$wgOut->addHTML("<tr style='background:#FFFFFF;'>
							<td><a href='$wgServer$wgScriptPath/index.php?action=viewPoll&id={$collection->id}'>{$collection->name}</a></td> <td>{$collection->getTotalVotes()}</td> <td>$created</td> <td>$expiration</td>
						</tr>");
			}
			$wgOut->addHTML("</table>");
		}
		else{
			$wgOut->addHTML("Vous avez pas créé de sondages. <a href='$wgServer$wgScriptPath/index.php/Special:CreatePoll'>Cliquez Ici</a> pour créer un.");
		}
	}
}

?>
