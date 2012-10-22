<?php
/**
 * File for Generating XML documents from articles
 * 
 */

require_once( 'Article.php' );
require_once( 'Revision.php' );
global $wgServer, $wgScriptPath;

define("ANNOKIURL",$wgServer.''.$wgScriptPath);
define("PAGEPREFIX", "pg");
define("USERPREFIX", "usr");
define("KEYPREFIX", "key");
define("VALUEPREFIX", "val");
define("ANNPREFIX", "ann");
define("ASSOCPREFIX", "a");
define("ASSOCTYPEPREFIX", "at-");
define("PAGETITLE","title");
define("PRETYPE","pre-type");
define("PRENUMBER","pre-number");
define("XML", 1);
define("FEED", 0);

class Node{
	public $id;
	public $prop;
	public $text;
	public $level;
}

class Edge{
        public $fromID;
        public $toID;
}

class XmlParser {
	var $opType = XML;
	var $assocCounter = 0;

	function XmlParser($action){
	}


	function checkLogin(){
		global $wgUser;
		
		if ($wgUser->getID()==0)
		  print "<user>false</user>";
                else
		  print "<user>true</user>";
	}	

	//TODO: This function is a mess.  Change set of replacements with something intellegent.
	function makeWiego($article){
		//$val=$_GET[];
                global $wgUser;
		if ($wgUser->getID()==0){
		  print 'ERROR:user';
		  return;
		}        		
		if(!$article->exists() || $article->isRedirect()){
		  print 'ERROR:not_exist';
		  return;
		}
		//	return 'error';
		//}

    		$text = $article->fetchContent();
		$isWiEGO = preg_match("=([a-z A-Z]* Map|Flow Chart)=",$text,$matches);
		if ($isWiEGO){
		  $header = '<?xml version="1.0" encoding="UTF-8"?>';
		  $res = $text;
		  $res = str_replace("\n",'',$text);
		  $res = str_replace("=".$matches[1]."=",'',$res);
		  $res = str_replace("'","&apos;",$res);
		  $res = str_replace("<","&lt;",$res);
		  $res = str_replace(">","&gt;",$res);
		  $res = str_replace("{{","<", $res);
		  $res = str_replace("}}","'/>",$res);
		  $res = preg_replace("/\s*Node\s*\|/","Node ",$res);
		  $res = preg_replace("/\s*Edge\s*\|/","Edge ",$res);
		  $res = preg_replace("/\s*\|\s*/","' ",$res);
		  $res = preg_replace("/=\s*=======/","=7",$res);
		  $res = preg_replace("/=\s*======/","=6",$res);
		  $res = preg_replace("/=\s*=====/","=5",$res);
		  $res = preg_replace("/=\s*====/","=4",$res);
		  $res = preg_replace("/=\s*===/","=3",$res);
		  $res = preg_replace("/=\s*==/","=2",$res);
		  $res = preg_replace("/=\s*=/","=1",$res);
		  //$res = str_replace("text=","",$res);
		  $res = preg_replace("/\s*=\s*/","='",$res);
		  $res = str_replace("/>","/>\n",$res);
		  $res = "$header\n<graph type='".$matches[1]."'>".$res."</graph>";
		  print $res;
		}
		else {
		  print 'ERROR:not_graph';
		}

			//$val += "<Node id=".$text[$pos].'/>';	
			//if(!(($pos=strpos($text,'id'))===false)){
			//	print "<Node id=".$text[$pos];			
			//}
	}

