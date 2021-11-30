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
        SpecialPage::__construct("MyPolls", HQP.'+', true, 'runMyPolls');
    }

    function execute($par){
        global $wgUser, $wgOut, $wgServer, $wgScriptPath, $wgTitle;
        $rows = DBFunctions::select(array('grand_poll_collection'),
                                    array('collection_id'));
        $collections = array();
        foreach($rows as $row){
            $collection = PollCollection::newFromId($row['collection_id']);
            $canUserViewPoll = $collection->canUserViewPoll($wgUser);
            if($canUserViewPoll && $wgUser->getId() == $collection->author->getId()){
                $collections[] = $collection;
            }
        }
        if(count($collections) > 0){
            $wgOut->addHTML("<table class='wikitable sortable' cellpadding='5' cellspacing='1' style='background:#CCCCCC;'>
                        <tr style='background:#EEEEEE;'>
                            <th>Name</th> <th>Number of Votes</th> <th>Creation Date</th> <th>Expiration Date</th>
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
            $wgOut->addHTML("You have not created any polls.  <a href='$wgServer$wgScriptPath/index.php/Special:CreatePoll'>Click Here</a> to create one.");
        }
    }
}

?>
