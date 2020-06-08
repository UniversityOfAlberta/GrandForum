<?php
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['GradDB'] = 'GradDB'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['GradDB'] = $dir . 'GradDB.i18n.php';
$wgSpecialPageGroups['GradDB'] = 'network-tools';

class GradDB extends SpecialPage{

    static function mail($to, $subject, $message, $pdf, $fileName){
        global $config;

        $attachment = chunk_split(base64_encode($pdf));
        $eol = PHP_EOL;
        $separator = md5(time());

        $headers = "From: {$config->getValue('networkName')} <{$config->getValue('supportEmail')}>$eol";
        $headers .= 'MIME-Version: 1.0' .$eol;
        $headers .= "Content-Type: multipart/mixed; boundary=\"".$separator."\"";

        $body = "--".$separator.$eol;
        $body .= "Content-Type: text/html; charset=\"iso-8859-1\"".$eol;
        $body .= "Content-Transfer-Encoding: 7bit".$eol.$eol;
        $body .= "{$message}".$eol;

        $body .= "--".$separator.$eol;
        $body .= "Content-Type: text/html; charset=\"iso-8859-1\"".$eol;
        $body .= "Content-Transfer-Encoding: 8bit".$eol.$eol;

        $body .= "--".$separator.$eol;
        $body .= "Content-Type: application/pdf; name=\"".$fileName."\"".$eol; 
        $body .= "Content-Transfer-Encoding: base64".$eol;
        $body .= "Content-Disposition: attachment".$eol.$eol;
        $body .= $attachment.$eol;
        $body .= "--".$separator."--";
        
        mail($to, $subject, $body, $headers);
    }

    function GradDB() {
        parent::__construct("GradDB", HQP.'+', true);
    }

    function execute($par){
        global $wgOut;
        if(isset($_GET['hqp']) && isset($_GET['term'])){
            $this->supervisorForm($_GET['hqp'], $_GET['term']);
        }
        else if(isset($_GET['pdf'])){
            $this->downloadPDF();
        }
        else if(isset($_GET['accept'])){
            $this->hqpAccept();
        }
        else{
            $this->hqpTable();
        }
    }
    