	//Builds wiki page for wiEGO graph
	function makeWiki(){
		$nodes = array();
		$edges = array();

		$xml = new XMLReader();
		$xml->XML(stripslashes($_POST["graph"]));
		//$xml->setParserProperty(XMLReader::VALIDATE, false);
		//$xml->setParserProperty(XMLReader::LOADDTD, false);

		$xml->read();
		$graphType=$xml->getAttribute("type");//Graph Type
		$graphName=$xml->getAttribute("name");
		$graphOverWrite=$xml->getAttribute("overWrite");
		//print_r("\n".$graphType."\n");
		//print_r($graphName);
		//print_r($graphOverWrite);
		while($xml->read()){ //read graph nodes and edges 

		  if($xml->nodeType == XMLReader::ELEMENT){ //check if the node is a start node 
		  	print($xml->name);
			print "\n";
			switch($xml->name){
		          case("Node"):
 		         	$node = new Node();
				//$xml->read();
			        $node->id = $xml->getAttribute("id");
			        $node->prop = $xml->getAttribute("prop");
			        $node->text = $xml->getAttribute("data"); 
				$node->level = $xml->getAttribute("level");
				array_push($nodes,$node);
				break;

			  case("Edge"):	
				$edge = new Edge();
				//$xml->read(); // read start of edges
				$edge->fromID = $xml->getAttribute("fromID");
				$edge->toID = $xml->getAttribute("toID");      
				//print $this->edge;
				//print "\n\n"
				array_push($edges,$edge);
			        break;
			}
		  }
		}
		$wikiText='='.$graphType.'='."\n";
		
	        foreach($nodes as $n){
		  //print_r($n);
//			switch($graphType){
//			case('TreeMap'):
//				if ($n->prop == 'root')
//					$level='==';
//				if ($n->prop == 'child')
//					$level='===';
//				if ($n->prop == 'leaf')
//					$level='====';
//				break;
//                        case('PersuasionMap'):
//				if ($n->prop =='goal')
//				   	$level='==';
//				if ($n->prop== 'reason')
//					$level='===';
//				if ($n->prop=='fact')
//					$level='====';
//				break;
//			case('StoryMap'):
//				$level='==';
//				break;
//			case('FlowChart'):
//				$level='==';
//				break;
//			case('DecisionMap'):
//				if ($n->prop == 'event')
//					$level='===';
//				else
//					$level='==';
//			case('BrainStormMap'):
//				if ($n->prop=='topic')
//					$level='==';
//				if ($v->prop=='idea')
//					$level='===';
//				break;
//			}
		  
		  switch($n->level){
		  case('1'): $level='==';
		    break;	
		  case('2'): $level='===';
		    break;
		  case('3'): $level='====';
		    break;	
		  case('4'): $level='=====';
		    break;	
		  case('5'): $level='======';
		    break;
		  default:
		    $level='======';
		    break;
		  }

		$wikiText .='{{Node| level='.$level.'| name='.$n->text.'| id='.$n->id.'| type='.$n->prop.'| text=}}'."\n\n";
	        }

		foreach($edges as $e){
		  //print_r($e);
		  $wikiText .= '{{Edge| fromID='.$e->fromID.'| toID='.$e->toID.'| text=}}';
		}
		//print_r($wikiText);

		$this->saveWiki($wikiText,$graphName,$graphOverWrite);
			//return true;
//		else 	return false;

	}

	function saveWiki($wikiText,$graphName,$graphOverWrite){
		global $egAnnokiCommonPath, $wgUser;
		require_once('Title.php');
		$title=Title::newFromText($graphName);
		require_once('Article.php');
		$article = new Article($title);

	        //chdir(dirname(__FILE__));	        
		require_once($egAnnokiCommonPath.'/AnnokiArticleEditor.php');

		if ($wgUser->getID()==0){
		  print "<user>false</user>";
		  return;
		}

		print "\n\n".$graphOverWrite."\n\n";
		if ($article->exists()){
			if ($graphOverWrite=="true"){
			  AnnokiArticleEditor::replaceArticleContent($article, $wikiText, 'wiEGO replaced article content');
			  //$tomuSubmission->removeArticle($wgUser->getName());
			  //$tomuSubmission->insertNewArticle($wgUser->getName(),$wikiText);
			  print "<overWrite>false</overWrite>";
			}
			if ($graphOverWrite=="false")
				print "\n\n<overWrite>true</overWrite>\n\n";
		}else{
		  AnnokiArticleEditor::createNewArticle($article, $wikiText, 'wiEGO added new article');
		  //$tomuSubmission->insertNewArticle($wgUser->getName(),$wikiText);
		  print "<overWrite>false</overWrite>";	 
		}

	}
	/* make a Graphic Organizer page into a feed for AnnokiBloom */
	// 	function makeGO($article){
// 		if($article!=null){
// 			$text = $article->fetchContent();
// 			if(!(strpos($text,'!GOTYPE!')===false)){
// 				/* see what type of GO */
// 				if(!(strpos($text,'!LINEAR!')===false)){
// 					$this->makeLinearString($text);
// 				}
// 				else if(!(strpos($text,'!FLOWCHART!')===false)){
// 					$this->makeFlowChart($text);
// 				}
// 				else if(!(strpos($text,'!TOPICMAP!')===false)){
// 					$this->makeTopicMap($text);
// 				}
// 				else if(!(strpos($text,'!HIERARCHY!')===false)){
// 					$this->makeHierarchy($text);
// 				}
// 				else if(!(strpos($text,'!SPIDERMAP!')===false)){
// 					$this->makeSpiderMap($text);
// 				}
// 				else if(!(strpos($text,'!STORYEVOLUTION!')===false)){
// 					$this->makeStoryEvolution($text);
// 				}
// 			}
// 		}
// 	}
	
