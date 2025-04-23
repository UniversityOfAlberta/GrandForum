<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['FECReflections'] = 'FECReflections'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['FECReflections'] = $dir . 'FECReflections.i18n.php';
$wgSpecialPageGroups['FECReflections'] = 'reporting-tools';

$wgHooks['SubLevelTabs'][] = 'FECReflections::createSubTabs';

class FECReflections extends SpecialPage {
    
    static $validDepts = array("All");
    
    function __construct(){
        global $config, $facultyMapSimple;
        parent::__construct("FECReflections", null, true);
        self::$validDepts = array_merge(array("All"), $facultyMapSimple[getFaculty()]);
    }
    
    function userCanExecute($user){
        $me = Person::newFromUser($user);
        return ($me->isRole(DEAN) || $me->isRole(DEANEA) || $me->isRole(VDEAN) || $me->isRoleAtLeast(STAFF));
    }
    
    function showStats($year){
        global $wgOut, $wgServer, $wgScriptPath;
        $blob = new ReportBlob(BLOB_ARRAY, $year, 0, 0);
	    $blob_address = ReportBlob::create_address("RP_FEC_REFLECTIONS", "REFLECTIONS", "REFLECTIONS", getFaculty());
	    $blob->load($blob_address);
	    $blob_data = $blob->getData();
	    //$blob_data = null;
	    
        if(date('Y-m-d') >= ($year+1)."-06-31" && is_array($blob_data)){
            $nPeople = @$blob_data['nPeople'];
            $nProfs = @$blob_data['nProfs'];
            $nFSO = @$blob_data['nFSO'];
            $nUncappedFSO = @$blob_data['nUncappedFSO'];
            $publications = @$blob_data['publications'];
            $rankings = @$blob_data['rankings'];
            $nAssistProfs = @$blob_data['nAssistProfs'];
            $nAssocProfs = @$blob_data['nAssocProfs'];
            $nFullProfs = @$blob_data['nFullProfs'];
            $sumAssistProfs = @$blob_data['sumAssistProfs'];
            $sumAssocProfs = @$blob_data['sumAssocProfs'];
            $sumFullProfs = @$blob_data['sumFullProfs'];
            $sumFSO = @$blob_data['sumFSO'];
            $nLowered = @$blob_data['nLowered'];
            $nRaised = @$blob_data['nRaised'];
            $nLessThan1 = @$blob_data['nLessThan1'];
            $nGreaterThan1 = @$blob_data['nGreaterThan1'];
            $increments = @$blob_data['increments'];
            $courses = @$blob_data['courses'];
            $students = @$blob_data['students'];
        }
        else{
            $people = Person::getAllPeopleDuring(NI, SOT, EOT);
            $people = Person::filterFaculty($people);
            $publications = array('pr' => array('journals' => array(), 
                                                'conference' => array(),
                                                'book_chapters' => array(),
                                                'others' => array()),
                                  'nonpr' => array('journals' => array(), 
                                                   'books' => array(),
                                                   'patents' => array()));
            $rankings = array();
            $nPeople = 0;
            $nProfs = 0;
            $nFSO = 0;
            $nUncappedFSO = 0;
            $nAssistProfs = 0;
            $nAssocProfs = 0;
            $nFullProfs = 0;
            $sumAssistProfs = 0;
            $sumAssocProfs = 0;
            $sumFullProfs = 0;
            $sumFSO = 0;
            $nLowered = 0;
            $nRaised = 0;
            $nLessThan1 = 0;
            $nGreaterThan1 = 0;
            $increments = array();
            $courses = array();
            $students = array();
            foreach($people as $person){
                $case = $person->getCaseNumber($year);
                if($case != ""){
                    $increment = self::getBlobValue("RP_CHAIR", "FEC_REVIEW", "INCREMENT", $person->getId(), $year, 0);
                    $revisedIncrement = self::getBlobValue("RP_FEC_TABLE", "TABLE", "INCREMENT", $person->getId(), $year, 1);

                    if($revisedIncrement == ""){
                        $revisedIncrement = $increment;
                    }
                }
                if($case == "" ||
                   strstr($case, "D") !== false ||
                   strstr($case, "E") !== false ||
                   strstr($case, "F") !== false ||
                   strstr($case, "M") !== false ||
                   strstr($case, "T") !== false){
                    if(!$person->isSubRole("DD") &&
                       !$person->isRoleOn(DEAN, "{$year}-07-01") &&
                       !$person->isRoleOn(VDEAN, "{$year}-07-01") &&
                       (strstr($case, "M") !== false ||
                        strstr($case, "D") !== false ||
                        strstr($case, "E") !== false ||
                        strstr($case, "F") !== false)){
                        // FSO
                        $nFSO++;
                        
                        if($revisedIncrement != "" &&
                           $revisedIncrement != "N/A" &&
                           $revisedIncrement != "0.00" &&
                           $revisedIncrement != "0.00 (PTC)" &&
                           $revisedIncrement != "0A"){
                            $sumFSO += floatval($revisedIncrement);
                            $nUncappedFSO++;
                        }
                    }
                    continue; // Skip
                }
                if(!$person->isSubRole("DD") &&
                   !$person->isRoleOn(DEAN, "{$year}-07-01") &&
                   !$person->isRoleOn(VDEAN, "{$year}-07-01") &&
                   (strstr($case, "N") !== false ||
                    strstr($case, "A") !== false ||
                    strstr($case, "B") !== false ||
                    strstr($case, "C") !== false)){
                    // Professor
                    $nProfs++;

                    if($revisedIncrement != "" &&
                       $revisedIncrement != "N/A" &&
                       $revisedIncrement != "0.00" &&
                       $revisedIncrement != "0.00 (PTC)" &&
                       $revisedIncrement != "0A"){
                        if(strstr($case, "A") !== false){
                            $sumAssistProfs += floatval($revisedIncrement);
                            $nAssistProfs++;
                        }
                        else if(strstr($case, "B") !== false){
                            $sumAssocProfs += floatval($revisedIncrement);
                            $nAssocProfs++;
                        }
                        else if(strstr($case, "C") !== false){
                            $sumFullProfs += floatval($revisedIncrement);
                            $nFullProfs++;
                        }
                    }
                    
                    if(floatval($revisedIncrement) > floatval($increment)){
                        $nRaised++;
                    }
                    else if(floatval($revisedIncrement) < floatval($increment)){
                        $nLowered++;
                    }
                    
                    if($revisedIncrement != "" && $revisedIncrement <= "1.00" && $revisedIncrement != "0A" && strstr($revisedIncrement, "PTC") === false){
                        $nLessThan1++;
                    }
                    if($revisedIncrement != "" && $revisedIncrement >= "1.25"){
                        $nGreaterThan1++;
                    }

                    $positions = array();
                    $depts = array("All");
                    $unis = $person->getUniversitiesDuring(($year-1)."-07-01", ($year)."-07-01");
                    foreach($unis as $uni){
                        if(in_array($uni['department'], self::$validDepts) !== false){
                            $positions[] = $uni['position'];
                            $depts[] = $uni['department'];
                        }
                    }
                    
                    if(strstr($case, "A") !== false || array_search("Assistant Professor", $positions) !== false){
                        $short = "A";
                    }
                    else if(strstr($case, "B") !== false || array_search("Associate Professor", $positions) !== false){
                        $short = "B";
                    }
                    else if(strstr($case, "C") !== false || array_search("Professor", $positions) !== false){
                        $short = "C";
                    }
                    
                    $nStudents = 0;
                    $nUgrad = 0;
                    $nGrad = 0;
                    foreach($person->getCoursesDuring(($year-1)."-07-01", ($year)."-06-30") as $course){
                        if($course->totEnrl >= 1 && $course->component == "LEC"){
                            $nStudents += $course->totEnrl;
                            $level = substr($course->catalog, 0, 1);
                            // Exceptions
                            if($course->subject == "MED" && ($course->catalog == "521" || $course->catalog == "525")){ $level = "1"; }
                            if($level <= "4"){
                                $nUgrad++;
                            }
                            if($level > "4"){
                                $nGrad++;
                            }
                        }
                    }
                    
                    foreach($depts as $dept){
                        if($nUgrad == 0 && $nGrad == 0){
                            @$courses[$short][$dept]["0"]++;
                        }
                        else{
                            @$courses[$short][$dept][($nUgrad+$nGrad)." ($nUgrad Ugrad, $nGrad Grad)"]++;
                        }
                    }
                    
                    foreach($depts as $dept){
                        if($nStudents == 0){
                            @$students[$short][$dept]["0"]++;
                        }
                        else if($nStudents >= 1 && $nStudents <= 20){
                            @$students[$short][$dept]["1-20"]++;
                        }
                        else if($nStudents >= 21 && $nStudents <= 40){
                            @$students[$short][$dept]["21-40"]++;
                        }
                        else if($nStudents >= 41 && $nStudents <= 60){
                            @$students[$short][$dept]["41-60"]++;
                        }
                        else if($nStudents >= 61 && $nStudents <= 80){
                            @$students[$short][$dept]["61-80"]++;
                        }
                        else if($nStudents >= 81 && $nStudents <= 100){
                            @$students[$short][$dept]["81-100"]++;
                        }
                        else if($nStudents >= 101 && $nStudents <= 150){
                            @$students[$short][$dept]["101-150"]++;
                        }
                        else if($nStudents >= 151 && $nStudents <= 200){
                            @$students[$short][$dept]["151-200"]++;
                        }
                        else if($nStudents >= 201 && $nStudents <= 300){
                            @$students[$short][$dept]["201-300"]++;
                        }
                        else if($nStudents >= 301 && $nStudents <= 500){
                            @$students[$short][$dept]["301-500"]++;
                        }
                        else if($nStudents >= 501){
                            @$students[$short][$dept]["501+"]++;
                        }
                    }
                    
                    if($revisedIncrement == "0A" || strstr($revisedIncrement, "PTC") !== false){
                        @$increments[$short]["PTC"]++;
                    }
                    else if($revisedIncrement == "0B" || $revisedIncrement == "0C" || $revisedIncrement == "0D"){
                        @$increments[$short]["0.00"]++;
                    }
                    else if($revisedIncrement >= "2.00"){
                        @$increments[$short]["2.00+"]++;
                    }
                    for($i=0.5;$i<2.00;$i+=0.25){
                        $inc = number_format($i, 2);
                        if($revisedIncrement == $inc){
                            @$increments[$short][$inc]++;
                        }
                    }
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
                    if($pub->getType() == "Patent" && $pub->getStatus() == "Awarded"){
                        $publications['nonpr']['patents'][$pub->getId()] = $pub;
                    }
                }
            }
            
            $data = array('nPeople' => $nPeople,
                          'nProfs' => $nProfs,
                          'nFSO' => $nFSO,
                          'nUncappedFSO' => $nUncappedFSO,
                          'publications' => $publications,
                          'rankings' => $rankings,
                          'nAssistProfs' => $nAssistProfs,
                          'nAssocProfs' => $nAssocProfs,
                          'nFullProfs' => $nFullProfs,
                          'sumAssistProfs' => $sumAssistProfs,
                          'sumAssocProfs' => $sumAssocProfs,
                          'sumFullProfs' => $sumFullProfs,
                          'sumFSO' => $sumFSO,
                          'nLowered' => $nLowered,
                          'nRaised' => $nRaised,
                          'nLessThan1' => $nLessThan1,
                          'nGreaterThan1' => $nGreaterThan1,
                          'increments' => $increments,
                          'courses' => $courses,
                          'students' => $students);
            
            $blob = new ReportBlob(BLOB_ARRAY, $year, 0, 0);
            $blob_address = ReportBlob::create_address("RP_FEC_REFLECTIONS", "REFLECTIONS", "REFLECTIONS", getFaculty());
            $blob->store($data, $blob_address, false);
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

        // Publication Stats
        $wgOut->addHTML("<h3>Publication Stats</h3>
                         <table class='wikitable'>");
        $wgOut->addHTML("   <tr><td><b>Journals:</b></td><td>".@count($publications['pr']['journals'])."</td></tr>");
        $wgOut->addHTML("   <tr><td><b>Conferences:</b></td><td>".@count($publications['pr']['conference'])."</td></tr>");
        $wgOut->addHTML("   <tr><td><b>Book Chapters:</b></td><td>".@count($publications['pr']['book_chapters'])."</td></tr>");
        $wgOut->addHTML("   <tr><td><b>Other Refereed:</b></td><td>".@count($publications['pr']['others'])."</td></tr>");
        $wgOut->addHTML("   <tr><td><b>Total Refereed:</b></td><td>".($totalRefereed)."</td></tr>");
        $wgOut->addHTML("   <tr><td><b># Faculty:</b></td><td>$nPeople</td></tr>");
        $wgOut->addHTML("   <tr><td><b>Average/Faculty:</b></td><td>".number_format($totalRefereed/max(1, $nPeople), 4)."</td></tr>");
        $wgOut->addHTML("   <tr><td><b>Non-refereed:</b></td><td>".@count($publications['nonpr']['journals'])."</td></tr>");
        $wgOut->addHTML("   <tr><td><b>Books:</b></td><td>".@count($publications['nonpr']['books'])."</td></tr>");
        $wgOut->addHTML("   <tr><td><b>Patents:</b></td><td>".@count($publications['nonpr']['patents'])."</td></tr>");
        $wgOut->addHTML("   <tr><th colspan='2'>Ranking Percentiles</th></tr>");
        $wgOut->addHTML("   <tr><td><b>1-10%:</b></td><td>".number_format($n1_10/max(1, count($rankings)), 4)."</td></tr>");
        $wgOut->addHTML("   <tr><td><b>10-30%:</b></td><td>".number_format($n10_30/max(1, count($rankings)), 4)."</td></tr>");
        $wgOut->addHTML("   <tr><td><b>30-50%:</b></td><td>".number_format($n30_50/max(1, count($rankings)), 4)."</td></tr>");
        $wgOut->addHTML("   <tr><td><b>50-100%:</b></td><td>".number_format($n50_100/max(1, count($rankings)), 4)."</td></tr>");
        $wgOut->addHTML("   <tr><td><b>Unranked:</b></td><td>".number_format($unranked/max(1, count($rankings)), 4)."</td></tr>");
        $wgOut->addHTML("</table>");
        
        // FEC Stats
        $wgOut->addHTML("<h3>FEC Stats</h3>
                         <table class='wikitable'>");
        $wgOut->addHTML("   <tr><td style='width:250px;' valign='top'><b>Promotion from assistant to associate professor (with tenure)</b></td><td valign='top'>Check <a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=FECTable'>FEC Table</a></td></tr>");
        $wgOut->addHTML("   <tr><td valign='top'><b>Promotion from associate to full professor</b></td><td valign='top'>Check <a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=FECTable'>FEC Table</a></td></tr>");
        $wgOut->addHTML("   <tr><td valign='top'><b>Number of professoriate cases considered by FEC</b><br /><small>This does not include the Dean, Vice Dean, and Department Chairs</small></td><td valign='top'>{$nProfs}</td></tr>");
        $wgOut->addHTML("   <tr><td valign='top'><b>Average number of increments for a full professor</b></td><td valign='top'>".number_format($sumFullProfs/max($nFullProfs, 1), 2)."</td></tr>");
        $wgOut->addHTML("   <tr><td valign='top'><b>Average number of increments for an uncapped associate professor</b><br /><small>Note that this number has been lowered by the “almost-capped” professors</small></td><td valign='top'>".number_format($sumAssocProfs/max($nAssocProfs, 1), 2)."</td></tr>");
        $wgOut->addHTML("   <tr><td valign='top'><b>Average number of increments for an assistant professor</b></td><td valign='top'>".number_format($sumAssistProfs/max($nAssistProfs, 1), 2)."</td></tr>");
        $wgOut->addHTML("   <tr><td valign='top'><b>Number of increment recommendations that were lowered by FEC</b></td><td valign='top'>{$nLowered}</td></tr>");
        $wgOut->addHTML("   <tr><td valign='top'><b>Number of increment recommendations that were raised by FEC</b></td><td valign='top'>{$nRaised}</td></tr>");
        $wgOut->addHTML("   <tr><td valign='top'><b>FSO promotions</b></td><td valign='top'>Check <a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=FECTable'>FEC Table</a></td></tr>");
        $wgOut->addHTML("   <tr><td valign='top'><b>Number of FSO cases considered</b></td><td valign='top'>{$nFSO}</td></tr>");
        $wgOut->addHTML("   <tr><td valign='top'><b>Average increments received by non-capped FSOs</b></td><td valign='top'>".number_format($sumFSO/max($nUncappedFSO, 1), 2)."</td></tr>");
        $wgOut->addHTML("   <tr><td valign='top'><b>Total number of faculty that have a non-PTC increment of 1 and below</b></td><td valign='top'>{$nLessThan1}</td></tr>");
        $wgOut->addHTML("   <tr><td valign='top'><b>Total number of faculty that have an increment of 1.25 and above</b></td><td valign='top'>{$nGreaterThan1}</td></tr>");
        $wgOut->addHTML("</table>");
        
        // Merit Increment Distribution
        $wgOut->addHTML("<h3>Merit Increment Distribution</h3>
                         <table class='wikitable'>");
        $wgOut->addHTML("   <tr><th style='width:6em;'></th>
                                <th style='width:6em;'>Assistant</th>
                                <th style='width:6em;'>Associate</th>
                                <th style='width:6em;'>Full</th>
                            </tr>");
        $wgOut->addHTML("   <tr><td><b>0A, PTC</b></td>
                                <td align='right'>".@intval($increments["A"]["PTC"])."</td>
                                <td align='right'>".@intval($increments["B"]["PTC"])."</td>
                                <td align='right'>".@intval($increments["C"]["PTC"])."</td>
                            </tr>");
        $wgOut->addHTML("   <tr><td><b>0B, 0C, 0D</b></td>
                                <td align='right'>".@intval($increments["A"]["0.00"])."</td>
                                <td align='right'>".@intval($increments["B"]["0.00"])."</td>
                                <td align='right'>".@intval($increments["C"]["0.00"])."</td>
                            </tr>");
        for($i=0.5;$i<2.00;$i+=0.25){
            $inc = number_format($i, 2);
            $wgOut->addHTML("<tr><td><b>{$inc}</b></td>
                                <td align='right'>".@intval($increments["A"][$inc])."</td>
                                <td align='right'>".@intval($increments["B"][$inc])."</td>
                                <td align='right'>".@intval($increments["C"][$inc])."</td>
                             </tr>");
        }
        $wgOut->addHTML("   <tr><td><b>2.00+</b></td>
                                <td align='right'>".@intval($increments["A"]["2.00+"])."</td>
                                <td align='right'>".@intval($increments["B"]["2.00+"])."</td>
                                <td align='right'>".@intval($increments["C"]["2.00+"])."</td>
                            </tr>");
        $wgOut->addHTML("</table>");
        
        // Courses Taught
        $wgOut->addHTML("<h3>Courses Taught</h3>
                         <div id='courses{$year}'>
                            <ul>");
        foreach(self::$validDepts as $i => $dept){
            $wgOut->addHTML("<li><a href='#courses{$year}-{$i}'>{$dept}</a></li>");
        }
        $wgOut->addHTML("   </ul>");
        foreach(self::$validDepts as $i => $dept){
            $wgOut->addHTML("<div id='courses{$year}-{$i}' style='padding: 0;'>
                             <table class='wikitable'>
                                <tr><th style='width:6em;'></th>
                                    <th style='width:6em;'>Assistant</th>
                                    <th style='width:6em;'>Associate</th>
                                    <th style='width:6em;'>Full</th>
                                </tr>");
            if(isset($courses["A"][$dept]) && isset($courses["B"][$dept]) && isset($courses["C"][$dept])){
                $courseRows = @array_merge($courses["A"][$dept], $courses["B"][$dept], $courses["C"][$dept]);
                @array_multisort(array_keys($courseRows), SORT_NATURAL, $courseRows);
                if(is_array($courseRows)){
                    foreach($courseRows as $key => $row){
                        $keyLabel = ($key == "0") ? "No teaching" : $key;
                        $wgOut->addHTML("<tr>
                                            <td style='white-space:nowrap;'><b>{$keyLabel}</b></td>
                                            <td align='right'>".@intval($courses["A"][$dept][$key])."</td>
                                            <td align='right'>".@intval($courses["B"][$dept][$key])."</td>
                                            <td align='right'>".@intval($courses["C"][$dept][$key])."</td>
                                         </tr>");
                    }
                }
            }
            $wgOut->addHTML("</table></div>");
        }
        $wgOut->addHTML("</div>");
        
        // Students Taught
        $studentRows = array();
        if(isset($students["A"]["All"]) && isset($students["B"]["All"]) && isset($students["C"]["All"])){
            $studentRows = @array_merge($students["A"]["All"], $students["B"]["All"], $students["C"]["All"]);
            @array_multisort(array_keys($studentRows), SORT_NATURAL, $studentRows);
        }
            
        $wgOut->addHTML("<h3>Students Taught</h3>
                         <div id='students{$year}'>
                            <ul>");
        foreach(self::$validDepts as $i => $dept){
            $wgOut->addHTML("<li><a href='#students{$year}-{$i}'>{$dept}</a></li>");
        }
        $wgOut->addHTML("   </ul>");
        foreach(self::$validDepts as $i => $dept){
            $wgOut->addHTML("<div id='students{$year}-{$i}' style='padding: 0;'>
                             <table class='wikitable'>
                                <tr><th style='width:6em;'></th>
                                    <th style='width:6em;'>Assistant</th>
                                    <th style='width:6em;'>Associate</th>
                                    <th style='width:6em;'>Full</th>
                                </tr>");
            if(is_array($studentRows)){
                foreach($studentRows as $key => $row){
                    $wgOut->addHTML("<tr>
                                        <td><b>{$key}</b></td>
                                        <td align='right'>".@intval($students["A"][$dept][$key])."</td>
                                        <td align='right'>".@intval($students["B"][$dept][$key])."</td>
                                        <td align='right'>".@intval($students["C"][$dept][$key])."</td>
                                     </tr>");
                }
            }
            $wgOut->addHTML("</table></div>");
        }
        $wgOut->addHTML("</div>");
        
        $wgOut->addHTML("<script type='text/javascript'>
            $('#courses{$year}').tabs();
            $('#students{$year}').tabs();
        </script>");
    }
    
    function execute($par){
        global $wgOut;
        $this->getOutput()->setPageTitle("FEC Reflections");
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
    
    static function getBlobValue($rpType, $rpSection, $rpItem, $rpSubItem=0, $year=YEAR, $userId=0){
        $blob = new ReportBlob(BLOB_TEXT, $year, $userId, 0);
	    $blob_address = ReportBlob::create_address($rpType, $rpSection, $rpItem, $rpSubItem);
	    $blob->load($blob_address);
	    $blob_data = $blob->getData();
	    return $blob_data;
    }
    
    static function saveBlobValue($value, $rpType, $rpSection, $rpItem, $rpSubItem=0, $year=YEAR, $userId=0){
        $blob = new ReportBlob(BLOB_TEXT, $year, $userId, 0);
	    $blob_address = ReportBlob::create_address($rpType, $rpSection, $rpItem, $rpSubItem);
        $blob->store($value, $blob_address);
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $person = Person::newFromWgUser();
        if((new self)->userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "FECReflections") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("FEC Reflections", "$wgServer$wgScriptPath/index.php/Special:FECReflections", $selected);
        }
        return true;
    }
}

?>
