<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['HelpfulResources'] = 'HelpfulResources'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['HelpfulResources'] = $dir . 'HelpfulResources.i18n.php';
$wgSpecialPageGroups['HelpfulResources'] = 'network-tools';

function runHelpfulResources($par) {
    HelpfulResources::execute($par);
}

class HelpfulResources extends SpecialPage{

    function __construct() {
        SpecialPage::__construct("HelpfulResources", null, false, 'runHelpfulResources');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return $person->isLoggedIn();
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
            HelpfulResources::generateHTML($wgOut);
    }
    
     function generateHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config;
        $user = Person::newFromId($wgUser->getId());
        $wgOut->addHTML("<div style='float:left'><span class='en'>Below are the different categories of resources available in {$config->getValue('networkName')}. Click on the icons to view the files for each category.</span><span class='fr'>
Voici les différentes catégories de ressources disponibles dans {$config->getValue('networkName')}. Cliquez sur les icônes pour afficher les fichiers pour chaque catégorie .</span><br /><br />
<div class='helpful_resources' style='display:inline-block; font-size:1.1em'>
                        <div style='margin-right:10px; display:inline-block; text-align:center'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Clinical'><img width='100px' src='$wgServer$wgScriptPath/skins/icons/caps/clinical_guidelines_files.png'></a><br /><span class='en'>Clinical Guidelines</span><span class='fr'>Lignes directrices cliniques</span></div>
			             <div style='margin-right:10px; display:inline-block; text-align:center'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Tools'><img width='100px'  src='$wgServer$wgScriptPath/skins/icons/caps/tools_tips_files.png'></a><br /><span class='en'>Tools & Tips</span><span class='fr'>Outils et conseils</span></div>
			             <div style='margin-right:10px; display:inline-block; text-align:center'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Organizations'><img width='100px' src='$wgServer$wgScriptPath/skins/icons/caps/organizations_files.png'></a><br /><span class='en'>Organizations</span><span class='fr'>Organizations</span></div>
			             <div style='margin-right:10px; display:inline-block; text-align:center'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Articles'><img width='100px' src='$wgServer$wgScriptPath/skins/icons/caps/articles_files.png'></a><br /><span class='en'>Articles</span><span class='fr'>Des articles</span></div>
                        <div style='margin-right:10px; display:inline-block; text-align:center'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Patients'><img width='100px' src='$wgServer$wgScriptPath/skins/icons/caps/patient_resource_files.png'></a><br /><span class='en'>Patient Resources</span><span class='fr'>les ressources des patients</span></div>
                        <div style='margin-right:10px; display:inline-block; text-align:center'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Canadian'><img width='100px' src='$wgServer$wgScriptPath/skins/icons/caps/canadian.png'></a><br /><span class='en'>Canadian Resources</span><span class='fr'>les ressources Canadiennes</span></div>
                        <div style='margin-right:10px; display:inline-block; text-align:center; vertical-align:top;'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Nursing'><img width='100px' src='$wgServer$wgScriptPath/skins/icons/caps/nursing.png'></a><br /><span class='en'>Advanced Nursing<br />Practice Resources</span><span class='fr'>Ressources de pratique<br />infirmière avancée</span></div>
                        <div style='margin-right:10px; display:inline-block; text-align:center'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Formulaires en français'><img width='100px' src='$wgServer$wgScriptPath/skins/icons/caps/french.png'></a><br /><span class='en'>Formulaires en français</span><span class='fr'>Formulaires en français</span></div>
                    </div><br /><br /><br />
<center><hr style='width:80%;' /></center><br />");
	$wgOut->addHTML("
	    <a href='http://www.cart-grac.ubc.ca' target='_blank'><img src='../skins/UBC_logo.png' onerror='this.src='skins/UBC_logo.png';' width='100' /></a>
<a href='http://sogc.org/' target='_blank'><img class='en' src='../skins/OBGYN.png' onerror='this.src='skins/OBGYN.png';' width='250' /><img class='fr' src='../skins/french_obgyn.png' onerror='this.src='skins/OBGYN.png';' width='250' /></a>
<a href='http://www.cfpc.ca/' target='_blank'><img src='../skins/CFPC.png' onerror='this.src='skins/CFPC.png';' width='250' /></a><br />
<a href='http://www.pharmacists.ca/' target='_blank'><img src='../skins/CPhA.png' onerror='this.src='skins/CPhA.png';' width='250' /></a>
<a href='http://cart-grac.ubc.ca/' target='_blank'><img src='../skins/CART.png' onerror='this.src='skins/CART.png';' width='250' /></a><br />
<a href='https://www.inspq.qc.ca/' target='_blank'><img src='../skins/INSPQ_logo.png' onerror='this.src='skins/INSPQ_logo.png';' width='250' /></a>
</br></br>
       </div>");
            $html = <<<EOF
                <div id='twitter' style='width: 200px%; text-align: right; float:right;'>
                    <div>
                        <a class="twitter-timeline" width="100%" height="400" href="https://twitter.com/cartgrac" data-screen-name="cartgrac" data-widget-id="553303321864196097">Tweets by @cartgrac</a>
                        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
                    </div>
                </div>
EOF;

	$wgOut->addHTML($html);
	$wgOut->addScript("<script type='text/javascript'>
		</script>");	
    }

}

?>
