<?php

class IntroTab extends AbstractSurveyTab {

   
    function IntroTab(){
        global $wgOut;
        parent::AbstractSurveyTab("Intro n Consent");
        $this->title = "Introductory Letter";
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        
        $this->showIntro();
        $this->showForm();
        return $this->html;
    }

    function showIntro(){
        config $config;
        $this->html =<<<EOF

<div>
<p>Part of {$config->getValue('networkName')}'s mandate is to create a community of professionals engaged in collaborative research, innovation, and knowledge transfer. Understanding the relationships among them is crucial for the success of {$config->getValue('networkName')}.</p>
<p>Many of you are already familiar with the NAVEL (Network Assessment and Validation for Effective Leadership) project: we trace the formation and evolution of the collaborative relationships among {$config->getValue('networkName')}'s participants. Our 2010 baseline survey showed how the researchers in {$config->getValue('networkName')} are connected to each other before the network was fully operational. This second survey will enable us to capture the changes in the relations among {$config->getValue('networkName')} researchers and will give a chance to new participants to get themselves on the {$config->getValue('networkName')} map.</p>
<p>We are confident that our second survey is easier to complete. The median time for the completion of the first survey was 39 min although this time can vary significantly depending on how well connected a person is. You can complete the survey in MULTIPLE SESSIONS: you can stop at any time and come back to it later.</p>
<p>We know that there are constraints on your time but it is only with your help - and with the input of as many of {$config->getValue('networkName')}'s participants as possible - that we can learn how the network functions. For those who complete the survey, we will offer the analyses we have produced and a brief individual report. Thank you in advance for your time and effort.</p>

<p style='text-align:right;'>Barry Wellman</p>
</div>
EOF;
    }

    function showForm(){
        global $wgOut, $wgServer, $wgScriptPath;


        $this->html .=<<<EOF
            <form id='introForm' action='$wgServer$wgScriptPath/index.php/Special:Survey' method='post'>
                <input type='hidden' name='submit' value='Save Intro'>
                <br />
                <input type="submit" value="Next" />
            </form>
EOF;

    }
    function handleEdit(){
        return false;
    }
}
   
?>
