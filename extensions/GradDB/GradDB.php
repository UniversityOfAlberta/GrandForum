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
            <table id='hqpTable' class='wikitable' frame='box' rules='all'>
                <thead>
                    <tr>
                        <th>HQP</th>
                        <th>Program</th>
                        <th style='width:1%;'>Eligible</th>
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
                    $button = ($hqp->isTAEligible($date) && !$gradDBFinancial->exists()) ? "<a class='button' href='{$wgServer}{$wgScriptPath}/index.php/Special:GradDB?hqp={$hqp->getId()}&term={$term}'>Edit</a>" : "";
                    $button = ($gradDBFinancial->exists()) ? "<a class='button' target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:GradDB?pdf={$gradDBFinancial->getMD5()}'>PDF</a>" : $button;
                    $eligible = ($hqp->isTAEligible($date)) ? "<span style='font-size:2em;'>&#10003;</span>" : "";
                    $hqpAccepted = ($gradDBFinancial->hasHQPAccepted()) ? "<span style='font-size:2em;'>&#10003;</span>" : "";
                    $hasSupervisorAccepted = array();
                    foreach($gradDBFinancial->getSupervisors(true) as $sup){
                        $supervisor = $sup['supervisor'];
                        if($supervisor->getId() == 0){
                            continue;
                        }
                        if($gradDBFinancial->hasSupervisorAccepted($supervisor->getId())){
                            $hasSupervisorAccepted[] = "{$supervisor->getFullName()}: &#10003;";
                        }
                        else{
                            $hasSupervisorAccepted[] = "{$supervisor->getFullName()}: __";
                        }
                    }
                    $wgOut->addHTML("<tr>
                        <td><a href='{$hqp->getUrl()}'>{$hqp->getReversedName()}</a></td>
                        <td>{$university['position']}</td>
                        <td align='center'>{$eligible}</td>
                        <td align='center'>{$hqpAccepted}</td>
                        <td align='center' style='white-space: nowrap;'>".implode("<br />", $hasSupervisorAccepted)."</td>
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
    
    function hqpTable(){
        global $wgOut, $wgServer, $wgScriptPath, $wgMessage, $config;
    }
    
    // View of the supervisor form
    function supervisorForm($hqpId, $term){
        global $wgOut, $wgServer, $wgScriptPath, $wgMessage, $config;
        $me = Person::newFromWgUser();
        $hqp = Person::newFromId($hqpId);
        
        $gradDBFinancial = GradDBFinancial::newFromTuple($hqp->getId(), $term);
        if($gradDBFinancial->exists()){
            $wgOut->addHTML("This entry already exists and cannot be edited");
            return;
        }
        if(isset($_POST['submit'])){
            // Handle Form Submit
            $gradDBFinancial->userId = $hqp->getId();
            $gradDBFinancial->term = implode(",", $_POST['terms']);
            
            $gradDBFinancial->supervisors = array();
            foreach($_POST['sup'] as $key => $sup){
                $gradDBFinancial->supervisors[] = $gradDBFinancial->emptySupervisor($_POST['sup'][$key], 
                                                                                    $_POST['type'][$key], 
                                                                                    $_POST['account'][$key], 
                                                                                    $_POST['hours'][$key], 
                                                                                    $_POST['percent'][$key]);
            }

            if(!$gradDBFinancial->exists()){
                $gradDBFinancial->create();
            }
            else{
                $gradDBFinancial->update();
            }
            $gradDBFinancial->generatePDF();
            $wgMessage->addSuccess("Financial Information updated");
            
            $message = "<p>{$me->getFullName()} has filled out a funding appointment for {$gradDBFinancial->getTerm()}.  The PDF is attached, so review the terms and then <a href='{$wgServer}{$wgScriptPath}/index.php/Special:GradDB?accept={$gradDBFinancial->getMD5()}'><b>Click Here</b></a> to accept it.</p>
                        <p> - {$config->getValue('networkName')}</p>";
            self::mail("dwt@ualberta.ca", "Supervisor Funding for {$gradDBFinancial->getTerm()}", $message, $gradDBFinancial->getPDF(), "Funding.pdf");

            redirect("{$wgServer}{$wgScriptPath}/index.php/Special:GradDB?term={$term}");
        }

        $terms = new VerticalCheckBox("terms", "Terms", $gradDBFinancial->getTerms(), GradDBFinancial::yearTerms($term));
        $wgOut->addHTML("<form method='POST'>
                            <table>
                                <tr>
                                    <td><b>Student:</b></td>
                                    <td>{$hqp->getReversedName()}</td>
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
        foreach(array_merge(array($gradDBFinancial->emptySupervisor()), $gradDBFinancial->getSupervisors()) as $supervisor){
            $sup = new SelectBox("sup[]", "Supervisor", $supervisor['supervisor'], $names);
            $sup->forceKey = true;
            $sup->attr("data-placeholder", "Choose an account holder...");
            $account = new TextField("account[]", "Account", $supervisor['account']);
            $type = new SelectBox("type[]", "Type", $supervisor['type'], array("GTA" => "GTA", 
                                                                               "GRA" => "GRA", 
                                                                               "GRAF" => "GRAF",
                                                                               "Fee Differential" => "Fee Differential"));
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
            $hours = new SelectBox("hours[]", "Hours per week", $supervisor['hours'], array("12" => "12", 
                                                                                            "6" => "6",
                                                                                            "N/A" => "N/A"));
            
            $wgOut->addHTML("
                <fieldset>
                <legend>Account Holder: <span style='font-weight: normal;'>{$sup->render()}</span> <button class='removeSupervisor' type='button'>Remove Account Holder</button></legend>
                
                <table>
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
                        <td><b>% Funding:</b></td>
                        <td>
                            {$percent->render()}
                        </td>
                    </tr>
                    <tr>
                        <td><b>Hours/Week:</b></td>
                        <td>{$hours->render()}</td>
                    </tr>
                </table>
                </fieldset>");
            }
            $wgOut->addHTML("</div><button class='addSupervisor' type='button'>Add Account Holder</button><br /><br /><input type='submit' name='submit' value='Submit' />
            </form>
            <script type='text/javascript'>
                var template = $('#supervisors fieldset').first().detach();
                
                function initSupervisors(){
                    var parent = $('#supervisors fieldset').last();
                    $('select[name=\"type[]\"]', parent).change(function(){
                        if($('select[name=\"type[]\"]', parent).val() == 'GRAF'){
                            $(\"select[name='hours[]'] option[value='12']\", parent).hide();
                            $(\"select[name='hours[]'] option[value='6']\", parent).hide();
                            $(\"select[name='hours[]'] option[value='N/A']\", parent).show();
                            $(\"select[name='hours[]']\", parent).val('N/A');
                        }
                        else{
                            $(\"select[name='hours[]'] option[value='12']\", parent).show()
                            $(\"select[name='hours[]'] option[value='6']\", parent).show();
                            $(\"select[name='hours[]'] option[value='N/A']\", parent).hide();
                            $(\"select[name='hours[]']\", parent).val('12');
                        }
                    });
                    
                    $('select[name=\"type[]\"]', parent).change();
                    $('select[name=\"sup[]\"]', parent).chosen();
                
                    $('.removeSupervisor', parent).click(function(){
                        $(this).closest('fieldset').remove();
                    });
                }
                
                $('.addSupervisor').click(function(){
                    $('#supervisors').append(template[0].outerHTML);
                    initSupervisors();
                });
                
                initSupervisors();
                
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
                $wgMessage->addError("You have already accepted this Funding.");
                return;
            }
            
            $gradDBFinancial->update();
            $gradDBFinancial->generatePDF();
            $message = "<p>{$me->getFullName()} has accepted the funding appointment for {$gradDBFinancial->getTerm()}.
                        <p> - {$config->getValue('networkName')}</p>";
            self::mail("dwt@ualberta.ca", "Supervisor Funding for {$gradDBFinancial->getTerm()} Accepted", $message, $gradDBFinancial->getPDF(), "Funding.pdf");
            $wgMessage->addSuccess("Thank you for accepting this Funding.");
        }
        else{
            $wgMessage->addError("This funding doesn't exist.");
        }
    }

}

?>
