<?php
require_once('FeatureRequestViewer.php');

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['FeatureRequest'] = 'FeatureRequest'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['FeatureRequest'] = $dir . 'FeatureRequest.i18n.php';
$wgSpecialPageGroups['FeatureRequest'] = 'other-tools';

define("NS_GOV", 212);
define("NS_FeatureRequest", 214);

function runFeatureRequest($par) {
  FeatureRequest::execute($par);
}

class FeatureRequest extends SpecialPage{

	function FeatureRequest() {
		SpecialPage::__construct("FeatureRequest", HQP.'+', true, 'runFeatureRequest');
	}

	function execute($par){
		global $wgOut, $wgUser, $wgTitle, $wgScriptPath;
		if(!isset($_POST['submit'])){ // Form not entered yet
			FeatureRequest::generateFormHTML($wgOut);
		}
		else if($_POST['title'] == null){
			$wgOut->addHTML("<font color='#FF0000'><b>A Title must be entered.</b></font><br />");
			FeatureRequest::generateFormHTML($wgOut);
		}
		else if($_POST['text'] == null){
			$wgOut->addHTML("<font color='#FF0000'><b>Description must not be blank.</b></font><br />");
			FeatureRequest::generateFormHTML($wgOut);
		}
		else{ // Form submission entered
			global $wgLocalTZoffset;
			$user = $wgUser->getName();
			$date = date("F j, Y - h:i:s A");
			$title = FeatureRequest::parse($_POST['title']);
			$text = FeatureRequest::parse($_POST['text']);
			$articleText = 
"{{FeatureRequest
|FeatureRequest = $text
|Name = $user
|Date = $date
|Comments = 
}}";
			$rows = DBFunctions::select(array('mw_page'),
			                            array('*'),
			                            array('page_title' => EQ(str_replace(" ", "_", $title))));
			if(count($rows) > 0){
				$wgOut->addHTML("<font color='#FF0000'><b>This title already exists, please choose another one.</b></font><br />");
				FeatureRequest::generateFormHTML($wgOut);
			}
			else {
				$newTitle = Title::newFromText($title, $_POST['visibility']);
				$article = new Article($newTitle);
				$article->doEdit($articleText, "", EDIT_NEW);
				$ns = "FeatureRequest";
				if($_POST['visibility'] == NS_GOV){
					$ns = GOV;
				}
				else if($_POST['visibility'] == NS_GRAND_NI){
					$ns = PNI;
				}
				else if($_POST['visibility'] == NS_GRAND_CR){
					$ns = CNI;
				}
				else if($_POST['visibility'] == NS_STUDENT){
					$ns = HQP;
				}
				else if($_POST['visibility'] == NS_STUDENT_COMM){
					$ns = "Student_Committee";
				}
				$wgOut->redirect("../index.php/$ns:$title");
			}
		}
	}
	
	function generateFormHTML($wgOut){
		global $wgUser, $wgScriptPath;
		
		$text = "";
		$title = "";
		if(isset($_POST['text'])){
			$text = $_POST['text'];
		}
		if(isset($_POST['title'])){
			$title = $_POST['title'];
		}
		
        $person = Person::newFromId($wgUser->getId());
		$radio = "<input type='radio' name='visibility' value='".NS_FeatureRequest."' checked /> Registered Users<br />";
		foreach($person->getRoles() as $role){
		    switch($role->getRole()){
		        case PNI:
		            $radio .= "<input type='radio' name='visibility' value='".NS_GRAND_NI."' /> Principal Network Investigators<br />";
		            break;
			    case CNI:
				    $radio .= "<input type='radio' name='visibility' value='".NS_GRAND_CR."' /> Collaborating Network Investigators<br />";
			        break;
			    case GOV:
				    $radio .= "<input type='radio' name='visibility' value='".NS_GOV."' /> Government Personnel<br />";
			        break;
			    case HQP:
				    $radio .= "<input type='radio' name='visibility' value='".NS_STUDENT."' /> Highly Qualified Personnel<br />";
			        break;
		    }
		}
		
		$wgOut->addHTML("If you would like you see a feature implemented on this wiki, then please fill in this form so that we can look into it.  Other users on the wiki will be able to view and comment on your request, unless otherwise specified.<br /><a href='$wgScriptPath/index.php/Special:FeatureRequestViewer'>View Current Feature Requests</a><br />
				<form method='post' action='../index.php/Special:FeatureRequest'>
				<h2>Feature Request Title</h2>
				<input type='text' name='title' value='$title' size='50' /><br />
				<h3>Visibility</h3>
				This Feature Request will be visible to which users?<br />
				$radio
				<h3>Feature Description</h3>
				<textarea style='width:100%; height:300px;' name='text'>$text</textarea>
				<br />
				<input type='submit' name='submit' value='Submit Request' />
				</form>");
	}
	
	function parse($text){
		$text = str_replace("'", "&#39;", $text);
		$text = str_replace("\"", "&quot;", $text); 
		return $text;
	}
}

?>