	/* make the linear string GO */
// 	function makeLinearString($text){
// 	  $text = strip_tags($text);
					
// 	  print '<topicMap xmlns="http://www.topicmaps.org/xtm/1.0/" xmlns:xlink="http://www.w3.org/1999/xlink">'."\n\n";
// 		$topics = array(array());
// 		$topicCounter = 0;
		
// 		/* find titles */
// 		preg_match_all('/\!T\![\s]*(.*)[\s]*\!TT\!/',$text,$matches,PREG_SET_ORDER);
// 		foreach($matches as $val){
// 		        print $this->makeGoLinearStartNodeXML($val[1],$topicCounter++);
// 			print "\n\n";
// 		}
// 		// find events 
// 		//preg_match_all('/\!#\![\s]*([\d]*)[\s]*\!##\![\s]*\!S\![\s]*(.*)[\s]*\!SS\!/',$text,$matches,PREG_SET_ORDER);
// // 		// preg_match_all('/\!#\![\s]*([\d]*)[\s]*\!##\![\s]*===([^=]+)===/',$text,$matches,PREG_SET_ORDER);
// //                 preg_match_all('/\!#\![\s]*(.*)[\s]*\!##\![\s]*===([^=]+)===/',$text,$matches,PREG_SET_ORDER);
		
// 		$i=0;
// 		foreach($matches as $val){
// 		  $topics[$val[1]] = $val[2];
// 		  print $this->makeGoLinearEventNodeXML($val[2],$topicCounter++,$i++);
// 		  print "\n\n";
// 		}
		
// 		/*$size = count($topics);
		
// 		for($i=0;$i<$size;$i++){
// 		  if(isset($topics[$i]) && ($topics[$i]!=null)){
// 		    print $this->makeGoLinearEventNodeXML($topics[$i],$topicCounter++,$i);
// 		    print "\n\n";
// 		  }
// 		}*/
// 		$assocCounter = 0;
// 		$endCounter = $topicCounter-1;
// 		for($i=0;$i<$endCounter;$i++){
// 			print $this->makeGoAssociationXML($assocCounter++,$i,$i+1);
// 			print "\n\n";
// 		}
// 		print "\n</topicMap>\n";
// 	}


