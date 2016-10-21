<?php
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['CAPSCompleteRegister'] = 'CAPSCompleteRegister'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['CAPSCompleteRegister'] = $dir . 'CAPSCompleteRegister.i18n.php';
$wgSpecialPageGroups['CAPSCompleteRegister'] = 'network-tools';

function runCAPSCompleteRegister($par) {
    CAPSCompleteRegister::execute($par);
}

class CAPSCompleteRegister extends SpecialPage{

    function CAPSCompleteRegister() {
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
        $file_name = ($file_name != "") ? "<a href='$wgServer$wgScriptPath/index.php?action=getCertification&id={$request->getId()}'>Proof of Certification</a>" : "Proof of Certification";
        $extra = $request->getExtras();
        $reference = @$extra['reference'];
        if($wgLang->getCode() == "en"){
            $formContainer = new FormContainer("form_container");
            $formTable = new FormTable("form_table");

            $fileLabel = new Label("file_label", "{$file_name}:</div>
                                                  <div style='text-align:right; font-size:0.7em'>
                                                  <a href='#!' onclick='openDialog()'>[what is this?]</a>", "The prior file of medical or surgical abortion services of the user", VALIDATE_NOTHING, false);
            $fileField = new FileField("file_field", "Proof of Certification", "", VALIDATE_NOTHING);
            $fileRow = new FormTableRow("file_row");
            $fileRow->attr('style','line-height: 10px;');
            $fileRow->append($fileLabel)->append($fileField);

            $referenceLabel = new Label("reference_label", "Name of Reference", "The physician or pharmacist who referred the user", VALIDATE_NOTHING);
            $referenceField = new TextField("reference_field", "Name of Reference", "", VALIDATE_NOTHING);
            if($reference != ""){
                $referenceField->attr('value', $reference);
            }
            
            $referenceRow = new FormTableRow("reference_row");
            $referenceRow->append($referenceLabel)->append($referenceField);

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
                                                  <div style='text-align:right; font-size:0.7em'>
                                                  <a href='#!' onclick='openDialog()'>[Qu'est-ce que c'est?]</a>", "Le fichier avant des services d'avortement médical ou chirurgical de l'utilisateur", VALIDATE_NOTHING, false);
            $fileField = new FileField("file_field", "Preuve de certification", "", VALIDATE_NOTHING);
            $fileRow = new FormTableRow("file_row");
            $fileRow->attr('style','line-height: 10px;');
            $fileRow->append($fileLabel)->append($fileField);

            $referenceLabel = new Label("reference_label", "Nom de référence", "Le médecin ou le pharmacien qui a renvoyé l' utilisateur", VALIDATE_NOTHING);
            $referenceField = new TextField("reference_field", "Name of Reference", "", VALIDATE_NOTHING);
            if($reference != ""){
                $referenceField->attr('value', $reference);
            }
            $referenceRow = new FormTableRow("reference_row");
            $referenceRow->append($referenceLabel)->append($referenceField);

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
            $wgOut->addHTML("<div id='fileUploadInfo' title='Proof of Certification' style='display:none'>Please upload a copy of your proof of certification from the Mifepristone training program.</div>");
            $wgOut->addHTML("<p>In order to complete your registration, a proof of certification document and a name of reference need to be provided.  Once you have filled out this information, then an administrator will review the document and then make you a full user.</p>");
        }
        else if($wgLang->getCode() == "fr"){
            $wgOut->addHTML("<div id='fileUploadInfo' title='Proof of Certification' style='display:none'>S'il vous plaît télécharger une copie de votre preuve de certification du programme de formation mifépristone .</div>");
            $wgOut->addHTML("<p>Afin de compléter votre inscription, une preuve du document de certification et un nom de référence doivent être fournis. Une fois que vous avez rempli ces informations, puis un administrateur examinera le document, puis vous faire un utilisateur complet.</p>");
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
            $form->getElementById('reference_field')->setPOST('reference');
            $extras['reference'] = $_POST['reference'];
            
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
