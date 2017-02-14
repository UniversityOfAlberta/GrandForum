<?php
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['PdfConversion'] = 'PdfConversion'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['PdfConversion'] = $dir . 'PdfConversion.i18n.php';
$wgSpecialPageGroups['PdfConversion'] = 'network-tools';

function runPdfConversion($par) {
    PdfConversion::execute($par);
}

class PdfConversion extends SpecialPage{

    function PdfConversion() {
        SpecialPage::__construct("PdfConversion", null, false, 'runPdfConversion');
    }

    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isLoggedIn());
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        if(!isset($_POST['submit'])){
            PdfConversion::generateFormHTML($wgOut);
        }
        else{
            PdfConversion::handleSubmit($wgOut);
            return;
        }
    }

    function createForm(){
        global $wgLang, $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        $formContainer = new FormContainer("form_container");
	$formTable = new FormTable("form_table");
	$fileLabel = new Label("file_label", "PDF File:</div>
                                                  <div style='text-align:right; font-size:0.7em'>file
                                                  <a href='#!' onclick='openDialog()'>[what is this?]</a>", "PDF file with all student information", VALIDATE_NOTHING, false);
        $fileField = new FileField("file_field", "Proof of Certification", "", VALIDATE_NOTHING);
        $fileRow = new FormTableRow("file_row");
        $fileRow->attr('style','line-height: 10px;');
        $fileRow->append($fileLabel)->append($fileField);
	$submitCell = new EmptyElement();
        $submitField = new SubmitButton("submit", "Submit Request", "Submit Request", VALIDATE_NOTHING);
        $submitRow = new FormTableRow("submit_row");
        $submitRow->append($submitCell)->append($submitField);
        $submitRow->attr("align","right");
        $formTable->append($fileRow)
                  ->append($submitRow);

        $formContainer->append($formTable);
        return $formContainer;
    }

     function generateFormHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config, $wgLang;
        $user = Person::newFromId($wgUser->getId());
	$userId = $user->getId();
        $wgOut->addHTML("<form action='$wgScriptPath/index.php?action=api.convertPdf' method='post' enctype='multipart/form-data'>\n");
        $form = self::createForm();
        $wgOut->addHTML($form->render());
        $wgOut->addScript("<script type='text/javascript'>

            </script>");
        $wgOut->addHTML("</form>");
    }

    function handleSubmit($wgOut){
        global $wgServer, $wgScriptPath, $wgMessage, $wgGroupPermissions;
	$max_file_size = 20;
	$form = self::createForm();
	$status = $form->validate();
	if($status){
            $result = APIRequest::doAction('ConvertPdf', false);
	}
        PdfConversion::generateFormHTML($wgOut);
    }

}

?>
