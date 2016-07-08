<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['HelpfulResources'] = 'HelpfulResources'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['HelpfulResources'] = $dir . 'HelpfulResources.i18n.php';
$wgSpecialPageGroups['HelpfulResources'] = 'network-tools';

function runHelpfulResources($par) {
    HelpfulResources::execute($par);
}

class HelpfulResources extends SpecialPage{

    function HelpfulResources() {
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
        $wgOut->addHTML("<div style='float:left'><span class='en'>Below are the different categories of resources available in {$config->getValue('networkName')}. Click on the icons to view the files for each category.<br /><br />
<a target='_blank' href='http://www.nafcanada.org/'><img src='http://prochoice.org/wp-content/uploads/NAFlogoCanada-small.jpg' width='350'></a><br /><br />
Click <a target='_blank' href='http://prochoice.org/health-care-professionals/naf-membership/'> here</a> to become a member.</span><span class='fr'>Pour rechercher un fichier ou une page en particulier, utiliser les champs de recherche ci-dessous. Vous pouvez rechercher par nom, date dernière édition , et le dernier éditeur.
<br /><br />
<a target='_blank' href='http://www.nafcanada.org/'><img src='http://prochoice.org/wp-content/uploads/NAFlogoCanada-small.jpg' width='350'></a><br /><br />
Cliquez <a target='_blank' href='http://prochoice.org/health-care-professionals/naf-membership/'>ici</a> pour devenir membre.
</span><br /><br />");
	$wgOut->addHTML("<div class='helpful_resources' style='display:inline-block; font-size:1.1em'>
                        <div style='margin-right:10px; display:inline-block; text-align:center'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Clinical'><img width='100px' src='http://grand.cs.ualberta.ca/caps/skins/icons/caps/clinical_guidelines_files.png'></a><br />Clinical Guidelines</div>
			             <div style='margin-right:10px; display:inline-block; text-align:center'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Tools'><img width='100px'  src='http://grand.cs.ualberta.ca/caps/skins/icons/caps/tools_tips_files.png'></a><br />Tools & Tips</div>
			             <div style='margin-right:10px; display:inline-block; text-align:center'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Organizations'><img width='100px' src='http://grand.cs.ualberta.ca/caps/skins/icons/caps/organizations_files.png'></a><br />Organizations</div>
			             <div style='margin-right:10px; display:inline-block; text-align:center'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Articles'><img width='100px' src='http://grand.cs.ualberta.ca/caps/skins/icons/caps/articles_files.png'></a><br />Articles</div>
                    </div>
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