    // View of the table of HQP for the supervisor
    function hqpTable(){
        global $wgOut, $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        $terms = array();
        for($y = date('Y'); $y > date('Y')-10; $y--){
            $terms["Fall$y"] = "Fall$y";
            $terms["Spring/Summer$y"] = "Spring/Summer$y";
            $terms["Winter$y"] = "Winter$y";
        }
        if(isset($_GET['term']) && in_array($_GET['term'], $terms)){
            $term = $_GET['term'];
        }
        else{
            $year = date('Y');
            $month = date('n');
            if($month == 1){
                $term = "Winter{$year}";
            }
            else if($month >= 2 && $month < 5){
                $term = "Spring/Summer{$year}";
            }
            else if($month >= 5 && $month < 9){
                $term = "Fall{$year}";
            }
            else if($month >= 9){
                $term = "Winter".($year+1);
            }
        }
        $date = GradDBFinancial::term2Date($term);
        
        $termSelect = new SelectBox("term", "Term", $term, $terms);
        $wgOut->addHTML("<div><span class='label'>Term:</span> {$termSelect->render()}</div><br />
            <table id='hqpTable' class='wikitable' frame='box' rules='all'>
                <thead>
                    <tr>
                        <th>HQP</th>
                        <th>Program</th>
                        <th style='width:1%;'>Eligible</th>
                        <th style='width:1%;'>HQP Accepted</th>
                        <th>Financial Form</th>
                    </tr>
                </thead>
                <tbody>");
        foreach($me->getHQP(true) as $hqp){
            $universities = $hqp->getUniversitiesDuring($date, $date);
            foreach($universities as $university){
                if(in_array(strtolower($university['position']), Person::$studentPositions['grad'])){
                    $gradDBFinancial = GradDBFinancial::newFromTuple($hqp->getId(), $me->getId(), $term);
                    $button = ($hqp->isTAEligible($date) && !$gradDBFinancial->exists()) ? "<a class='button' href='{$wgServer}{$wgScriptPath}/index.php/Special:GradDB?hqp={$hqp->getId()}&term={$term}'>Edit</a>" : "";
                    $button = ($gradDBFinancial->exists()) ? "<a class='button' target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:GradDB?pdf={$gradDBFinancial->getMD5()}'>PDF</a>" : $button;
                    $eligible = ($hqp->isTAEligible($date)) ? "<span style='font-size:2em;'>&#10003;</span>" : "";
                    $hqpAccepted = ($gradDBFinancial->hasHQPAccepted()) ? "<span style='font-size:2em;'>&#10003;</span>" : "";
                    $supervisorAccepted = ($gradDBFinancial->hasSupervisorAccepted()) ? "<span style='font-size:2em;'>&#10003;</span>" : "";
                    $wgOut->addHTML("<tr>
                        <td><a href='{$hqp->getUrl()}'>{$hqp->getReversedName()}</a></td>
                        <td>{$university['position']}</td>
                        <td align='center'>{$eligible}</td>
                        <td align='center'>{$hqpAccepted}</td>
                        <td align='center'>{$button}</td>
                    </tr>");
                    break;
                }
            }
        }
        $wgOut->addHTML("</tbody></table>
        <script type='text/javascript'>
            $('#hqpTable').DataTable();
            $('select[name=term]').change(function(){
                document.location = wgServer + wgScriptPath + '/index.php/Special:GradDB?term=' + $('select[name=term]').val();
            });
        </script>");
    }
    
    // View of the supervisor form
    function supervisorForm($hqpId, $term){
        global $wgOut, $wgServer, $wgScriptPath, $wgMessage, $config;
        $me = Person::newFromWgUser();
        $hqp = Person::newFromId($hqpId);
        
        $gradDBFinancial = GradDBFinancial::newFromTuple($hqp->getId(), $me->getId(), $term);
        if($gradDBFinancial->exists()){
            $wgOut->addHTML("This entry already exists and cannot be edited");
            return;
        }
        if(isset($_POST['submit'])){
            // Handle Form Submit
            $gradDBFinancial->userId = $hqp->getId();
            $gradDBFinancial->supervisor = $me->getId();
            $gradDBFinancial->term = $term;
            $gradDBFinancial->start = $_POST['start'];
            $gradDBFinancial->end = $_POST['end'];
            $gradDBFinancial->account = $_POST['account'];
            $gradDBFinancial->type = $_POST['type'];
            $gradDBFinancial->hours = $_POST['hours'];
            $gradDBFinancial->supervisorAccepted = currentTimeStamp();
            if(!$gradDBFinancial->exists()){
                $gradDBFinancial->create();
            }
            else{
                $gradDBFinancial->update();
            }
            $gradDBFinancial->generatePDF();
            $wgMessage->addSuccess("Financial Information updated");
            
            $message = "<p>{$gradDBFinancial->getSupervisor()->getFullName()} has filled out a funding appointment for {$gradDBFinancial->getTerm()}.  The PDF is attached, so review the terms and then <a href='{$wgServer}{$wgScriptPath}/index.php/Special:GradDB?accept={$gradDBFinancial->getMD5()}'><b>Click Here</b></a> to accept it.</p>
                        <p> - {$config->getValue('networkName')}</p>";
            self::mail("dwt@ualberta.ca", "Supervisor Funding for {$gradDBFinancial->getTerm()}", $message, $gradDBFinancial->getPDF(), "Funding.pdf");

            redirect("{$wgServer}{$wgScriptPath}/index.php/Special:GradDB?term={$term}");
        }
        
        $account = new TextField("account", "Account", $gradDBFinancial->getAccount());
        $type = new SelectBox("type", "Type", $gradDBFinancial->getType(), array("GTA" => "GTA", 
                                                                                 "GRA" => "GRA", 
                                                                                 "GRAF" => "GRAF"));
        $hours = new SelectBox("hours", "Hours per week", $gradDBFinancial->getHours(), array("12" => "12", 
                                                                                              "6" => "6",
                                                                                              "N/A" => "N/A"));
        $startDate = new CalendarField("start", "Start Date", $gradDBFinancial->getStart());
        $endDate = new CalendarField("end", "End Date", $gradDBFinancial->getEnd());
        $wgOut->addHTML("<form method='POST'>
            <table>
                <tr>
                    <td><b>Student:</b></td>
                    <td>{$hqp->getReversedName()}</td>
                </tr>
                <tr>
                    <td><b>Term:</b></td>
                    <td>{$term}</td>
                </tr>
                <tr>
                    <td><b>Start Date:</b></td>
                    <td>{$startDate->render()}</td>
                </tr>
                <tr>
                    <td><b>End Date:</b></td>
                    <td>{$endDate->render()}</td>
                </tr>
                <tr>
                    <td><b>Account:</b></td>
                    <td>{$account->render()}</td>
                </tr>
                <tr>
                    <td><b>Type:</b></td>
                    <td>
                        {$type->render()}
                    </td>
                </tr>
                <tr>
                    <td><b>Hours/Week:</b></td>
                    <td>{$hours->render()}</td>
                </tr>
                <tr>
                    <td></td><td><input type='submit' name='submit' value='Submit' /></td>
                </tr>
            </table></form>
            <script type='text/javascript'>
                $('select[name=type]').change(function(){
                    if($('select[name=type]').val() == 'GRAF'){
                        $(\"select[name=hours] option[value='12']\").hide();
                        $(\"select[name=hours] option[value='6']\").hide();
                        $(\"select[name=hours] option[value='N/A']\").show();
                        $('select[name=hours]').val('N/A');
                    }
                    else{
                        $(\"select[name=hours] option[value='12']\").show()
                        $(\"select[name=hours] option[value='6']\").show();
                        $(\"select[name=hours] option[value='N/A']\").hide();
                        $('select[name=hours]').val('{$gradDBFinancial->getHours()}');
                    }
                });
                $('select[name=type]').change();
            </script>");
    }
    
    function downloadPDF(){
        $gradDBFinancial = GradDBFinancial::newFromMD5($_GET['pdf']);
        if($gradDBFinancial->exists()){
            if($gradDBFinancial->isAllowedToView()){
                header("Content-Type: application/pdf");
                header("Content-Disposition:filename=\"{$gradDBFinancial->getHQP()->getName()}_{$gradDBFinancial->getTerm()}.pdf\"");
                echo $gradDBFinancial->getPDF();
                exit;
            }
            else{
                permissionError();
            }
        }
        else{
            $wgOut->addHTML("This PDF doesn't exist.");
        }
    }
    
    function hqpAccept(){
        global $wgMessage, $wgServer, $wgScriptPath, $config;
        $me = Person::newFromWgUser();
        $gradDBFinancial = GradDBFinancial::newFromMD5($_GET['accept']);
        if($gradDBFinancial->exists() && $gradDBFinancial->getHQP()->getId() == $me->getId()){
            if(!$gradDBFinancial->hasHQPAccepted()){
                $gradDBFinancial->hqpAccepted = currentTimeStamp();
                $gradDBFinancial->generatePDF();
                $message = "<p>{$gradDBFinancial->getHQP()->getFullName()} has accepted the funding appointment for {$gradDBFinancial->getTerm()}.
                            <p> - {$config->getValue('networkName')}</p>";
                self::mail("dwt@ualberta.ca", "Supervisor Funding for {$gradDBFinancial->getTerm()} Accepted", $message, $gradDBFinancial->getPDF(), "Funding.pdf");
                $wgMessage->addSuccess("Thank you for accepting this Funding.");
            }
            else{
                $wgMessage->addError("You have already accepted this Funding.");
            }
        }
        else{
            $wgMessage->addError("This funding doesn't exist.");
        }
    }

}

?>
