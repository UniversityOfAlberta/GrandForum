<?php

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['CrossForumExport'] = 'CrossForumExport';
$wgExtensionMessagesFiles['CrossForumExport'] = $dir . 'CrossForumExport.i18n.php';
$wgSpecialPageGroups['CrossForumExport'] = 'network-tools';

$wgHooks['BeforePageDisplay'][] = 'CrossForumExport::crossForumExport';

function runCrossForumExport($par) {
    CrossForumExport::execute($par);
}

class CrossForumExport extends SpecialPage {

    function userCanExecute($user){
        return true;
    }

    function __construct() {
        SpecialPage::__construct("CrossForumExport", null, false, 'runCrossForumExport');
    }
    
    function execute($par){
        global $wgOut, $wgUser, $config;
        $this->getOutput()->setPageTitle("Cross Forum Export");
        if($wgUser->isLoggedIn()){
            // Handle Exporting
            $me = Person::newFromWgUser();
            $products = $me->getPapers("all", true, 'both', true, 'Public');
            $collection = new Collection($products);
            echo "<script type='text/javascript'>
                var crossForumUrls = ".json_encode($config->getValue('crossForumUrls')).";
                Object.keys(crossForumUrls).forEach(function(key) {
                    var url = crossForumUrls[key];
                    opener.postMessage(".json_encode(implode("", $collection->pluck('toBibTeX()'))).", url);
                });
                window.close();
                setInterval(function(){
                    // If the user was interacting with something in the window, it doesn't close, so try again often
                    window.close();
                }, 100);
            </script>";
            exit;
        }
        else{
            // Handle Login
            self::modifyLogin();
        }
    }
        
    static function modifyLogin(){
        global $wgOut;
        $wgOut->addScript("<style>
            
            #topheader {
                text-align: center;
            }
            
            #topheader > :not(.smallLogo) {
                display:none;
            }
            
            #topheader > .smallLogo {
                float: none;
            }
            
            #side {
                position: absolute;
                top: 0;
                right: 0;
                left: 0;
                width: 100%;
                max-width: none;
                z-index: 1000;
                font-size: 16px;
            }
            
            #side .portlet > span {
                text-align: center;
            }
            
            #side .portlet ul {
                line-height: 2.5em !important;
            }
            
            #side form {
                left: 0 !important;
                display: block;
                margin: 0 auto;
                padding: 0 15px;
            }
            
            #side form > table {
                margin: 0 auto !important;
                width: 100% !important;
            }
            
            #side .mw-input input[type=text], #side .mw-input input[type=password] {
                width: 100% !important;
                box-sizing: border-box;
                -moz-box-sizing: border-box;
                -webkit-box-sizing: border-box;
                height: auto;
            }
            
            #side input[type=submit] {
                width: 100%;
                box-sizing: border-box;
                -moz-box-sizing: border-box;
                -webkit-box-sizing: border-box;
            }
            
        </style>");
        $wgOut->addScript("<script type='text/javascript'>
            $(document).ready(function(){
                _.defer(function(){
                    $('.tooltip').qtip({
                        position: {
                            adjust: {
                                x: -($('.tooltip').width()-25),
                                y: -($('.tooltip').height()/3)
                            }
                        },
                        show: {
                            delay: 500
                        }
                    });
                });
                $('.smallLogo a').attr('href', wgServer + wgScriptPath + '/index.php/Special:CrossForumExport');
                $('#side').append($('form#mw-resetpass-form').detach());
                $('#wpMailmypassword').hide();
            });
        </script>");
    }
    
    static function crossForumExport($out){
        global $wgTitle, $wgOut, $config;
        $wgOut->addScript("<script type='text/javascript'>
        
            var crossForumUrls = ".json_encode($config->getValue('crossForumUrls')).";
        
            var exportFn = function(event) { };
            window.addEventListener('message', function(event){
                exportFn(event);
            }, false);
        
            var openCrossForumExport = function(url, callback){
                exportFn = callback;
                return window.open(url, 'export', 'height=600,width=500,left=100,top=100,resizable=yes,scrollbars=no,toolbar=no,menubar=no,location=no,directories=no, status=yes');
            }
            
        </script>");
        $nsText = ($wgTitle != null) ? str_replace("_", " ", $wgTitle->getNsText()) : "";
        $text = $wgTitle->getText();
        if($nsText == "Special" && $text == "UserLogin"){
            if(isset($_GET['returnto']) && strstr($_GET['returnto'], "CrossForumExport") !== false){
                self::modifyLogin();
            }
        }
        return true;
    }
}

?>
