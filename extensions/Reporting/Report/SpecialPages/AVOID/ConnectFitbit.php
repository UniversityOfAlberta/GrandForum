<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['ConnectFitbit'] = 'ConnectFitbit'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ConnectFitbit'] = $dir . 'ConnectFitbit.i18n.php';
$wgSpecialPageGroups['ConnectFitbit'] = 'other-tools';

class ConnectFitbit extends SpecialPage{

    function __construct() {
        SpecialPage::__construct("ConnectFitbit", null, true);
    }
    
    function userCanExecute($user){
        return $user->isLoggedIn();
    }

    function execute($par){
        global $wgServer, $wgScriptPath, $config;
        $me = Person::newFromWgUser();
        $fitbitEnabled = ($me->getExtra('fitbit') != "" && time() < $me->getExtra('fitbit_expires'));
        if(!$fitbitEnabled){
            $url = "https://www.fitbit.com/oauth2/authorize?response_type=token&client_id={$config->getValue('fitbitId')}&redirect_uri={$wgServer}{$wgScriptPath}/index.php/Special:AVOIDDashboard?fitbitApi&scope=activity%20nutrition%20sleep%20heartrate&expires_in=31536000";
            redirect($url);
        }
        else{
            echo "<html><script type='text/javascript'>
                window.close();
            </script></html>";
            exit;
        }
        exit;
    }

}

?>
