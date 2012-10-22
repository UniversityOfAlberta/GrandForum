<?php

$twitter = new Twitter();

$wgHooks['UnknownAction'][] = array($twitter, 'getTwitterFeed');

class Twitter{
	
	function getTwitterFeed($action, $article){
		global $wgOut, $wgServer, $wgScriptPath, $wgLocalTZoffset;
		if($action == "getTwitterFeed"){
			$userTable = getTableName("user");
			$sql = "SELECT *
				FROM $userTable u
				WHERE user_name LIKE '%{$article->getTitle()->getText()}'";
			$data = DBFunctions::execSQL($sql);
			if(count($data) > 0){
				if($data[0]['user_twitter'] != null){
					$wgOut->setPageTitle("{$article->getTitle()->getText()}'s Twitter Feed");
					$wgOut->addHTML("<a href='http://twitter.com/{$data[0]['user_twitter']}' target='_blank'><img src='$wgServer$wgScriptPath/extensions/Twitter/twitter.png' alt='Twitter Account' width='91' height='68' /></a> <a style='font-size:16px;' href='http://twitter.com/{$data[0]['user_twitter']}' target='_blank'>Twitter Account</a>");
					exec("wget -q -O - http://twitter.com/statuses/user_timeline/{$data[0]['user_twitter']}.rss", $outputArray);
					$xml = implode("\n", $outputArray);
					$doc = new DOMDocument();
					@$doc->loadXML($xml);
					$items = $doc->getElementsByTagName("item");
					for($i = 0; $i < $items->length; $i++){
						$item = $items->item($i);
						// Title
						$titles = $item->getElementsByTagName("title");
						$title = $titles->item(0)->textContent;
						// Date
						$dates = $item->getElementsByTagName("pubDate");
						$date = new DateTime($dates->item(0)->textContent);
						$date->modify("$wgLocalTZoffset minutes");
						$dateF = $date->format("Y-m-d H:i:s");
						// Description
						$descriptions = $item->getElementsByTagName("description");
						$description = $descriptions->item(0)->textContent;
						// Link
						$links = $item->getElementsByTagName("link");
						$link = $links->item(0)->textContent;
						
						$wgOut->addHTML("<h3><a href='$link' target='_blank'>$title</a></h3>
								$dateF<br />
								$description");
					}
					return false;
				}
				$wgOut->addHTML("This user does not have a Twitter feed.");
			}
		}
		return true;
	}

}


?>
