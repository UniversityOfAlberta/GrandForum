<?php
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['CAPSCompleteRegister'] = 'CAPSCompleteRegister'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['CAPSCompleteRegister'] = $dir . 'CAPSCompleteRegister.i18n.php';
$wgSpecialPageGroups['CAPSCompleteRegister'] = 'network-tools';

function runCAPSCompleteRegister($par) {
    CAPSCompleteRegister::execute($par);
}

class CAPSCompleteRegister extends SpecialPage{

    function __construct() {
        SpecialPage::__construct("CAPSCompleteRegister", null, false, 'runCAPSCompleteRegister');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isLoggedIn() && $person->isCandidate());
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        if(!isset($_POST['submit'])){
            CAPSCompleteRegister::generateFormHTML($wgOut);
        }
        else{
            CAPSCompleteRegister::handleSubmit($wgOut);
            return;
        }
    }
    
    function createForm(){
        global $wgLang, $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        $request = UserCreateRequest::newFromName($me->getName());
        $certification = $request->getCertification();
        $file_name = @$certification['file_data']['name'];
        $file_name = ($file_name != "") ? "<a style='float:left;' href='$wgServer$wgScriptPath/index.php?action=getCertification&id={$request->getId()}'>
                                               <span class='en'>[Download]</span>
                                               <span class='fr'>[Télécharger]</span>
                                           </a>" : "";
        $extra = $request->getExtras();
        $references = @$extra['references'];
        $values = array();
        if(is_array($references)){
            foreach($references as $key => $reference){
                $values[] = array_values($reference);
            }
        }
        
        if($wgLang->getCode() == "en"){
            $formContainer = new FormContainer("form_container");
            $formTable = new FormTable("form_table");

            $fileLabel = new Label("file_label", "Proof of Certification:</div>
                                                  <div style='text-align:right; font-size:0.7em'>$file_name
                                                  <a href='#!' onclick='openDialog()'>[what is this?]</a>", "The prior file of medical or surgical abortion services of the user", VALIDATE_NOTHING, false);
            $fileField = new FileField("file_field", "Proof of Certification", "", VALIDATE_NOTHING);
            $fileRow = new FormTableRow("file_row");
            $fileRow->attr('style','line-height: 10px;');
            $fileRow->append($fileLabel)->append($fileField);
            
            $plusMinus = new PlusMinus('reference_array', $values);

            $referenceLabel = new Label("reference_label", "Sponsors", "The physician or pharmacist who referred the user", VALIDATE_NOTHING);
            $referenceNameField = new TextField("reference_name_field[]", "Name of Sponsor", "", VALIDATE_NOTHING);
            $referenceAffilField = new TextField("reference_affil_field[]", "Affiliation of Sponsor", "", VALIDATE_NOTHING);
            $referenceEmailField = new TextField("reference_email_field[]", "Email of Sponsor", "", VALIDATE_NOTHING);
            $referencePhoneField = new TextField("reference_phone_field[]", "Phone Number of Sponsor", "", VALIDATE_NOTHING);
            
            $referenceNameField->attr('placeholder', 'Name')->attr('size', 16);
            $referenceAffilField->attr('placeholder', 'Affiliation')->attr('size', 20);
            $referenceEmailField->attr('placeholder', 'Email Address')->attr('size', 20);
            $referencePhoneField->attr('placeholder', 'Phone Number')->attr('size', 16);
            
            $plusMinus->append($referenceNameField);
            $plusMinus->append($referenceAffilField);
            $plusMinus->append($referenceEmailField);
            $plusMinus->append($referencePhoneField);
            
            $referenceRow = new FormTableRow("reference_row");
            $referenceRow->append($referenceLabel)->append($plusMinus);

            $submitCell = new EmptyElement();
            $submitField = new SubmitButton("submit", "Submit Request", "Submit Request", VALIDATE_NOTHING);
            $submitRow = new FormTableRow("submit_row");
            $submitRow->append($submitCell)->append($submitField);
            $submitRow->attr("align","right");
        }
        else if($wgLang->getCode() == "fr"){
            $formContainer = new FormContainer("form_container");
            $formTable = new FormTable("form_table");
            
            $fileLabel = new Label("file_label", "Preuve de la certification:<sup><span style='color:red;'>*</span></sup></div>
                                                  {$file_name}<div style='text-align:right; font-size:0.7em'>
                                                  <a href='#!' onclick='openDialog()'>[Qu'est-ce que c'est?]</a>", "Le fichier avant des services d'avortement médical ou chirurgical de l'utilisateur", VALIDATE_NOTHING, false);
            $fileField = new FileField("file_field", "Preuve de certification", "", VALIDATE_NOTHING);
            $fileRow = new FormTableRow("file_row");
            $fileRow->attr('style','line-height: 10px;');
            $fileRow->append($fileLabel)->append($fileField);

            $plusMinus = new PlusMinus('reference_array', $values);

            $referenceLabel = new Label("reference_label", "Sponsors", "Le médecin ou le pharmacien qui a renvoyé l'utilisateur", VALIDATE_NOTHING);
            $referenceNameField = new TextField("reference_name_field[]", "Nom du commanditaire", "", VALIDATE_NOTHING);
            $referenceAffilField = new TextField("reference_affil_field[]", "Affiliation du commanditaire", "", VALIDATE_NOTHING);
            $referenceEmailField = new TextField("reference_email_field[]", "Email du commanditaire", "", VALIDATE_NOTHING);
            $referencePhoneField = new TextField("reference_phone_field[]", "Numéro de téléphone du commanditaire", "", VALIDATE_NOTHING);
            
            $referenceNameField->attr('placeholder', 'Nom')->attr('size', 16);
            $referenceAffilField->attr('placeholder', 'Affiliation')->attr('size', 20);
            $referenceEmailField->attr('placeholder', 'Adresse Email')->attr('size', 20);
            $referencePhoneField->attr('placeholder', 'Numéro de Téléphone')->attr('size', 16);
            
            $plusMinus->append($referenceNameField);
            $plusMinus->append($referenceAffilField);
            $plusMinus->append($referenceEmailField);
            $plusMinus->append($referencePhoneField);
            
            $referenceRow = new FormTableRow("reference_row");
            $referenceRow->append($referenceLabel)->append($plusMinus);


            $submitCell = new EmptyElement();
            $submitField = new SubmitButton("submit", "Envoyer la demande", "Envoyer la demande", VALIDATE_NOTHING);
            $submitRow = new FormTableRow("submit_row");
            $submitRow->append($submitCell)->append($submitField);
            $submitRow->attr("align","right");
        }
        $formTable->append($fileRow)
                  ->append($referenceRow)
                  ->append($submitRow);
        
        $formContainer->append($formTable);
        return $formContainer;
    }
    
     function generateFormHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config, $wgLang;
        $user = Person::newFromId($wgUser->getId());
        if($wgLang->getCode() == "en"){
            $wgOut->addHTML("<p>This section is for certified mifepristone abortion care providers (physicians and pharmacists).<br />
                                To enter, please provide information on a sponsor who can confirm your eligibility. We will review and respond within 7 days. If you have any questions please contact the site administrators <a href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a>.</p>");
            $wgOut->addHTML("<div id='fileUploadInfo' title='Proof of Certification' style='display:none'>Please upload a copy of your proof of certification from the Mifepristone training program.</div>");
        }
        else if($wgLang->getCode() == "fr"){
            $wgOut->addHTML("<p>Cette section est pour les fournisseurs certifiés de soins mifépristone avortement (médecins et pharmaciens). <br />
                                Pour entrer, s'il vous plaît fournir des informations sur un sponsor qui peut confirmer votre admissibilité. Nous allons examiner et de répondre dans les 7 jours. Si vous avez des questions s'il vous plaît contacter le site aux administrateurs <a href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a></p>");
            $wgOut->addHTML("<div id='fileUploadInfo' title='Proof of Certification' style='display:none'>S'il vous plaît télécharger une copie de votre preuve de certification du programme de formation mifépristone .</div>");
        }
        $wgOut->addHTML("<form action='$wgScriptPath/index.php/Special:CAPSCompleteRegister' method='post' enctype='multipart/form-data'>\n");
        $form = self::createForm();
        $wgOut->addHTML($form->render());
        $wgOut->addScript("<script type='text/javascript'>
                    
                    function openDialog(){
                        $('#fileUploadInfo').dialog({width:'200px',position:{my: 'center', at:'center', of: window},modal:true,resizable:false,     buttons: {
                            'OK': function () {
                                $(this).dialog('close')
                            }
                        }});
                    }

            </script>");
        $wgOut->addHTML("</form>");
    }
    
    function handleSubmit($wgOut){
        global $wgServer, $wgScriptPath, $wgMessage, $wgGroupPermissions;
        $me = Person::newFromWgUser();
        $max_file_size = 20;
        $form = self::createForm();
        $status = $form->validate();
        if($status){
            $request = UserCreateRequest::newFromName($me->getName());
            $extras = $request->getExtras();

            $extras['references'] = array();
            if(@count($_POST['reference_name_field']) > 0){
                foreach(@$_POST['reference_name_field'] as $key => $reference){
                    if($reference != ""){
                        $extras['references'][] = array('name' => $_POST['reference_name_field'][$key],
                                                        'affil' => $_POST['reference_affil_field'][$key],
                                                        'email' => $_POST['reference_email_field'][$key],
                                                        'phone' => $_POST['reference_phone_field'][$key]);
                    }
                }
            }
            
            DBFunctions::update('grand_user_request',
                                array('extras' => serialize($extras)),
                                array('id' => $request->getId()));
            if(isset($_FILES['file_field']) && $_FILES['file_field']['tmp_name'] != "" && $_FILES['file_field']['size'] < $max_file_size*1024*1024){
                $contents = base64_encode(file_get_contents($_FILES['file_field']['tmp_name']));
                $filename = $_FILES['file_field']['name'];
                $filesize = $_FILES['file_field']['size'];
                $filetype = $_FILES['file_field']['type'];
                $wpFile['file_data'] = array('name' => $filename,
                                             'size' => $filesize,
                                             'type' => $filetype,
                                             'file' => $contents);
                
                DBFunctions::update('grand_user_request',
                                    array('proof_certification' => serialize($wpFile)),
                                    array('id' => $request->getId()));
            }
        }
        CAPSCompleteRegister::generateFormHTML($wgOut);
    }

}

?>
