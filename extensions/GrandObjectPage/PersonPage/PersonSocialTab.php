<?php
require_once ("symfony/vendor/autoload.php");

class PersonSocialTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonSocialTab($person, $visibility){
        parent::AbstractTab("Social");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        // Make it so the tab reloads when person changes. ie: remove all social info should remove the social tab.
        $this->person = Person::newFromId($this->person->id);
        global $wgMessage;


        $this->html .= 
        "<style>
            div.gallery {
                width: 100%;
                height: auto;
            }
            .responsive {
                padding: 0 6px;
                float: left;
                width: 40.9999%;
            }
            @media only screen and (max-width:850px){
                .responsive {
                    width: 100%;
                    margin: 6px 0;
                }
            }
            .clearfix:after {
                content: \"\";
                display: table;
                clear: both;
            }
        </style>";

        $p = $this->person;
        if($p->getTwitter() != ""){
            $this->html .= "
                <div id='twitter' class='responsive' text-align: right; overflow: hidden; position:relative;'>
                    <div class='gallery'> 
                        <a class=\"twitter-timeline\" width=\"100%\" height=\"400\" href=\"https://twitter.com/{$p->getTwitter()}\" data-screen-name=\"{$p->getTwitter()}\" data-widget-id=\"553303321864196097\">Tweets by @{$p->getTwitter()}</a>
                        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+\"://platform.twitter.com/widgets.js\";fjs.parentNode.insertBefore(js,fjs);}}(document,\"script\",\"twitter-wjs\");</script>
                    </div>
                </div>
                ";
        }

        if($p->getLinkedin() != "") {
            $this->html .= '
            <div class="responsive">
                <div class="gallery">
                    <script src="//platform.linkedin.com/in.js" type="text/javascript"></script>
                    <script type="IN/MemberProfile" data-id=' . "{$p->getLinkedin()}" . ' data-format="inline" data-related="false"></script>
                </div>
            </div>';
        }

    }
}
?>
