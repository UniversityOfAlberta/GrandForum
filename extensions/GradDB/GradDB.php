<?php

require_once("GradChairTable/GradChairTable.php");

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
        $me = Person::newFromWgUser();
        
        if(isset($_GET['pdf'])){
            $this->downloadPDF();
        }
        else if(isset($_GET['accept'])){
            $this->accept();
        }
        else if($me->isRole(NI)){
            if(isset($_GET['hqp']) && isset($_GET['term'])){
                $this->supervisorForm($_GET['hqp'], $_GET['term']);
            }
            else{
                $this->supervisorTable();
           }
        }
        else if($me->isRole(HQP)){
            $this->hqpTable();
        }
    }
    
    // View of the table of HQP for the supervisor
    function supervisorTable(){
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
            <p>If the HQP not in the table you can <a class='button' href='{$wgServer}{$wgScriptPath}/index.php/Special:GradDB?hqp=0&term={$term}'>Make a Contract</a> for any eligible HQP.</p>
            <table id='hqpTable' class='wikitable' frame='box' rules='all'>
                <thead>
                    <tr>
                        <th>HQP</th>
                        <th>Program</th>
                        <th style='width:1%;'>TA Eligible</th>
                        <th style='width:1%;'>HQP Accepted</th>
                        <th style='width:1%;'>Supervisor Accepted</th>
                        <th>Financial Form</th>
                    </tr>
                </thead>
                <tbody>");
        foreach(array_merge($me->getHQP(true), GradDBFinancial::getAttachedHQP($me->getId(), $term)) as $hqp){
            $universities = $hqp->getUniversitiesDuring($date, $date);
            foreach($universities as $university){
                if(in_array(strtolower($university['position']), Person::$studentPositions['grad'])){
                    $gradDBFinancial = GradDBFinancial::newFromTuple($hqp->getId(), $term);
                    $button = (!$gradDBFinancial->exists()) ? "<a class='button' href='{$wgServer}{$wgScriptPath}/index.php/Special:GradDB?hqp={$hqp->getId()}&term={$term}'>Make a Contract</a>" : "<a class='button' target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:GradDB?pdf={$gradDBFinancial->getMD5()}'>View Contract</a>";
                    $eligible = ($hqp->isTAEligible($date)) ? "<span style='font-size:2em;'>&#10003;</span>" : "";
                    $hqpAccepted = ($gradDBFinancial->hasHQPAccepted()) ? "<span style='font-size:2em;'>&#10003;</span>" : "";
                    $hasSupervisorAccepted = array();
                    if($gradDBFinancial->exists()){
                        foreach($gradDBFinancial->getSupervisors(true) as $sup){
                            $supervisor = $sup['supervisor'];
                            if($supervisor->getId() == 0){
                                continue;
                            }
                            if($gradDBFinancial->hasSupervisorAccepted($supervisor->getId())){
                                $hasSupervisorAccepted[] = "{$supervisor->getFullName()}: &#10003;";
                            }
                            else{
                                $hasSupervisorAccepted[] = "{$supervisor->getFullName()}: _";
                            }
                        }
                    }
                    $wgOut->addHTML("<tr>
                        <td><a href='{$hqp->getUrl()}'>{$hqp->getReversedName()}</a></td>
                        <td>{$university['position']}</td>
                        <td align='center'>{$eligible}</td>
                        <td align='center'>{$hqpAccepted}</td>
                        <td align='right' style='white-space: nowrap;'>".implode("<br />", $hasSupervisorAccepted)."</td>
                        <td align='center'>{$button}</td>
                    </tr>");
                    break;
                }
            }
        }
        $wgOut->addHTML("</tbody></table>
        <script type='text/javascript'>
            $('#hqpTable').DataTable({
                aLengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, 'All']
                ],
                iDisplayLength: -1
            });
            $('select[name=term]').change(function(){
                document.location = wgServer + wgScriptPath + '/index.php/Special:GradDB?term=' + $('select[name=term]').val();
            });
        </script>");
    }
    
    function hqpTable(){
        global $wgOut, $wgServer, $wgScriptPath, $wgMessage, $config;
    }
    
    // View of the supervisor form
    function supervisorForm($hqpId, $term){
        global $wgOut, $wgServer, $wgScriptPath, $wgMessage, $config;
        $me = Person::newFromWgUser();
        if(isset($_POST['hqp'])){
            $hqp = Person::newFromId($_POST['hqp']);
        }
        else{
            $hqp = Person::newFromId($hqpId);
        }
        $terms = (isset($_POST['terms'])) ? $_POST['terms'] : array($term);
        foreach($terms as $t){
            $gradDBFinancial = GradDBFinancial::newFromTuple($hqp->getId(), $t);
            if($gradDBFinancial->exists()){
                $wgOut->addHTML("This entry already exists and cannot be edited");
                return;
            }
        }
        if(isset($_POST['submit'])){
            // Handle Form Submit
            $error = "";
            $gradDBFinancial->userId = $hqp->getId();
            $gradDBFinancial->term = implode(",", $_POST['terms']);
            
            $gradDBFinancial->supervisors = array();
            foreach($_POST['sup'] as $key => $sup){
                $gradDBFinancial->supervisors[] = $gradDBFinancial->emptySupervisor($_POST['sup'][$key], 
                                                                                    $_POST['type'][$key], 
                                                                                    $_POST['account'][$key],
                                                                                    $_POST['percent'][$key]);
            }

            if(!$gradDBFinancial->exists()){
                $gradDBFinancial->create();
            }
            
            if($error == ""){
                $gradDBFinancial->generatePDF();
                $wgMessage->addSuccess("Financial Information updated");
                
                $message = "<p>{$me->getFullName()} has filled out a contract for {$gradDBFinancial->getTerm()}.  The PDF is attached, so review the terms and then <a href='{$wgServer}{$wgScriptPath}/index.php/Special:GradDB?accept={$gradDBFinancial->getMD5()}'><b>Click Here</b></a> to accept it.</p>
                            <p> - {$config->getValue('networkName')}</p>";
                self::mail("dwt@ualberta.ca", "Contract for {$gradDBFinancial->getTerm()}", $message, $gradDBFinancial->getPDF(), "Contract.pdf");

                redirect("{$wgServer}{$wgScriptPath}/index.php/Special:GradDB?term={$term}");
            }
        }
        
        // Form
        $date = GradDBFinancial::term2Date($term);
        $hqps = Person::getAllPeople(HQP);
        $hqpNames = array("");
        foreach($hqps as $hqp){
            $email = str_replace("@ualberta.ca", "", $hqp->getEmail());
            if($email != ""){
                $email = "($email)";
            }
            $hqpNames[$hqp->getId()] = "{$hqp->getNameForForms()} {$email}";
        }
        $students = new SelectBox("hqp", "Student", $gradDBFinancial->userId, $hqpNames);
        $students->forceKey = true;
        $students->attr("data-placeholder", "Choose a student...");
        $terms = new VerticalCheckBox("terms", "Terms", $gradDBFinancial->getTerms(), GradDBFinancial::yearTerms($term));
        $wgOut->addHTML("<form method='POST'>
                            <table>
                                <tr>
                                    <td><b>Student:</b></td>
                                    <td>{$students->render()}</td>
                                </tr>
                                <tr>
                                    <td><b>Term(s):</b></td>
                                    <td>{$terms->render()}</td>
                                </tr>
                            </table><div id='supervisors'>");
        $names = array("");
        foreach(Person::getAllPeople(NI) as $faculty){
            $names[$faculty->getId()] = $faculty->getNameForForms();
        }
        $wgOut->addHTML("<table class='wikitable'>
                            <thead>
                                <tr>
                                    <th>Account</th>
                                    <th>Type</th>
                                    <th>Percent</th>
                                    <th>Award ($)</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>");
        $speedCodes = array("");
        foreach($me->getSpeedCodes() as $code){
            $speedCodes[$code['speedcode']] = "{$code['speedcode']} - {$code['title']}";
        }
        foreach(array_merge(array($gradDBFinancial->emptySupervisor()), $gradDBFinancial->getSupervisors()) as $supervisor){
            $account = new SelectBox("account[]", "Account", $supervisor['account'], $speedCodes);
            $type = new SelectBox("type[]", "Type", $supervisor['type'], array("GTA" => "GTA", 
                                                                               "GRA" => "GRA", 
                                                                               "GRAF" => "GRAF",
                                                                               "Fee Differential" => "Fee Differential",
                                                                               "Top Up" => "Top Up"));
            $percent = new SelectBox("percent[]", "% Funding", $supervisor['percent'], array("100" => "100",
                                                                                             "90" => "90",
                                                                                             "80" => "80",
                                                                                             "70" => "70",
                                                                                             "60" => "60",
                                                                                             "50" => "50",
                                                                                             "40" => "40",
                                                                                             "30" => "30",
                                                                                             "20" => "20",
                                                                                             "10" => "10"));
            
            $wgOut->addHTML("
                <tr>
                    <td>{$account->render()}</td>
                    <td>{$type->render()}</td>
                    <td>{$percent->render()}</td>
                    <td align='right'><span class='award'></span></td>
                    <td><button class='removeSupervisor' type='button'>Remove Line Item</button></td>
                </tr>");
            }
            $wgOut->addHTML("</tbody></table></div><button class='addSupervisor' type='button'>Add Line Item</button><br /><br /><input type='submit' name='submit' value='Submit' />
            </form>
            <script type='text/javascript'>
                var template = $('#supervisors tbody tr').first().detach();
                
                function initSupervisors(){
                    var parent = $('#supervisors tbody tr').last();
                    
                    $('select[name=\"account[]\"]', parent).chosen();
                    
                    $('select[name=\"percent[]\"]', parent).change(function(){
                        var percent = parseInt($(this).val())/100;
                        $('span.award', parent).text('$' + (900*percent*4));
                    }).change();
                    
                    $('.removeSupervisor', parent).click(function(){
                        $(this).closest('tr').remove();
                    });
                }
                
                $('.addSupervisor').click(function(){
                    $('#supervisors tbody').append(template[0].outerHTML);
                    initSupervisors();
                });
                
                initSupervisors();
                
                $('select[name=\"hqp\"]').chosen();
                
            </script>");
    }
    
    function downloadPDF(){
        $gradDBFinancial = GradDBFinancial::newFromMD5($_GET['pdf']);
        $gradDBTimeUse = GradDBTimeUse::newFromMD5($_GET['pdf']);
        if($gradDBFinancial->exists()){
            if($gradDBFinancial->isAllowedToView()){
                header("Content-Type: application/pdf");
                header("Content-Disposition:filename=\"{$gradDBFinancial->getHQP()->getName()}_{$gradDBFinancial->getTerm()}_Appointment.pdf\"");
                echo $gradDBFinancial->getPDF();
                exit;
            }
            else{
                permissionError();
            }
        }
        else if($gradDBTimeUse->exists()){
            if($gradDBTimeUse->isAllowedToView()){
                header("Content-Type: application/pdf");
                header("Content-Disposition:filename=\"{$gradDBTimeUse->getHQP()->getName()}_{$gradDBTimeUse->getTerm()}_TimeUse.pdf\"");
                echo $gradDBTimeUse->getPDF();
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
    
    function accept(){
        global $wgMessage, $wgServer, $wgScriptPath, $config;
        $me = Person::newFromWgUser();
        $gradDBFinancial = GradDBFinancial::newFromMD5($_GET['accept']);
        if($gradDBFinancial->exists() && $gradDBFinancial->isAllowedToView() && ($gradDBFinancial->getHQP()->getId() == $me->getId() || 
                                                                                 $gradDBFinancial->isSupervisor($me->getId()))){
            if($gradDBFinancial->getHQP()->getId() == $me->getId() && !$gradDBFinancial->hasHQPAccepted()){
                $gradDBFinancial->hqpAccepted = currentTimeStamp();
            }
            else if($gradDBFinancial->isSupervisor($me->getId()) && !$gradDBFinancial->hasSupervisorAccepted($me->getId())){
                $gradDBFinancial->setSupervisorField($me->getId(), 'accepted', currentTimeStamp());
            }
            else{
                $wgMessage->addError("You have already accepted this contract.");
                return;
            }
            
            $gradDBFinancial->update();
            $gradDBFinancial->generatePDF();
            $message = "<p>{$me->getFullName()} has accepted the contract appointment for {$gradDBFinancial->getTerm()}.
                        <p> - {$config->getValue('networkName')}</p>";
            self::mail("dwt@ualberta.ca", "Contract for {$gradDBFinancial->getTerm()} Accepted", $message, $gradDBFinancial->getPDF(), "Contract.pdf");
            $wgMessage->addSuccess("Thank you for accepting this contract.");
        }
        else{
            $wgMessage->addError("This contract doesn't exist.");
        }
    }

}

?>
