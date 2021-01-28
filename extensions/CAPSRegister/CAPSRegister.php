<?php
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['CAPSRegister'] = 'CAPSRegister'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['CAPSRegister'] = $dir . 'CAPSRegister.i18n.php';
$wgSpecialPageGroups['CAPSRegister'] = 'network-tools';

require_once("CAPSCompleteRegister.php");

function runCAPSRegister($par) {
    CAPSRegister::execute($par);
}

class CAPSRegister extends SpecialPage{

    function __construct() {
        SpecialPage::__construct("CAPSRegister", null, false, 'runCAPSRegister');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return !$person->isLoggedIn();
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        if(!isset($_POST['submit'])){
            CAPSRegister::generateFormHTML($wgOut);
        }
        else{
            CAPSRegister::handleSubmit($wgOut);
            return;
        }
    }
    
    function createForm(){
        global $wgLang;
        $englishTerms = "Consent Form for CAPS Website<br /><br />
            <span style=‘text-align: center;’><b>How we protect and use your data</b>
<br /><br />
This website is part of the Mifepristone Implementation Research in Canada study (The CART-Mife Study) which aims to identify and address the facilitators and barriers for successful initiation and ongoing provision of medical abortion services using mifepristone.
<br /><br />
We will collect data including: your demographic information; posts on challenges, barriers and facilitators; responses to a weekly 1-question poll; and participant usage statistics. We will also collect your contact information to inform you of future opportunities to participate in research. You will be assigned a unique study number and your personal information will be kept confidential. Your name and identifying information will not be included anywhere in the analysis or report for this study. Member IP addresses and identifiers will not be collected for research purposes. Computer files will be password-protected on a password-protected computer and stored securely on a UBC server. 
<br /><br />
We do not believe that participating in this study represents any risk to you. Participants will contribute to knowledge of the barriers and challenges faced when providing medical abortion care in Canada. The main results of our study will be presented to health system and policy leaders to improve policies and systems that support your provision of safe, accessible medical abortion care. We will also report aggregate findings (with no personal identifiers) in academic journals and conferences.  
<br /><br />
Your participation is optional and you may choose to withdraw from the study at any time. For more information, contact the Research Participant Complaint Line in the University of British Columbia Office of Research Ethics (e) <a href='mailto:RSIL@ors.ubc.ca'>RSIL@ors.ubc.ca</a> (t) 604-822-8598 or 1-877-822-8598. Principal Investigator: Dr. Wendy Norman, Associate Professor, Dept. of Family Practice, Faculty of Medicine, UBC (e) <a href='mailto:wendy.norman@ubc.ca'>wendy.norman@ubc.ca</a> (t) 604-875-2424 x4880</span>
<center><hr style='width:80%;' /></center>
<img src='../skins/UBC_logo.png' width='50' />
<img class='en' src='../skins/obgyn_transparent.png' width='120' />
<img class='fr' src='../skins/french_obgyn.png' width='120' />
<img src='../skins/CFPC.png' width='120' />
<img src='../skins/cpa_transparent.png' width='120' />
<img src='../skins/cart_transparent.png' width='120' />
<img src='../skins/msfhr_transparent.png' width='120' />
<img src='../skins/bc_women_logo.png' width='120' />
<img src='../skins/cihr_logo_transparent.png' width='120' />
            ";
        $frenchTerms = "Formulaire de consentement pour CAPS site <br /> <br />
            <span style = 'text-align: center;'> <b> Comment nous protégeons et utilisons vos données </b>
<br /> <br />
Ce site fait partie de la mise en œuvre de la recherche mifépristone dans l'étude Canada (L'étude CART-Mife) qui vise à identifier et traiter les facilitateurs et les obstacles à l'initiation réussie et la prestation continue des services d'avortement médicamenteux utilisant la mifépristone.
<br /> <br />
Nous allons recueillir des données, y compris: vos renseignements démographiques; messages sur les défis, les obstacles et les facilitateurs; les réponses à un hebdomadaire 1 question sondage; et les statistiques d'utilisation des participants. Nous allons également recueillir vos coordonnées pour vous informer de futures occasions de participer à la recherche. Vous recevrez un numéro d'étude unique et vos informations personnelles seront gardées confidentielles. Votre nom et les informations d'identification ne sera pas incluse partout dans l'analyse ou un rapport pour cette étude. Adresses et identifiants IP membres ne seront pas collectées à des fins de recherche. Les fichiers informatiques seront sur un ordinateur protégé par mot de passe et stockés en toute sécurité sur un serveur UBC protégé par mot de passe.
<br /> <br />
Nous ne croyons pas que la participation à cette étude représente un risque pour vous. Les participants contribueront à la connaissance des obstacles et des défis rencontrés lors de la prestation de soins de l'avortement médicamenteux au Canada. Les principaux résultats de notre étude seront présentés au système et aux politiques de la santé des dirigeants pour améliorer les politiques et les systèmes qui prennent en charge votre prestation de sécurité, l'accessibilité des services d'avortement médical. Nous allons également rendre compte des résultats globaux (sans identificateurs personnels) dans des revues spécialisées et des conférences.
<br /> <br />
Votre participation est facultative et vous pouvez choisir de se retirer de l'étude à tout moment. Pour plus d'informations, contacter la recherche Participant Plainte ligne à l'Université de la Colombie-Britannique Bureau de l'éthique de la recherche (e) <a href='mailto:RSIL@ors.ubc.ca'> RSIL@ors.ubc.ca </a> (t) 604-822-8598 ou 1-877-822-8598. Chercheur principal: Dr Wendy Norman, professeur agrégé, Département de médecine familiale, Faculté de médecine, UBC (e) <a href='mailto:wendy.norman@ubc.ca'> wendy.norman@ubc.ca </a> (t) 604-875-2424 x4880</span>
<center><hr style='width:80%;' /></center>
<img src='../skins/UBC_logo.png' width='50' />
<img class='en' src='../skins/obgyn_transparent.png' width='120' />
<img class='fr' src='../skins/french_obgyn_transparent.png' width='120' />
<img src='../skins/CFPC.png' width='120' />
<img src='../skins/cpa_transparent.png' width='120' />
<img src='../skins/cart_transparent.png' width='120' />
<img src='../skins/msfhr_transparent.png' width='120' />
<img src='../skins/bc_women_logo.png' width='120' />
<img src='../skins/cihr_logo_transparent.png' width='120' />";
        if($wgLang->getCode() == "en"){
            $formContainer = new FormContainer("form_container");
            $formTable = new FormTable("form_table");
            
            $firstNameLabel = new Label("first_name_label", "First Name", "The first name of the user (cannot contain spaces)", VALIDATE_NOT_NULL);
            $firstNameField = new TextField("first_name_field", "First Name", "", VALIDATE_NOT_NULL + VALIDATE_NOSPACES);
            $firstNameRow = new FormTableRow("first_name_row");
            $firstNameRow->append($firstNameLabel)->append($firstNameField->attr('size', 20));
            
            $lastNameLabel = new Label("last_name_label", "Last Name", "The last name of the user (cannot contain spaces)", VALIDATE_NOT_NULL);
            $lastNameField = new TextField("last_name_field", "Last Name", "", VALIDATE_NOT_NULL + VALIDATE_NOSPACES);
            $lastNameRow = new FormTableRow("last_name_row");
            $lastNameRow->append($lastNameLabel)->append($lastNameField->attr('size', 20));
            $lastNameField->registerValidation(new UniqueUserValidation(VALIDATION_POSITIVE, VALIDATION_ERROR));
            
            $emailLabel = new Label("email_label", "Email", "The email address of the user", VALIDATE_NOT_NULL);
            $emailField = new EmailField("email_field", "Email", "", VALIDATE_NOT_NULL);
            $emailRow = new FormTableRow("email_row");
            $emailRow->append($emailLabel)->append($emailField);

            $roleLabel = new Label("role_label", "Role", "The role of the user", VALIDATE_NOT_NULL);
            $roleField = new SelectBox("role_field", "Role", "", array("Physician", "Pharmacist", "Nurse Practitioner", "Midwife", "Facility Staff", "Other"), VALIDATE_NOT_NULL);
            $roleRow = new FormTableRow("role_row");
            $roleRow->append($roleLabel)->append($roleField);

            $otherRoleLabel = new Label("other_role_label", "Specify Role", "The role of the user", VALIDATE_NOTHING);
            $otherRoleField = new SelectBox("other_role_field", "other_Role", "", array("Administrator", "Counsellor", "Nurse", "Clerical"), VALIDATE_NOTHING);
            $otherRoleRow = new FormTableRow("other_role_row");
            $otherRoleRow->attr("style","display:none");
            $otherRoleRow->append($otherRoleLabel)->append($otherRoleField);

            $languageLabel = new Label("language_label", "Language", "The language of the user", VALIDATE_NOT_NULL);
            $languageField = new SelectBox("language_field", "Language", "", array("English", "French"), VALIDATE_NOT_NULL);
            $languageRow = new FormTableRow("language_row");
            $languageRow->append($languageLabel)->append($languageField);
           
            $postalcodeLabel = new Label("postalcode_label", "Postal Code", "The postalcode of the user", VALIDATE_NOT_NULL);
            $postalcodeField = new TextField("postalcode_field", "Postal Code", "", VALIDATE_NOT_NULL);
            $postalcodeRow = new FormTableRow("postalcode_row");
            $postalcodeRow->append($postalcodeLabel)->append($postalcodeField->attr('size', 20));

            $cityLabel = new Label("city_label", "City", "The city of the user", VALIDATE_NOT_NULL);
            $cityField = new TextField("city_field", "City", "", VALIDATE_NOT_NULL);
            $cityRow = new FormTableRow("city_row");
            $cityRow->append($cityLabel)->append($cityField->attr('size', 20));

            $provinceLabel = new Label("province_label", "Province", "The province of the user", VALIDATE_NOT_NULL);
            $provinceField = new TextField("province_field", "Province", "", VALIDATE_NOT_NULL);
            $provinceRow = new FormTableRow("province_row");
            $provinceRow->append($provinceLabel)->append($provinceField->attr('size', 20));
            
            $clinicLabel = new Label("clinic_label", "Clinic/Hospital Name", "The clinic of the user", VALIDATE_NOTHING);
            $clinicField = new TextField("clinic_field", "Clinic/Hospital Name", "", VALIDATE_NOTHING);
            $clinicRow = new FormTableRow("clinic_row");
            $clinicRow->attr('style','display:none');
            $clinicRow->append($clinicLabel)->append($clinicField->attr('size', 20));

            $specialtyLabel = new Label("specialty_label", "Specialty", "The specialty of the user", VALIDATE_NOTHING);
            $specialtyField = new SelectBox("specialty_field", "Specialty", "",array("Family Physician/General Practitioner",
                                                                                     "Obstetrician/Gynecologist",
                                                                                     "Pediatrician",
                                                                                     "Other (Specify)"), VALIDATE_NOTHING);
            $specialtyRow = new FormTableRow("specialty_row");
            $specialtyRow->attr('style','display:none');
            $specialtyRow->append($specialtyLabel)->append($specialtyField);

            $otherSpecialtyLabel = new Label("other_specialty_label", "Specify Specialty", "The specialty of the user", VALIDATE_NOTHING);
            $otherSpecialtyField = new TextField("other_specialty_field", "other_Specialty", "", VALIDATE_NOTHING);
            $otherSpecialtyRow = new FormTableRow("other_specialty_row");
            $otherSpecialtyRow->attr("style","display:none");
            $otherSpecialtyRow->append($otherSpecialtyLabel)->append($otherSpecialtyField);
     
            $yearsLabel = new Label("years_label", "Years in Practice", "The years of practice of the user", VALIDATE_NOTHING);
            $yearsField = new TextField("years_field", "Years of Practice", "", VALIDATE_NOTHING);
            $yearsRow = new FormTableRow("years_row");
            $yearsRow->attr('style','display:none');
            $yearsRow->append($yearsLabel)->append($yearsField->attr('size',5));

            $provisionLabel = new Label("provision_label", "Prior Provision of<br />Abortion Services", "The prior provision of medical or surgical abortion services of the user", VALIDATE_NOTHING);
            $provisionLabel->attr('style', 'line-height: 1.1em; padding-top:5px;');
            $provisionField = new VerticalRadioBox("provision_field", "Prior Provision of Abortion Services", array("provision_fieldyes","provision_fieldno"), array("Yes","No"), VALIDATE_NOTHING);
            $provisionRow = new FormTableRow("provision_row");
            $provisionRow->attr('style','display:none');
            $provisionRow->append($provisionLabel)->append($provisionField);

            $disclosureLabelRow = new FormTableRow("disclosureLabel");
            $disclosureLabelRow->attr("style","display:none");
            $disclosureRow = new FormTableRow("disclosure");
            $disclosureRow->attr("style","display:none;");
            $emptyElement = new EmptyElement();
            $disclosureLabel = new CustomElement("disclosure", "disclosure", "disclosure", "<td colspan=2><div id='disclosure_div'style='background: #f0f0f0;'>
                                                                                                This community provides Mifepristone trained physicians with a way<br />
                                                                                                to locate the nearest trained pharmacist.<br /> 
                                                                                                Do you agree to disclose the name and location of your pharmacy for this map?</td>");
            $disclosureField = new HorizontalRadioBox("disclosure_field", "disclosure_field",array("disclosure_fieldyes", "provision_fieldno"), array("I agree", "I disagree"));
            $disclosureLabelRow->append($disclosureLabel);
            $disclosureRow->append($emptyElement)->append($disclosureField);

            $pharmacyNameLabel = new Label("pharmacy_name_label", "Pharmacy Name", "Pharmacy Name", VALIDATE_NOTHING);
            $pharmacyField = new TextField("pharmacy_name_field", "Pharmacy Name", "", VALIDATE_NOTHING);
            $pharmacyRow = new FormTableRow("pharmacy_name_row");
            $pharmacyRow->append($pharmacyNameLabel)->append($pharmacyField);
            $pharmacyRow->attr("style","display:none");

            $pharmacyAddressLabel = new Label("pharmacy_address_label", "Pharmacy Address", "Pharmacy Address", VALIDATE_NOTHING);
            $pharmacyAddressField = new TextField("pharmacy_address_field", "Pharmacy Address", "", VALIDATE_NOTHING);
            $pharmacyAddressRow = new FormTableRow("pharmacy_address_row");
            $pharmacyAddressRow->attr("style","display:none");
            $pharmacyAddressRow->append($pharmacyAddressLabel)->append($pharmacyAddressField);

           /* $fileLabel = new Label("file_label", "Proof of Certification:</div>
                              <div style='text-align:right; font-size:0.7em'>
                              <a href='#!' onclick='$(\"#fileUploadInfo\").dialog({width:\"221px\",position:{my: \"center\", at:\"center\", of: window}})'>[what is this?]</a>", "The prior file of medical or surgical abortion services of the user", VALIDATE_NOTHING, false);*/
            $fileLabel = new Label("file_label", "Proof of Certification:</div>
                                                  <div style='text-align:right; font-size:0.7em'>
                                                  <a href='#!' onclick='openDialog()'>[what is this?]</a>", "The prior file of medical or surgical abortion services of the user", VALIDATE_NOTHING, false);
            $fileField = new FileField("file_field", "Proof of Certification", "", VALIDATE_NOTHING);
            $fileRow = new FormTableRow("file_row");
            $fileRow->attr('style','line-height: 10px;');
            $fileRow->append($fileLabel)->append($fileField);

            $referenceLabel = new Label("reference_label", "Name of Reference", "The physician or pharmacist who referred the user", VALIDATE_NOTHING);
            $referenceField = new TextField("reference_field", "Name of Reference", "", VALIDATE_NOTHING);
            $referenceRow = new FormTableRow("reference_row");
            $referenceRow->append($referenceLabel)->append($referenceField);

            $captchaLabel = new Label("captcha_label", "Enter Code", "Enter the code you see in the image", VALIDATE_NOT_NULL);
            $captchaField = new Captcha("captcha_field", "Captcha", "", VALIDATE_NOT_NULL);
            $captchaRow = new FormTableRow("captcha_row");
            $captchaRow->append($captchaLabel)->append($captchaField);
            $termsLabel = new Label("terms_label", "<div style='display:inline-block;vertical-align:top;'>Terms and Conditions<br /><small>*must scroll through consent form</small></div>", "Terms and conditions", VALIDATE_NOTHING);
            $termsField = new CustomElement("terms", "terms", "terms", 
                                            
                                               "<div class='TermsOuterDiv'>
                                                $englishTerms</div>
                                             
                                                                                            
                                             ");
            $termsAgree = new VerticalCheckBox("terms_agree", "terms_agree", array(), array("I have read and agree to the terms in the consent form"), VALIDATE_NOTHING);
            $termsExtra = new VerticalCheckBox("terms_extra", "terms_extra", 
                                               array("collect_demo", "collect_comments"), 
                                               array("I agree to have my demographic information used for research purposes." => "collect_demo",
                                                     "I agree to have my posts on challenges, barriers and facilitators used for research purposes." => "collect_comments"),
                                               VALIDATE_NOTHING);
            $termsRow = new FormTableRow("terms_row");
            $termsAgree->attr("disabled","disabled");
            $termsRow->append($termsLabel)->append($termsField);
            $agreeRow = new FormTableRow("agree_row");
            $agreeExtraRow = new FormTableRow("agree_extra_row");
            $agreeCell = new EmptyElement();
            $agreeExtraCell = new EmptyElement();
            $agreeRow->append($agreeCell)->append($termsAgree);
            $agreeExtraRow->append($agreeExtraCell)->append($termsExtra);
            $agreeRow->attr("class","terms");
            $submitCell = new EmptyElement();
            $submitField = new SubmitButton("submit", "Submit Request", "Submit Request", VALIDATE_NOTHING);
            $submitField->attr("disabled","disabled");
            $submitRow = new FormTableRow("submit_row");
            $submitRow->append($submitCell)->append($submitField);
            $submitRow->attr("align","right");
        }
        else if($wgLang->getCode() == "fr"){
            $formContainer = new FormContainer("form_container");
            $formTable = new FormTable("form_table");
            
            $firstNameLabel = new Label("first_name_label", "Prénom", "Le premier nom de l'utilisateur (ne peut pas contenir des espaces)", VALIDATE_NOT_NULL);
            $firstNameField = new TextField("first_name_field", "First Name", "", VALIDATE_NOSPACES);
            $firstNameRow = new FormTableRow("first_name_row");
            $firstNameRow->append($firstNameLabel)->append($firstNameField->attr('size', 20));
            
            $lastNameLabel = new Label("last_name_label", "Nom de famille", "Le nom de l'utilisateur (ne peut pas contenir des espaces)", VALIDATE_NOT_NULL);
            $lastNameField = new TextField("last_name_field", "Last Name", "", VALIDATE_NOSPACES);
            $lastNameRow = new FormTableRow("last_name_row");
            $lastNameRow->append($lastNameLabel)->append($lastNameField->attr('size', 20));
            $lastNameField->registerValidation(new UniqueUserValidation(VALIDATION_POSITIVE, VALIDATION_ERROR));
            
            $emailLabel = new Label("email_label", "Email", "L'adresse email de l'utilisateur", VALIDATE_NOT_NULL);
            $emailField = new EmailField("email_field", "Email", "", VALIDATE_NOT_NULL);
            $emailRow = new FormTableRow("email_row");
            $emailRow->append($emailLabel)->append($emailField);

            $roleLabel = new Label("role_label", "Rôle", "Le rôle de l'utilisateur", VALIDATE_NOT_NULL);
            $roleField = new SelectBox("role_field", "Role", "", array("Médecin", "Pharmacien", "Infirmière Praticienne", "Personnel de l'installation", "Autre"), VALIDATE_NOT_NULL);
            $roleRow = new FormTableRow("role_row");
            $roleRow->append($roleLabel)->append($roleField);

            $otherRoleLabel = new Label("other_role_label", "Spécifiez Rôle", "Le rôle de l'utilisateur", VALIDATE_NOTHING);
            $otherRoleField = new SelectBox("other_role_field", "other_Role", "", array("Administrateur", "Conseiller", "Infirmière", "Sage-Femme", "Commis de bureau"), VALIDATE_NOTHING);
            $otherRoleRow = new FormTableRow("other_role_row");
            $otherRoleRow->attr("style","display:none");
            $otherRoleRow->append($otherRoleLabel)->append($otherRoleField);

            $languageLabel = new Label("language_label", "La langue", "La langue de l'utilisateur", VALIDATE_NOT_NULL);
            $languageField = new SelectBox("language_field", "Language", "", array("English", "Français"), VALIDATE_NOT_NULL);
            $languageRow = new FormTableRow("language_row");
            $languageRow->append($languageLabel)->append($languageField);
           
            $postalcodeLabel = new Label("postalcode_label", "Code Postal", "Le code postal de l'utilisateur", VALIDATE_NOT_NULL);
            $postalcodeField = new TextField("postalcode_field", "Postal Code", "", VALIDATE_NOT_NULL);
            $postalcodeRow = new FormTableRow("postalcode_row");
            $postalcodeRow->append($postalcodeLabel)->append($postalcodeField->attr('size', 20));

            $cityLabel = new Label("city_label", "Ville", "Le ville de l'utilisateur", VALIDATE_NOT_NULL);
            $cityField = new TextField("city_field", "City", "", VALIDATE_NOT_NULL);
            $cityRow = new FormTableRow("city_row");
            $cityRow->append($cityLabel)->append($cityField->attr('size', 20));

            $provinceLabel = new Label("province_label", "Province", "La province de l'utilisateur", VALIDATE_NOT_NULL);
            $provinceField = new TextField("province_field", "Province", "", VALIDATE_NOT_NULL);
            $provinceRow = new FormTableRow("province_row");
            $provinceRow->append($provinceLabel)->append($provinceField->attr('size', 20));
            
            $clinicLabel = new Label("clinic_label", "Clinique / Nom de l'hôpital", "La clinique de l'utilisateur", VALIDATE_NOTHING);
            $clinicField = new TextField("clinic_field", "Clinic/Hospital Name", "", VALIDATE_NOTHING);
            $clinicRow = new FormTableRow("clinic_row");
            $clinicRow->attr('style','display:none');
            $clinicRow->append($clinicLabel)->append($clinicField->attr('size', 20));

            $specialtyLabel = new Label("specialty_label", "Spécialité", "La spécialité de l'utilisateur", VALIDATE_NOTHING);
            $specialtyField = new SelectBox("specialty_field", "Specialty", "",array("Médecin de famille/Médecin généraliste",
                                                                                     "Gynécologue/Obstétricien",
                                                                                     "Pédiatre",
                                                                                     "Autre (précisez)"), VALIDATE_NOTHING);
            $specialtyRow = new FormTableRow("specialty_row");
            $specialtyRow->attr('style','display:none');
            $specialtyRow->append($specialtyLabel)->append($specialtyField);

            $otherSpecialtyLabel = new Label("other_specialty_label", "Spécifiez spéciaux", "Le spéciaux de l'utilisateur", VALIDATE_NOTHING);
            $otherSpecialtyField = new TextField("other_specialty_field", "other_Specialty", "", VALIDATE_NOTHING);
            $otherSpecialtyRow = new FormTableRow("other_specialty_row");
            $otherSpecialtyRow->attr("style","display:none");
            $otherSpecialtyRow->append($otherSpecialtyLabel)->append($otherSpecialtyField);
     
            $yearsLabel = new Label("years_label", "Années de pratique", "Le Années de pratique de l'utilisateur", VALIDATE_NOTHING);
            $yearsField = new TextField("years_field", "Years of Practice", "", VALIDATE_NOTHING);
            $yearsRow = new FormTableRow("years_row");
            $yearsRow->attr('style','display:none');
            $yearsRow->append($yearsLabel)->append($yearsField->attr('size',5));

            $provisionLabel = new Label("provision_label", "Fourniture Avant de<br />Les services d'avortement", "La mise à disposition préalable des services d'avortement médical ou chirurgical de l'utilisateur", VALIDATE_NOTHING);
            $provisionLabel->attr('style', 'line-height: 1.1em; padding-top:5px;');
            $provisionField = new VerticalRadioBox("provision_field", "Prior Provision of Abortion Services", array("Yes","No"), array("Yes","No"), VALIDATE_NOTHING);
            $provisionRow = new FormTableRow("provision_row");
            $provisionRow->attr('style','display:none');
            $provisionRow->append($provisionLabel)->append($provisionField);

            $disclosureLabelRow = new FormTableRow("disclosureLabel");
            $disclosureLabelRow->attr("style","display:none");
            $disclosureRow = new FormTableRow("disclosure");
            $disclosureRow->attr("style","display:none;");
            $emptyElement = new EmptyElement();
            $disclosureLabel = new CustomElement("disclosure", "disclosure", "disclosure", "<td colspan=2><div id='disclosure_div'style='background: #f0f0f0;'>
                                                                                                Cette communauté fournit des mifépristone médecins formés avec un moyen <br />
                                                                                                pour localiser le pharmacien formé le plus proche. <br />
                                                                                                Acceptez-vous de divulguer le nom et l'emplacement de votre pharmacie pour cette carte?>");
            $disclosureField = new HorizontalRadioBox("disclosure_field", "disclosure_field",array("disclosure_fieldyes", "provision_fieldno"), array("I agree", "I disagree"));
            $disclosureLabelRow->append($disclosureLabel);
            $disclosureRow->append($emptyElement)->append($disclosureField);

            $pharmacyNameLabel = new Label("pharmacy_name_label", "Pharmacie Nom", "Pharmacie Nom", VALIDATE_NOTHING);
            $pharmacyField = new TextField("pharmacy_name_field", "Pharmacy Name", "", VALIDATE_NOTHING);
            $pharmacyRow = new FormTableRow("pharmacy_name_row");
            $pharmacyRow->append($pharmacyNameLabel)->append($pharmacyField);
            $pharmacyRow->attr("style","display:none");

            $pharmacyAddressLabel = new Label("pharmacy_address_label", "Pharmacie Adresse", "Pharmacie Adresse", VALIDATE_NOTHING);
            $pharmacyAddressField = new TextField("pharmacy_address_field", "Pharmacy Address", "", VALIDATE_NOTHING);
            $pharmacyAddressRow = new FormTableRow("pharmacy_address_row");
            $pharmacyAddressRow->attr("style","display:none");
            $pharmacyAddressRow->append($pharmacyAddressLabel)->append($pharmacyAddressField);

           /* $fileLabel = new Label("file_label", "Proof of Certification:</div>
                              <div style='text-align:right; font-size:0.7em'>
                              <a href='#!' onclick='$(\"#fileUploadInfo\").dialog({width:\"221px\",position:{my: \"center\", at:\"center\", of: window}})'>[what is this?]</a>", "The prior file of medical or surgical abortion services of the user", VALIDATE_NOTHING, false);*/
            $fileLabel = new Label("file_label", "Preuve de la certification:</div>
                                                  <div style='text-align:right; font-size:0.7em'>
                                                  <a href='#!' onclick='openDialog()'>[Qu'est-ce que c'est?]</a>", "Le fichier avant des services d'avortement médical ou chirurgical de l'utilisateur", VALIDATE_NOT_NULL, false);
            $fileField = new FileField("file_field", "Preuve de certification", "", VALIDATE_NOT_NULL);
            $fileRow = new FormTableRow("file_row");
            $fileRow->attr('style','line-height: 10px;');
            $fileRow->append($fileLabel)->append($fileField);

            $referenceLabel = new Label("reference_label", "Nom de référence", "Le médecin ou le pharmacien qui a renvoyé l' utilisateur", VALIDATE_NOTHING);
            $referenceField = new TextField("reference_field", "Name of Reference", "", VALIDATE_NOTHING);
            $referenceRow = new FormTableRow("reference_row");
            $referenceRow->append($referenceLabel)->append($referenceField);

            $captchaLabel = new Label("captcha_label", "Entrez le code", "Entrez le code que vous voyez dans l'image", VALIDATE_NOT_NULL);
            $captchaField = new Captcha("captcha_field", "Captcha", "", VALIDATE_NOT_NULL);
            $captchaRow = new FormTableRow("captcha_row");
            $captchaRow->append($captchaLabel)->append($captchaField);
            $termsLabel = new Label("terms_label", "<div style='display:inline-block;vertical-align:top;'>Termes et conditions<br /><small>*Doit faire défiler le formulaire de consentement</small></div>", "Termes et conditions", VALIDATE_NOTHING);
            $termsField = new CustomElement("terms", "terms", "terms", 
                                            
                                               " <div class='TermsOuterDiv'>
                                                $frenchTerms</div>
                                             
                                                                                            
                                             ");
            $termsAgree = new HorizontalCheckBox("terms_agree", "terms_agree", array(), array("J'ai lu et j'accepte les termes du formulaire de consentement"), VALIDATE_NOTHING);
            $termsExtra = new VerticalCheckBox("terms_extra", "terms_extra", 
                                               array("collect_demo", "collect_comments"), 
                                               array("Je suis d'accord pour avoir mes informations démographiques utilisées à des fins de recherche." => "collect_demo",
                                                     "Je suis d'accord pour que mes messages sur les défis, les obstacles et les facilitateurs utilisés à des fins de recherche." => "collect_comments"),
                                               VALIDATE_NOTHING);
            $termsRow = new FormTableRow("terms_row");
            $termsAgree->attr("disabled","disabled");
            $termsRow->append($termsLabel)->append($termsField);
            $agreeRow = new FormTableRow("agree_row");
            $agreeExtraRow = new FormTableRow("agree_extra_row");
            $agreeCell = new EmptyElement();
            $agreeExtraCell = new EmptyElement();
            $agreeRow->append($agreeCell)->append($termsAgree);
            $agreeExtraRow->append($agreeExtraCell)->append($termsExtra);
            $agreeRow->attr("class","terms");
            $submitCell = new EmptyElement();
            $submitField = new SubmitButton("submit", "Envoyer la demande", "Envoyer la demande", VALIDATE_NOTHING);
            $submitField->attr("disabled","disabled");
            $submitRow = new FormTableRow("submit_row");
            $submitRow->append($submitCell)->append($submitField);
            $submitRow->attr("align","right");
        }
        $formTable->append($firstNameRow)
                  ->append($lastNameRow)
                  ->append($emailRow)
                  ->append($languageRow)
                  ->append($postalcodeRow)
                  ->append($cityRow)
                  ->append($provinceRow)
                  ->append($roleRow)
                  ->append($otherRoleRow)
                  ->append($clinicRow)
                  ->append($specialtyRow)
                  ->append($otherSpecialtyRow)
                  ->append($yearsRow)
                  ->append($provisionRow)
                  ->append($disclosureLabelRow)
                  ->append($disclosureRow)
                  ->append($pharmacyRow)
                  ->append($pharmacyAddressRow)
                  //->append($fileRow)
                  //->append($referenceRow)
                  ->append($captchaRow)
                  ->append($termsRow)
                  ->append($agreeExtraRow)
                  ->append($agreeRow)
                  ->append($submitRow);
        
        $formContainer->append($formTable);
        return $formContainer;
    }
    
     function generateFormHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config, $wgLang;
        $user = Person::newFromId($wgUser->getId());
        if($wgLang->getCode() == "en"){
            $wgOut->addHTML("<div id='fileUploadInfo' title='Proof of Certification' style='display:none'>Please upload a copy of your proof of certification from the Mifepristone training program.</div>");
            $wgOut->addHTML("Each submitted form is reviewed by an administrator. You will be contacted by email with your login details when your submission has been approved. You may need to check your spam/junk mail for the registration email.  If you do not get an email after a few business days, please contact <a href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a>.<br /><br />");
        }
        else if($wgLang->getCode() == "fr"){
            $wgOut->addHTML("<div id='fileUploadInfo' title='Proof of Certification' style='display:none'>S'il vous plaît télécharger une copie de votre preuve de certification du programme de formation mifépristone .</div>");
            $wgOut->addHTML("Chaque formulaire soumis est examiné par un administrateur . Vous serez contacté par email avec vos informations de connexion lorsque votre demande a été approuvée. Vous devrez peut-être vérifier votre courrier Spam / jonque pour l'e-mail d'inscription. Si vous ne recevez un courriel après quelques jours ouvrables , s'il vous plaît contacter
 <a href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a>.<br /><br />");
        }
        $wgOut->addHTML("<form action='$wgScriptPath/index.php/Special:CAPSRegister' method='post' enctype='multipart/form-data'>\n");
        $form = self::createForm();
        $wgOut->addHTML($form->render());
        $wgOut->addScript("<script type='text/javascript'>
                            $(document).ready(function () {
                                toggleFields();
                                disclaimerFunction();
                                $('#role_field').change(function () {
                                    toggleFields();
                                });
                                $('#disclosure_field0').click(function (){
                                    $('#pharmacy_address_label').parent().parent().show();
                                    $('#pharmacy_name_label').parent().parent().show();
                                });
                                $('#disclosure_field1').click(function (){
                                    $('#pharmacy_address_label').parent().parent().hide();
                                    $('#pharmacy_name_label').parent().parent().hide();
                                });
                                $('#specialty_field').change(function () {
                                   specialtySpecify();
                                });
                                $(\"input[name='terms_agree[]']\").change(function(){
                                    checkSubmit();
                                });
                            });

                    function toggleFields() {
                        if($('#role_field').val() == 'Physician' || $('#role_field').val() == 'Médecin' ||
                           $('#role_field').val() == 'Nurse Practitioner' || $('#role_field').val() == 'Infirmière Praticienne'){
                            $('#specialty_label').parent().parent().show();
                            $('#years_label').parent().parent().show();
                            $('#provision_label').parent().parent().show();
                            $('#clinic_label').parent().parent().show();
                        }
                        else{
                            $('#specialty_label').parent().parent().hide();
                            $('#years_label').parent().parent().hide();
                            $('#provision_label').parent().parent().hide();
                            $('#clinic_label').parent().parent().hide();
                        }
                        if($('#role_field').val() == 'Pharmacist'){
                            $('#disclosure_div').parent().parent().show();
                            $('input[name=disclosure_field]').parent().parent().show();
                        }
                        else{
                            $('#disclosure_div').parent().parent().hide();
                            $('input[name=disclosure_field]').parent().parent().hide();
                        }
                        if($('#role_field').val() == 'Facility Staff' || $('#role_field').val() == 'Personnel de l\'installation'){
                            $('#other_role_label').parent().parent().show();
                        }
                        else{
                            $('#other_role_label').parent().parent().hide();
                        }
                    }
                    
                    function specialtySpecify() {
                        if ($('#specialty_field').val() == 'Other (Specify)' || $('#specialty_field').val() == 'Autre (précisez)'){
                            $('#other_specialty_label').parent().parent().show();
                        }
                        else{
                            $('#other_specialty_label').parent().parent().hide();
                        }
                    }
                    
                    function openDialog(){
                        $('#fileUploadInfo').dialog({width:'200px',position:{my: 'center', at:'center', of: window},modal:true,resizable:false,     buttons: {
                            'OK': function () {
                                $(this).dialog('close')
                            }
                        }});
                    }

                    function disclaimerFunction() {
                        $(\"input[name='terms_agree[]']\").removeAttr('checked');
                        $('.TermsOuterDiv').scroll(function() {
                            if ($(this).scrollTop()+5 >= $(this)[0].scrollHeight - $(this).innerHeight()) {
                                $(\"input[name='terms_agree[]']\").removeAttr('disabled');
                            }
                        });
                    }
                    
                    function checkSubmit(){
                        if($(\"input[name='terms_agree[]']\").is(':checked')){
                            $(\"input[name='submit']\").removeAttr('disabled');
                        }
                        else{
                            $(\"input[name='submit']\").attr('disabled','disabled');
                        }
                    }
            </script>");
        $wgOut->addHTML("</form>");
    }
    
    function handleSubmit($wgOut){
        global $wgServer, $wgScriptPath, $wgMessage, $wgGroupPermissions;
        $max_file_size = 20;
        $form = self::createForm();
        $status = $form->validate();
        if($status){
            $firstname = $form->getElementById('first_name_field')->setPOST('wpFirstName');
            $lastname = $form->getElementById('last_name_field')->setPOST('wpLastName');
            $email = $form->getElementById('email_field')->setPOST('wpEmail');
            $form->getElementById('role_field')->setPOST('wpRole');
            $_POST['wpOtherRole'] = ""; // Initialize to empty to start with
            if(in_array($_POST['wpRole'], array("Physician", "Médecin"))){
                $_POST['wpUserType'] = CI;
                $_POST['wpRole'] = CI;
            }
            else if(in_array($_POST['wpRole'], array("Pharmacist", "Pharmacien"))){
                $_POST['wpUserType'] = AR;
                $_POST['wpRole'] = AR;
            }
            else if(in_array($_POST['wpRole'], array("Nurse Practitioner", "Infirmière Praticienne"))){
                $_POST['wpUserType'] = NP;
                $_POST['wpRole'] = NP;
            }
            else if(in_array($_POST['wpRole'], array("Midwife", "Sage-Femme"))){
                $_POST['wpUserType'] = MW;
                $_POST['wpRole'] = MW;
            }
            else if(in_array($_POST['wpRole'], array("Facility Staff", "Personnel de l&#39;installation"))){
                $_POST['wpUserType'] = HQP;
                $_POST['wpRole'] = HQP;
                $form->getElementById('other_role_field')->setPOST('wpOtherRole');
            }
            else if(in_array($_POST['wpRole'], array("Other", "Autre"))){
                $_POST['wpUserType'] = EXTERNAL;
                $_POST['wpRole'] = EXTERNAL;
            }
            if(in_array($_POST['wpOtherRole'], array("Nurse", "Infirmière"))){
                $_POST['wpOtherRole'] = "Nurse";
            }
            else if(in_array($_POST['wpOtherRole'], array("Administrator", "Administrateur"))){
                $_POST['wpOtherRole'] = "Administrator";
            }
            else if(in_array($_POST['wpOtherRole'], array("Counsellor", "Conseiller"))){
                $_POST['wpOtherRole'] = "Councillor";
            }
            else if(in_array($_POST['wpOtherRole'], array("Clerical", "Commis de bureau"))){
                $_POST['wpOtherRole'] = "Clerical";
            }
            
            $language = $form->getElementById('language_field')->setPOST('wpLanguage');
            $postalcode = $form->getElementById('postalcode_field')->setPOST('wpPostalCode');
            $city = $form->getElementById('city_field')->setPOST('wpCity');
            $province = $form->getElementById('province_field')->setPOST('wpProvince');
            //$reference = $form->getElementById('reference_field')->setPOST('wpReference');
            $agreeExtra = $form->getElementById('terms_extra')->setPOST('wpAgreeExtra');
            if($_POST['wpRole'] == "Physician" || $_POST['wpRole'] == "Nurse"){
                $clinic = $form->getElementById('clinic_field')->setPOST('wpClinic');
                $provision = $form->getElementById('provision_field')->setPOST('wpProvision');
                if($_POST['wpProvision'] != "Yes" && $_POST['wpProvision'] != "No"){
                    $_POST['wpProvision'] = "";
                }
                $specialty = $form->getElementById('specialty_field')->setPOST('wpSpecialty');
                if($_POST['wpSpecialty'] == "Other (Specify)"){
                    $specialty = $form->getElementById('other_specialty_field')->setPOST('wpSpecialty');
                }
                $years = $form->getElementById('years_field')->setPOST('wpYears');
            }
            if($_POST['wpRole'] == "Pharmacist"){
                $provision = $form->getElementById('disclosure_field')->setPOST('wpDisclosure');
                $pharmacy_name = $form->getElementById('pharmacy_name_field')->setPOST('wpPharmacyName');
                $pharmacy_address = $form->getElementById('pharmacy_address_field')->setPOST('wpPharmacyAddress');
            }
            if(!isset($_FILES['file_field']) || $_FILES['file_field']['size'] < $max_file_size*1024*1024){
                $uid = md5(uniqid(time()));
                
                $msg = "The following person has asked to be registered for the CAPS website."."\n"."Please review and approve the member on https://www.caps-cpca.ubc.ca/index.php/Special:AddMember?action=view" . "\n"."\n";
                $msg .= "First name: ".$_POST['wpFirstName']."\n";
                $msg .= "Last name: ".$_POST['wpLastName']."\n";
                $msg .= "Email: ".$_POST['wpEmail']."\n";
                $msg .= "Role: ".$_POST['wpRole']."\n";
                $msg .= "Postal Code: ".$_POST['wpPostalCode']."\n";
                $msg .= "City: ".$_POST['wpCity']."\n";
                $msg .= "Province: ".$_POST['wpProvince']."\n";
                if($_POST['wpRole'] == "Physician" || $_POST['wpRole'] == "Nurse"){
                    $msg .= "Specialty: ".$_POST['wpSpecialty']."\n";
                    $msg .= "Clinic Name: ".$_POST['wpClinic']."\n";
                    $msg .= "Provision: ".$_POST['wpProvision']."\n";
                }
                if($_POST['wpRole'] == "Pharmacist"){
                    $msg .= "Share Pharmacy Agreement?: ".$_POST['wpDisclosure']."\n";
                    if($_POST['wpDisclosure'] == "I agree"){
                        $msg .= "Pharmacy Name: ".$_POST['wpPharmacyName']."\n";
                        $msg .= "Pharmacy Address: ".$_POST['wpPharmacyAddress']."\n";
                    }
                }

                //$msg .= "Reference: ".$_POST['wpReference']."\n";
                // header
                $header = "From: ".$_POST['wpFirstName']." ".$_POST['wpLastName']." <".$_POST['wpEmail'].">\r\n";
                $header .= "Reply-To: ".$_POST['wpEmail']."\r\n";
                $header .= "MIME-Version: 1.0\r\n";
                $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";

                // message & attachment
                $nmessage = "--".$uid."\r\n";
                $nmessage .= "Content-type:text/plain; charset=iso-8859-1\r\n";
                $nmessage .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
                $nmessage .= $msg."\r\n\r\n";
                $nmessage .= "--".$uid."\r\n";
                if(isset($_FILES['file_field']) && $_FILES['file_field']['tmp_name'] != "" && $_FILES['file_field']['size'] < $max_file_size*1024*1024){
                    $file = $_FILES['file_field']['tmp_name'];           
                    $content = chunk_split(base64_encode(file_get_contents($file)));
                    $nmessage .= "Content-Type: ".$_FILES['file_field']['type']."; name=\""."credentials"."\"\r\n";
                    $nmessage .= "Content-Transfer-Encoding: base64\r\n";
                    $nmessage .= "Content-Disposition: attachment; filename=\""."credentials"."\"\r\n\r\n";
                    $nmessage .= $content."\r\n\r\n";
                    $nmessage .= "--".$uid."--";
                }
                //Note: Change the address here to email to someone else. this will only notify them

                $managers = Person::getAllPeople(MANAGER);
                $email_managers = "";
                foreach($managers as $manager){
                    $email_managers .= $manager->email .",";
                }

                if (mail($email_managers, "New CAPS registration", $nmessage, $header)) {
                    $_POST['wpRealName'] = "{$_POST['wpFirstName']} {$_POST['wpLastName']}";
                    $_POST['wpName'] = ucfirst(str_replace("&#39;", "", strtolower($_POST['wpFirstName']))).".".ucfirst(str_replace("&#39;", "", strtolower($_POST['wpLastName'])));
                    $_POST['wpSendMail'] = "true";
                    $_POST['candidate'] = "1";
                    
                    if(!preg_match("/^[À-Ÿa-zA-Z\-]+\.[À-Ÿa-zA-Z\-]+$/", $_POST['wpName'])){
                        $wgMessage->addError("This User Name is not in the format 'FirstName.LastName'");
                    }
                    else{
                        $result = APIRequest::doAction('RequestUser', false);
                        if($result){
                            $form->reset();
                            $wgMessage->addSuccess("A request has been sent.");
                            redirect("$wgServer$wgScriptPath");
                        }
                    }             
                } 
                else {
                    return false;
                }
            }
            else{
                $wgMessage->addError("The file cannot be larger than {$max_file_size}MB");
            }
        }
        CAPSRegister::generateFormHTML($wgOut);
    }

}

?>