	/* make the flow chart GO */
// 	function makeFlowChart($text){
//           $text = strip_tags($text);
// 	  print '<topicMap xmlns="http://www.topicmaps.org/xtm/1.0/" xmlns:xlink="http://www.w3.org/1999/xlink">'."\n\n";
		
// 		$topics = array(array());
// 		$topicCounter = 0;
		
// 		/* find titles 
// 		preg_match_all('/-T-[\s]*(.*)[\s]*-TT-/',$text,$matches,PREG_SET_ORDER);
// 		foreach($matches as $val){
// 			#print $this->makeGoFlowChartStartNodeXML($val[1],$topicCounter++);
// 			#print "\n\n";
// 		}
// 		*/
		
// 		/* find events */
// 	//	preg_match_all('/-([\w]+)-([\w\W]*)-(\\1\\1)-/',$text,$matches,PREG_SET_ORDER);
// 		$i=0;
// 		preg_match_all('/-([\w]+)-(.*)-(\\1\\1)-/',$text,$matches,PREG_SET_ORDER);
// 		foreach($matches as $val){
// 		  $topics[$val[1]] = $val[2];
// 		  switch ($val[1]){
// 		  case 'b':
//                         print $this->makeGoFlowChartStartNodeXML($val[2],$topicCounter++);
// 			print "\n\n";
// 		  	break;

// 		  case 'd':
//                         print $this->makeGoFlowChartNodeXML('#DECISION: '.$val[2],$topicCounter++,$i++);
//                         print "\n\n";
//                         break;

// 		  case 'e':
//                         print $this->makeGoFlowChartNodeXML('#END: '.$val[2],$topicCounter++,$i++);
//                         print "\n\n";
//                         break;
				
// 		}
// 	      }			
// 		/*
// 		$size = count($topics);
// 		$decisions = array();
// 		$associateToPrevious = -1;
// 		$assocCounter = 0;
// 		for($i=0;$i<$size;$i++){
// 		  if(isset($topics[$i]) && $topics[$i]!=null){
// 				$newString = "";
// 				$iOf = $topicCounter-1;
// 				if($iOf<0){
// 				  //$iOf=null;
// 				}
// 				$terminal = false;
// 				preg_match_all('/-([\w]+)-([\w\W]*)-(\\1\\1)-/',$topics[$i],$matches,PREG_SET_ORDER);
// 				foreach($matches as $val){
// 				  #print $val[0]."\n";		
// 					if($val[1]=='s'){
// 						$newString = '#EVENT: '.$this->makeStringSafe($val[2]);
						
// 					}
// 					else if($val[1]=='b'){
// 						$newString = '#STARTC: '.$this->makeStringSafe($val[2]);
// 						//$iOf=null;
// 					}
// 					else if($val[1]=='e'){
// 						$newString = '#ENDC: '.$this->makeStringSafe($val[2]);
// 						$terminal = true;
// 					}
// 					else if($val[1]=='ai'){
// 						$newString = '#EVENT: '.$this->makeStringSafe($val[2]);
// 					}
// 					else if($val[1]=='d'){
// 					  $decisions[$i] = '#DECISION: '.$this->makeStringSafe($val[2]);
// 					  $terminal = true;
//                                         }
// 					else if($val[1]=='sp'){
// 					  $terminal = true;
// 					}

// 				}
// 				if(($newString == "")&&(!isset($decisions[$i]))){
// 					$newString = '#EVENT: '.$this->makeStringSafe($topics[$i]);
// 				}
// 				if($newString!=""){
// 				  print $this->makeGoFlowChartNodeXML($newString,$topicCounter++,$iOf);
// 				  print "\n\n";
// 				  $topics[$i] = $topicCounter -1;
// 				  if($associateToPrevious>=0){
// 				    print $this->makeGOAssociationXML($assocCounter++,$topics[$i],$associateToPrevious);
// 				    print "\n\n";
// 				  }
// 				  if($terminal){
// 				    $associateToPrevious = -1;
// 				  }
// 				  else{
// 				    $associateToPrevious = $topics[$i];
// 				  }

// 				}
// 			}
// 		}
		
// 		foreach($decisions as $k=>$d){
// 		  print $this->makeGoFlowChartNodeXML($d,$topicCounter,$topics[$k]);
// 		  print "\n\n";
// 		  $decisions[$k] = $topicCounter;
// 		  print $this->makeGOAssociationXML($assocCounter++,$topicCounter, $topics[$k]);
//                   print "\n\n";

// 		  $topicCounter++;
// 		}
		

// 		// find decisions 
// 		preg_match_all('/-sp-([\d]+)_([\d]+)-(.*)-spsp-/',$text,$matches,PREG_SET_ORDER);
// 		foreach($matches as $val){
// 		  $startFrom = $val[1];
// 		  if(isset($decisions[$startFrom]) && isset($topics[$val[2]])){
// 		    print $this->makeGoFlowChartNodeXML('#STEP: '.($this->makeStringSafe($val[3])),$topicCounter,$decisions[$startFrom]);
// 		    print "\n\n";
		  
// 		    print $this->makeGOAssociationXML($assocCounter++,$decisions[$startFrom],$topicCounter);
// 		    print "\n\n";
// 		    print $this->makeGOAssociationXML($assocCounter++,$topicCounter,$topics[$val[2]]);
// 		    print "\n\n";

// 		    $topicCounter++;
// 		  }
// 		  else if(isset($topics[$val[1]]) && isset($topics[$val[2]])){
// 		    print $this->makeGOAssociationXML($assocCounter++,$topics[$val[1]],$topics[$val[2]]);
//                     print "\n\n";
// 		  }
// 		}*/
		
//                 $assocCounter = 0;
//                 $endCounter = $topicCounter-1;
//                 for($i=0;$i<$endCounter;$i++){
//                       print $this->makeGoAssociationXML($assocCounter++,$i,$i+1);
//                       print "\n\n";
//                 }
// 		print "\n</topicMap>\n";
//	}
	

/* make the topicmap string GO */
// 	function makeTopicMap($text){
//           $text = strip_tags($text);
// 	  print '<topicMap xmlns="http://www.topicmaps.org/xtm/1.0/" xmlns:xlink="http://www.w3.org/1999/xlink">'."\n\n";
// 		$topics = array(array());
// 		$topicCounter = 0;
		
// 		/* find titles */
// 		preg_match_all('/\[root\][\s]*(.*)[\s]*\[\/root\]/',$text,$matches,PREG_SET_ORDER);
// 		foreach($matches as $val){
// 			print $this->makeGoTopicMapStartNodeXML($val[1],$topicCounter++);
// 			print "\n\n";
// 		}
		
// 		/* find childs */
// 		$i=0;
// 	//preg_match_all('/\!#\![\s]*([\d]*)[\s]*\!##\![\s]*\!S\![\s]*(.*)[\s]*\!SS\!/',$text,$matches,PREG_SET_ORDER);
//           	preg_match_all('/\!#\![\s]*(.*)[\s]*\!##\![\s]*===([^=]+)===/',$text,$matches,PREG_SET_ORDER);
// 		foreach($matches as $val){
// 			$topics[$val[1]] = $val[2];
//                         print $this->makeGoTopicMapNodeXML($val[2],$topicCounter++,$i++);
//                         print "\n\n";
// 	        }
// 		/*
// 		$size = count($topics);
// 		for($i=0;$i<$size;$i++){
// 			if(isset($topics[$i]) && ($topics[$i]!=null)){
// 				print $this->makeGoTopicMapNodeXML($topics[$i],$topicCounter++,$i);
// 				print "\n\n";
// 			}
// 		}*/

//                 /* find roots */
//                 $i=0;
//                 preg_match_all('/\[\*\][\s]*(.*)[\s]*\[\*\*\][\s]*====([^=]+)====/',$text,$matches,PREG_SET_ORDER);
//                 foreach($matches as $val){
//                        $topics[$val[1]] =$val[2];
//                        print $this->makeGoTopicMapLeafXML($val[2],$topicCounter++,$i++);
// 		       print "\n\n";
//                 }

// 		$assocCounter = 0;
// 		$endCounter = $topicCounter-1;
// 		for($i=0;$i<$endCounter;$i++){
// 			print $this->makeGoAssociationXML($assocCounter++,$i,$i+1);
// 			print "\n\n";
// 		}
// 		print "\n</topicMap>\n";
// 	}



/* make the Hierarchy GO */
// 	function makeHierarchy($text){
//           $text = strip_tags($text);
// 	  print '<topicMap xmlns="http://www.topicmaps.org/xtm/1.0/" xmlns:xlink="http://www.w3.org/1999/xlink">'."\n\n";
// 		$topics = array(array());
// 		$topicCounter = 0;
		
// 		/* find titles */
// 		preg_match_all('/\!T\![\s]*(.*)[\s]*\!TT\!/',$text,$matches,PREG_SET_ORDER);
// 		foreach($matches as $val){
// 			print $this->makeHierarchyStartNodeXML($val[1],$topicCounter++);
// 			print "\n\n";
// 		}
		
// 		/* find events */
// 	//preg_match_all('/\!#\![\s]*([\d]*)[\s]*\!##\![\s]*\!S\![\s]*(.*)[\s]*\!SS\!/',$text,$matches,PREG_SET_ORDER);
//                 preg_match_all('/[\s]*\!S\![\s]*(.*)[\s]*\!SS\!/',$text,$matches,PREG_SET_ORDER);
// 		$i=0;
// 		foreach($matches as $val){
// 			$topics[$val[1]] = $val[2];
//                         print $this->makeGoHierarchyNodeXML($val[1],$topicCounter++,$i++);
// 		        print "\n\n";
// 		}
// 		//$size = count($topics);
// 		//for($i=0;$i<$size;$i++){
// 		//	if(isset($topics[$i]) && ($topics[$i]!=null)){
// 		//		print $this->makeGoHierarchyNodeXML($topics[$i],$topicCounter++,$i);
// 		//		print "\n\n";
// 		//	}
// 		//}
// 		$assocCounter = 0;
// 		$endCounter = $topicCounter-1;
// 		for($i=0;$i<$endCounter;$i++){
// 			print $this->makeGoAssociationXML($assocCounter++,$i,$i+1);
// 			print "\n\n";
// 		}
// 		print "\n</topicMap>\n";
// 	}

/* make the spidermap GO */
// 	function makeSpiderMap($text){
//           $text = strip_tags($text);
// 	  print '<topicMap xmlns="http://www.topicmaps.org/xtm/1.0/" xmlns:xlink="http://www.w3.org/1999/xlink">'."\n\n";
// 		$topics = array(array());
// 		$topicCounter = 0;
		
// 		/* find titles */
// 		preg_match_all('/\!T\![\s]*(.*)[\s]*\!TT\!/',$text,$matches,PREG_SET_ORDER);
// 		foreach($matches as $val){
// 			print $this->makeGoSpiderMapStartNodeXML($val[1],$topicCounter++);
// 			print "\n\n";
// 		}
		
// 		/* find events */
//   	    //preg_match_all('/\!#\![\s]*([\d]*)[\s]*\!##\![\s]*\!S\![\s]*(.*)[\s]*\!SS\!/',$text,$matches,PREG_SET_ORDER);
//             preg_match_all('/\!#\![\s]*(.*)[\s]*\!##\![\s]*\!S\![\s]*(.*)[\s]*\!SS\!/',$text,$matches,PREG_SET_ORDER);
// 	    $i=0;
// 		foreach($matches as $val){
// 			$topics[$val[1]] = $val[2];
// 			print $this->makeGoSpiderMapNodeXML($val[2],$topicCounter++,$i++);
// 			print "\n\n";
// 		}
// 		/*
// 		$size = count($topics);
// 		for($i=0;$i<$size;$i++){
// 			if(isset($topics[$i]) && ($topics[$i]!=null)){
// 				print $this->makeGoSpiderMapNodeXML($topics[$i],$topicCounter++,$i);
// 				print "\n\n";
// 			}
// 		}*/
// 		$assocCounter = 0;
// 		$endCounter = $topicCounter-1;
// 		for($i=0;$i<$endCounter;$i++){
// 			//print $this->makeGoAssociationXML($assocCounter++,$i,$i+1);
//                         print $this->makeGoAssociationXML($assocCounter++,0,$i+1);
// 			print "\n\n";
// 		}
// 		print "\n</topicMap>\n";
// 	}

/* make the story evolution GO */
// 	function makeStoryEvolution($text){
//           $text = strip_tags($text);
// 	  print '<topicMap xmlns="http://www.topicmaps.org/xtm/1.0/" xmlns:xlink="http://www.w3.org/1999/xlink">'."\n\n";
// 		$topics = array(array());
// 		$topicCounter = 0;
		
// 		/* find titles */
// 		preg_match_all('/\!T\![\s]*(.*)[\s]*\!TT\!/',$text,$matches,PREG_SET_ORDER);
// 		foreach($matches as $val){
// 			print $this->makeGoStoryEvolutionStartNodeXML($val[1],$topicCounter++);
// 			print "\n\n";
// 		}
		
// 		/* find events */
// 	//	preg_match_all('/\!#\![\s]*([\d]*)[\s]*\!##\![\s]*\!S\![\s]*(.*)[\s]*\!SS\!/',$text,$matches,PREG_SET_ORDER);
//                 preg_match_all('/\!#\![\s]*(.*)[\s]*\!##\![\s]*===([^=]+)===/',$text,$matches,PREG_SET_ORDER);
// 		$i=0;
//                 foreach($matches as $val){
//                        $topics[$val[1]] = $val[2];
//                        print $this->makeGoStoryEvolutionNodeXML($val[2],$topicCounter++,$i++);
//                        print "\n\n";
//                 }

// 	//	$size = count($topics);
// 	//	for($i=0;$i<$size;$i++){
// 	//		if(isset($topics[$i]) && ($topics[$i]!=null)){
// 	//			print $this->makeGoStoryEvolutionNodeXML($topics[$i],$topicCounter++,$i);
// 	//			print "\n\n";
// 	//		}
// 	//	}
// 		$assocCounter = 0;
// 		$endCounter = $topicCounter-1;
// 		for($i=0;$i<$endCounter;$i++){
// 			print $this->makeGoAssociationXML($assocCounter++,$i,$i+1);
// 			print "\n\n";
// 		}
// 		print "\n</topicMap>\n";
// 	}



//         function makeGoFlowChartStartNodeXML($name,$idNum){
//              $val = '<topic id="t'.$idNum.'">';
//              $val .= "\n<baseName>\n<baseNameString>#START: $name</baseNameString>\n";
//              $val .= "<variant>\n<variantName>\n<resourceData id=\"x0\">ARROW</resourceData>\n</variantName>\n</variant>\n</baseName>\n</topic>";
//              return $val;
// 	}

// 	function makeGOFlowChartNodeXML($name,$idNum, $iOfIdNum){
// 		$val = '<topic id="t'.$idNum.'">';
// 		if($iOfIdNum>=0){
// 			$val .= "\n<instanceOf>\n<topicRef xlink:href=\"#t".($iOfIdNum)."\"/>\n</instanceOf>";
// 		}
// 		$val .= "\n<baseName>\n<baseNameString>$name</baseNameString>\n";
// 		$val .= "<variant>\n<variantName>\n<resourceData id=\"x$idNum\">ARROW</resourceData>\n</variantName>\n</variant>\n</baseName>\n</topic>";
// 		return $val;
// 	}
	

//         function makeGoSpiderMapStartNodeXML($name,$idNum){
//              $val = '<topic id="t'.$idNum.'">';
//              $val .= "\n<baseName>\n<baseNameString>#SMROOT: $name</baseNameString>\n";
//              $val .= "<variant>\n<variantName>\n<resourceData id=\"x0\">LEG</resourceData>\n</variantName>\n</variant>\n</baseName>\n</topic>";
//              return $val;
//         }


// 	function makeGOLinearStartNodeXML($name,$idNum){
// 		$val = '<topic id="t'.$idNum.'">';
// 		$val .= "\n<baseName>\n<baseNameString>#SEVENT: $name</baseNameString>\n";
// 		$val .= "<variant>\n<variantName>\n<resourceData id=\"x0\">DOUBLELINES</resourceData>\n</variantName>\n</variant>\n</baseName>\n</topic>";
// 		return $val;
// 	}
	
	
//         function makeGoSpiderMapNodeXML($name,$idNum, $sequenceNum){
//               $val = '<topic id="t'.$idNum.'">';
//               $val .= "\n<instanceOf>\n<topicRef xlink:href=\"#t".($idNum-1)."\"/>\n</instanceOf>";
//               $val .= "\n<baseName>\n<baseNameString>#SMLEAF: $name</baseNameString>\n";
//               $val .= "<variant>\n<variantName>\n<resourceData id=\"x$sequenceNum\">LEG</resourceData>\n</variantName>\n</variant>\n</baseName>\n</topic>";
//               return $val;
									      
// 	}

// 	function makeGOLinearEventNodeXML($name,$idNum, $sequenceNum){
// 		$val = '<topic id="t'.$idNum.'">';
// 		$val .= "\n<instanceOf>\n<topicRef xlink:href=\"#t".($idNum-1)."\"/>\n</instanceOf>";
// 		$val .= "\n<baseName>\n<baseNameString>#EVENT: $name</baseNameString>\n";
// 		$val .= "<variant>\n<variantName>\n<resourceData id=\"x$sequenceNum\">DOUBLELINES</resourceData>\n</variantName>\n</variant>\n</baseName>\n</topic>";
// 		return $val;
// 	}


// 	function makeGoTopicMapStartNodeXML($name,$idNum){
// 		$val = '<topic id="t'.$idNum.'">';
// 	    	$val .= "\n<baseName>\n<baseNameString>#ROOT: $name</baseNameString>\n";
// 	    	$val .= "<variant>\n<variantName>\n<resourceData id=\"x0\">BASIC</resourceData>\n</variantName>\n</variant>\n</baseName>\n</topic>";
//             	return $val;
// 	}

// 	function makeGoTopicMapNodeXML($name,$idNum,$sequenceNum){
//                 $val = '<topic id="t'.$idNum.'">';
//                 $val .= "\n<instanceOf>\n<topicRef xlink:href=\"#t".($idNum-1)."\"/>\n</instanceOf>";
//                 $val .= "\n<baseName>\n<baseNameString>#CHILD: $name</baseNameString>\n";
//                 $val .= "<variant>\n<variantName>\n<resourceData id=\"x$sequenceNum\">BASIC</resourceData>\n</variantName>\n</variant>\n</baseName>\n</topic>";
//                 return $val;
// 	}

// 	function makeGoTopicMapLeafXML($name,$idNum,$sequenceNum){
// 		$val = '<topic id="t'.$idNum.'">';
// 		$val .= "\n<instanceOf>\n<topicRef xlink:href=\"#t".($idNum-1)."\"/>\n</instanceOf>";
//                 $val .= "\n<baseName>\n<baseNameString>#LEAF: $name</baseNameString>\n";
// 		$val .= "<variant>\n<variantName>\n<resourceData id=\"x$sequenceNum\">BASIC</resourceData>\n</variantName>\n</variant>\n</baseName>\n</topic>";
//                 return $val;
//         }


// 	function makeHierarchyStartNodeXML ($name,$idNum){
//                $val = '<topic id="t'.$idNum.'">';
//                $val .= "\n<baseName>\n<baseNameString>#SINVERT: $name</baseNameString>\n";
//                $val .= "<variant>\n<variantName>\n<resourceData id=\"x0\">ARROW</resourceData>\n</variantName>\n</variant>\n</baseName>\n</topic>";
//                return $val;
//         }

//         function makeGoHierarchyNodeXML($name,$idNum,$sequenceNum){
//                $val = '<topic id="t'.$idNum.'">';
//                $val .= "\n<instanceOf>\n<topicRef xlink:href=\"#t".($idNum-1)."\"/>\n</instanceOf>";
//                $val .= "\n<baseName>\n<baseNameString>#INVERT: $name</baseNameString>\n";
// 	       $val .= "<variant>\n<variantName>\n<resourceData	id=\"x$sequenceNum\">ARROW</resourceData>\n</variantName>\n</variant>\n</baseName>\n</topic>";
//                return $val;
//        }

// 	function makeGoStoryEvolutionStartNodeXML($name,$idNum){
//                $val = '<topic id="t'.$idNum.'">';
//                $val .= "\n<baseName>\n<baseNameString>#SEVENT: $name</baseNameString>\n";
//                $val .= "<variant>\n<variantName>\n<resourceData	id=\"x0\">DOUBLELINES</resourceData>\n</variantName>\n</variant>\n</baseName>\n</topic>";
//                return $val;
//         }

// 	function makeGoStoryEvolutionNodeXML($name,$idNum, $sequenceNum){
//                $val = '<topic id="t'.$idNum.'">';
//                $val .= "\n<instanceOf>\n<topicRef xlink:href=\"#t".($idNum-1)."\"/>\n</instanceOf>";
//                $val .= "\n<baseName>\n<baseNameString>#EVENT: $name</baseNameString>\n";
//                $val .= "<variant>\n<variantName>\n<resourceData id=\"x$sequenceNum\">DOUBLELINES</resourceData>\n</variantName>\n</variant>\n</baseName>\n</topic>";
//                return $val;
//         }


//        function makeGOAssociationXML($assocIdNum, $idNum1, $idNum2){
// 	 $val = "<association id=\"a$assocIdNum\">\n<instanceOf>\n<topicRef xlink:href=\"#t$idNum2\"/>\n</instanceOf>\n";
//          $val .= "<member>\n<roleSpec>\n<topicRef xlink:href=\"#t$idNum1\"/>\n</roleSpec>\n</member>\n<member>\n<roleSpec>\n";
// 	 $val .= "<topicRef xlink:href=\"#t$idNum2\"/>\n</roleSpec>\n</member>\n</association>";
// 		return $val;
// 	}

// 	function makePageXML($pid, $ptitle, $pcounter, $revcount, $anncount, $link){
// 	        $ptitle = ereg_replace("&","&amp;",$ptitle);
// 		$ptitle = ereg_replace(">","&gt;",$ptitle);
// 		$ptitle = ereg_replace("<","&lt;",$ptitle);
// 		$link =$this->makeURLSafe($link);
// 		if($this->opType==FEED){
//   			$value = "page\t".PAGEPREFIX."".$pid."\t".$ptitle."\t".$link."\t".$pcounter."\t".$revcount."\t".$anncount;
// 		}
// 		else{
// 			$value = "<topic id=\"".PAGEPREFIX."$pid\">\n<baseName>\n<baseNameString>PAGE: $ptitle</baseNameString>\n".
// 				"<variant>\n<variantName>\n<resourceData id=\"RevisionAnnotationCounters\">RevAnn:$revcount,$anncount</resourceData>\n</variantName>\n</variant>\n".
// 				"<variant>\n<variantName>\n<resourceData id=\"Nugget\">text/html,$link,</resourceData>\n</variantName>\n</variant>\n</baseName>\n</topic>";
// 		}
  		
//   		return $value;
// 	}

// 	function makeUserXML($uid, $uname, $uemail, $usubmittedann, $uacceptedann, $urejectedann, $uurl){

//   		$uurl =$this->makeURLSafe($uurl);
//   		if($this->opType==FEED){
//   			$value = "user\t".USERPREFIX."".$uid."\t".$uname."\t\"".$uemail.'"'."\t".$usubmittedann."\t".$uacceptedann."\t".$urejectedann."\t".$uurl;
// 		}
// 		else{
// 		$value = "<topic id=\"".USERPREFIX."$uid\">\n<baseName>\n<baseNameString>USER: $uname</baseNameString>\n".
// 			"<variant>\n<variantName>\n<resourceData id=\"AnnotationCounters\">Rated: $usubmittedann:$uacceptedann:$urejectedann</resourceData>\n</variantName>\n</variant>\n".
// 			"<variant>\n<variantName>\n<resourceData id=\"Nugget\">text/html,$link,</resourceData>\n</variantName>\n</variant>\n</baseName>\n</topic>";
// 		}
//   		return $value;
// 	}


// 	function makeAssociationXML($type, $fromId, $toId, $attr=""){
// 		if($this->opType==FEED){
// 	  		return "assoc\t$type\t".$fromId."\t".$toId."\t"."\"$attr\"";
//   		}
//   		$value= "<association id=\"".ASSOCPREFIX."".$this->assocCounter."\">\n<instanceOf>\n<topicRef xlink:href=\"#".ASSOCTYPEPREFIX."$type\"/>\n</instanceOf>\n<member>".
//   			"\n<roleSpec><topicRef xlink:href=\"#$fromId\"/>\n</roleSpec>\n</member>\n<member>\n<roleSpec>\n<topicRef xlink:href=\"#$toId\"/>\n</roleSpec>".
// 			"\n</member>\n</association>";
// 		$this->assocCounter++;
// 		return $value;
// 	}

// 	function makeAttributeXML($type, $attr){
// 		if($this->opType==XML){
// 			return "";
// 		}
// 	  $value = "attr".$type."\t";
// 	  $size = sizeOf($attr);
// 	  for($i=0;$i<$size;$i++){
// 	    $value.= ($attr[$i]."\t");
// 	  }
// 	  return $value;
// 	}

	function makeURLSafe($string){
		$value = ereg_replace("&","&amp;",$string);
		$value = ereg_replace(">","&gt;",$value);
		$value = ereg_replace("<","&lt;",$value);
		return $value;
	}
	function makeStringSafe($string){
		$newString = preg_replace('/[^\w\s\d,-\.]/','',$string);
		return $newString;
	}
	
}

?>
