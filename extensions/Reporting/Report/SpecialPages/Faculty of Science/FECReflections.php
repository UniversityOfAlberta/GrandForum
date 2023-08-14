<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['FECReflections'] = 'FECReflections'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['FECReflections'] = $dir . 'FECReflections.i18n.php';
$wgSpecialPageGroups['FECReflections'] = 'reporting-tools';

$wgHooks['SubLevelTabs'][] = 'FECReflections::createSubTabs';

class FECReflections extends SpecialPage {
    
    function FECReflections(){
        parent::__construct("FECReflections", null, true);
    }
    
    function userCanExecute($user){
        $me = Person::newFromUser($user);
        return ($me->isRole(DEAN) || $me->isRole(DEANEA) || $me->isRole(VDEAN) || $me->isRoleAtLeast(STAFF));
    }
    
    function showStats($year){
        global $wgOut;
        $blob = new ReportBlob(BLOB_ARRAY, $year, 0, 0);
	    $blob_address = ReportBlob::create_address("RP_FEC_REFLECTIONS", "REFLECTIONS", "REFLECTIONS", 0);
	    $blob->load($blob_address);
	    $blob_data = $blob->getData();
	    
        if(date('Y-m-d') >= ($year+1)."01-01" && is_array($blob_data)){
            $nPeople = $blob_data['nPeople'];
            $publications = $blob_data['publications'];
            $rankings = $blob_data['rankings'];
        }
        else{
            $people = Person::getAllPeopleDuring(NI, "2000-01-01", "2100-01-01");
            $publications = array('pr' => array(),
                                  'nonpr' => array());
            $rankings = array();
            $nPeople = 0;
            foreach($people as $person){
                $case = $person->getCaseNumber($year);
                if($case == "" ||
                   strstr($case, "D") !== false ||
                   strstr($case, "E") !== false ||
                   strstr($case, "F") !== false ||
                   strstr($case, "T") !== false){
                    continue;
                }
                $nPeople++;
                $report = new Report();
                $section = new EditableReportSection();
                $pubs = new PersonProductsReportItemSet();
                $report->year = $year;
                $pubs->parent = $section;
                $section->parent = $report;
                $pubs->personId = $person->getId();
                $pubs->attributes['category'] = "Publication";
                $pubs->attributes['useProductYear'] = "true";
                $pubs->attributes['includeHQP'] = "false";
                $pubs->attributes['start'] = ($year-1)."-07-01";
                $pubs->attributes['end'] = ($year)."-06-30";
                // First do Peer Reviewed
                $pubs->attributes['peerReviewed'] = "Yes";
                foreach($pubs->getData() as $pub){
                    $pub = Product::newFromId($pub['product_id']);
                    if($pub->getType() == "Journal Paper"){
                        $publications['pr']['journals'][$pub->getId()] = $pub;
                        $ranking = $pub->getData(array('category_ranking'));
                        $ranking = explode("/", $ranking);
                        if(count($ranking) == 2){
                            // Ranked
                            $rankings[$pub->getId()] = $ranking[0]/$ranking[1];
                        }
                        else{
                            // Unranked
                            $rankings[$pub->getId()] = -1;
                        }
                    }
                    else if($pub->getType() == "Conference Paper"){
                        $publications['pr']['conference'][$pub->getId()] = $pub;
                    }
                    else if($pub->getType() == "Book Chapter"){
                        $publications['pr']['book_chapters'][$pub->getId()] = $pub;
                    }
                    else{
                        $publications['pr']['others'][$pub->getId()] = $pub;
                    }
                }
                
                // Then do Non-Peer Reviewed and Books
                $pubs->attributes['peerReviewed'] = "";
                foreach($pubs->getData() as $pub){
                    $pub = Product::newFromId($pub['product_id']);
                    if($pub->getData('peer_reviewed') == "" || $pub->getData('peer_reviewed') == "No"){
                        if($pub->getType() != "Book"){
                            $publications['nonpr']['journals'][$pub->getId()] = $pub;
                        }
                    }
                    if($pub->getType() == "Book"){
                        $publications['nonpr']['books'][$pub->getId()] = $pub;
                    }
                }
                
                // Then do Patents
                $pubs->attributes['category'] = "Patent/Spin-Off";
                $pubs->attributes['peerReviewed'] = "";
                $pubs->attributes['includeHQP'] = "false";
                foreach($pubs->getData() as $pub){
                    $pub = Product::newFromId($pub['product_id']);
                    if($pub->getType() == "Patent" && ($pub->getStatus() == "Awarded" || $pub->getStatus() == "Published")){
                        $publications['nonpr']['patents'][$pub->getId()] = $pub;
                    }
                }
            }
            
            if(date('Y-m-d') >= ($year+1)."01-01"){
                $data = array('nPeople' => $nPeople,
	                          'publications' => $publications,
	                          'rankings' => $rankings);
                
                $blob = new ReportBlob(BLOB_ARRAY, $year, 0, 0);
	            $blob_address = ReportBlob::create_address("RP_FEC_REFLECTIONS", "REFLECTIONS", "REFLECTIONS", 0);
	            $blob->store($data, $blob_address, false);
	        }
        }
        
        $n1_10 = 0;
        $n10_30 = 0;
        $n30_50 = 0;
        $n50_100 = 0;
        $unranked = 0;
        
        foreach($rankings as $rank){
            if($rank == -1){
                $unranked++;
            }
            else if($rank <= 0.10){
                $n1_10++;
            }
            else if($rank <= 0.30){
                $n10_30++;
            }
            else if($rank <= 0.50){
                $n30_50++;
            }
            else{
                $n50_100++;
            }
        }
        
        $totalRefereed = @(count($publications['pr']['journals']) + count($publications['pr']['conference']) + count($publications['pr']['book_chapters']) + count($publications['pr']['others']));

        $wgOut->addHTML("Journals: ".@count($publications['pr']['journals'])."<br />");
        $wgOut->addHTML("Conferences: ".@count($publications['pr']['conference'])."<br />");
        $wgOut->addHTML("Book Chapters: ".@count($publications['pr']['book_chapters'])."<br />");
        $wgOut->addHTML("Other Refereed: ".@count($publications['pr']['others'])."<br />");
        $wgOut->addHTML("Total Refereed: ".($totalRefereed)."<br />");
        $wgOut->addHTML("# Faculty: $nPeople<br />");
        $wgOut->addHTML("Average/Faculty: ".($totalRefereed/$nPeople)."<br />");
        $wgOut->addHTML("Non-refereed: ".@count($publications['nonpr']['journals'])."<br />");
        $wgOut->addHTML("Books: ".@count($publications['nonpr']['books'])."<br />");
        $wgOut->addHTML("Patents: ".@count($publications['nonpr']['patents'])."<br />");
        $wgOut->addHTML("<br />");
        $wgOut->addHTML("1-10%: ".($n1_10/count($rankings))."<br />");
        $wgOut->addHTML("10-30%: ".($n10_30/count($rankings))."<br />");
        $wgOut->addHTML("30-50%: ".($n30_50/count($rankings))."<br />");
        $wgOut->addHTML("50-100%: ".($n50_100/count($rankings))."<br />");
        $wgOut->addHTML("Unranked: ".($unranked/count($rankings))."<br />");
    }
    
    function execute($par){
        global $wgOut;
        
        $wgOut->addHTML("<div id='tabs'>");
        $wgOut->addHTML("   <ul>");
        for($year=YEAR;$year>2017;$year--){
            $wgOut->addHTML("   <li><a href='#tabs-$year'>{$year}</a></li>");
        }
        $wgOut->addHTML("   </ul>");
        
        for($year=YEAR;$year>2017;$year--){
            $wgOut->addHTML("<div id='tabs-$year'>");
            $this->showStats($year);
            $wgOut->addHTML("</div>");
        }
        
        $wgOut->addHTML("</div>");
        $wgOut->addHTML("<script type='text/javascript'>
            $('#tabs').tabs();
        </script>");
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $person = Person::newFromWgUser();
        if(self::userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "FECReflections") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("FEC Reflections", "$wgServer$wgScriptPath/index.php/Special:FECReflections", $selected);
        }
        return true;
    }
}

?>
