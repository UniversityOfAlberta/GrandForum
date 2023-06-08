<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['HowDidYouHearOfUs'] = 'HowDidYouHearOfUs'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['HowDidYouHearOfUs'] = $dir . 'HowDidYouHearOfUs.i18n.php';
$wgSpecialPageGroups['HowDidYouHearOfUs'] = 'other-tools';

$wgHooks['SubLevelTabs'][] = 'HowDidYouHearOfUs::createSubTabs';

class HowDidYouHearOfUs extends SpecialPage{

    function __construct() {
        SpecialPage::__construct("HowDidYouHearOfUs", STAFF.'+', true);
    }

    function handleEdit(){
        global $wgMessage, $wgServer, $wgScriptPath;
        $person = Person::newFromId($_POST['user_id']);
        $person->extra['hearField'] = @$_POST['hear_field'];
        $person->extra['hearLocationSpecify'] = @$_POST['hear_location_specify'];
        $person->extra['hearPlatformSpecify'] = @$_POST['hear_platform_specify'];
        $person->extra['hearPlatformOtherSpecify'] = @$_POST['hear_platform_other_specify'];
        $person->extra['hearProgramOtherSpecify'] = @$_POST['hear_other_specify'];
        
        $status = $person->update();
        
        if($status){
            $wgMessage->addSuccess("User Updated");
        }
        else{
            $wgMessage->addError("There was an error updating this user");
        }
        redirect("{$wgServer}{$wgScriptPath}/index.php/Special:HowDidYouHearOfUs");
    }

    function execute($par){
        global $wgOut, $wgServer, $wgScriptPath;
        
        if(isset($_POST['submit'])){
            $this->handleEdit();
        }
        
        $wgOut->setPageTitle("How Did You Hear Of Us?");
        
        $allPeople = Person::getAllCandidates('all');
        
        $wgOut->addHTML("<form action='{$wgServer}{$wgScriptPath}/index.php/Special:HowDidYouHearOfUs' method='post'>
                         <select id='names' data-placeholder='Chose a Person...' name='user_id'>
                            <option value=\"0\" selected></option>\n");
	    foreach($allPeople as $person){
	        $wgOut->addHTML("<option value=\"{$person->getId()}\">".str_replace(".", " ", $person->getNameForForms())."</option>\n");
	    }
	    $wgOut->addHTML("</select>");
	    
	    $formContainer = new FormContainer("form_container");
        $formTable = new FormTable("form_table");
	    
	    $hearLabel = new Label("hear_label", "<en>How did you hear about the AVOID Frailty program?</en><fr>Comment avez-vous entendu parler du programme AVOID Frailty?</fr>", "How did you hear about the AVOID Frailty program?", VALIDATE_NOT_NULL);
        $hearLabel->colon = "";
        $hearLabel->attr('class', 'label tooltip left-align');
        $hearRow1 = new FormTableRow("hear_row1");
        $hearRow1->append($hearLabel);
        $hearField = new SelectBox("hear_field", "Hear", "", array("", "Canadian Frailty Network website", "Poster, flyer, or pamphlet at community venue", "Newspaper", "Magazine or Newsletter", "Healthcare practitioner", "Social media", "Word of mouth", "Event", "Radio", "Mail", "Television", "Other"), VALIDATE_NOT_NULL);
        $hearRow2 = new FormTableRow("hear_row2");
        $hearRow2->append($hearField);
        
        $hearLocationLabel = new Label("hear_label", "If you remember the location, please specify", "If you remember the location, please specify", VALIDATE_NOTHING);
        $hearLocationLabel->attr('class', 'tooltip left-align');
        $hearRow3 = new FormTableRow("hear_row3");
        $hearRow3->append($hearLocationLabel);
        $hearLocationField = new TextField("hear_location_specify", "Hear", "", VALIDATE_NOTHING);
        $hearRow4 = new FormTableRow("hear_row4");
        $hearRow4->append($hearLocationField);
        
        $hearPlatformLabel = new Label("hear_label", "Please specify platform", "Please specify platform", VALIDATE_NOTHING);
        $hearPlatformLabel->attr('class', 'tooltip left-align');
        $hearRow5 = new FormTableRow("hear_row5");
        $hearRow5->append($hearPlatformLabel);
        $hearPlatformField = new VerticalRadioBox("hear_platform_specify", "Hear", "", array("Facebook", "Twitter", "LinkedIn", "Other"), VALIDATE_NOTHING);
        $hearRow6 = new FormTableRow("hear_row6");
        $hearRow6->append($hearPlatformField);
        
        $hearPlatformOtherLabel = new Label("hear_label", "Specify", "Specify", VALIDATE_NOTHING);
        $hearPlatformOtherLabel->attr('class', 'tooltip left-align');
        $hearRow7 = new FormTableRow("hear_row7");
        $hearRow7->append($hearPlatformOtherLabel);
        $hearPlatformOtherField = new TextField("hear_platform_other_specify", "Hear", "", VALIDATE_NOTHING);
        $hearRow8 = new FormTableRow("hear_row8");
        $hearRow8->append($hearPlatformOtherField);
        
        $hearOtherLabel = new Label("hear_label", "Please specify", "Please specify", VALIDATE_NOTHING);
        $hearOtherLabel->attr('class', 'tooltip left-align');
        $hearRow9 = new FormTableRow("hear_row9");
        $hearRow9->append($hearOtherLabel);
        $hearOtherField = new TextField("hear_other_specify", "Hear", "", VALIDATE_NOTHING);
        $hearRow10 = new FormTableRow("hear_row10");
        $hearRow10->append($hearOtherField);
        
        $submitField = new SubmitButton("submit", "Submit", "Submit", VALIDATE_NOTHING);
        $submitField->buttonText = "Submit";
        $submitRow = new FormTableRow("submit_row");
        $submitRow->append($submitField);
        
        $formTable->append($hearRow1)
                  ->append($hearRow2)
                  ->append($hearRow3)
                  ->append($hearRow4)
                  ->append($hearRow5)
                  ->append($hearRow6)
                  ->append($hearRow7)
                  ->append($hearRow8)
                  ->append($hearRow9)
                  ->append($hearRow10)
                  ->append($submitRow);
                  
        $formContainer->append($formTable);
        $wgOut->addHTML("<div id='formContainer' style='display: none;'>
                            {$formContainer->render()}
                        </div>
                        </form>");
	    
	    $wgOut->addHTML("<script type='text/javascript'>
	        
	        $('#names').chosen();
	        
	        $('#names').change(function(){
	            var id = $('#names option:selected').val();
	            var person = new Person({id: id});
	            person.on('sync', function(){
	                var extra = person.get('extra');
	                $('#formContainer').show();
	                
	                $('[name=hear_field] option').prop('selected', false);
	                $('[name=hear_platform_specify]').prop('checked', false);
	                
	                $('[name=hear_field] option[value=\"' + extra.hearField + '\"]').prop('selected', true);
	                $('[name=hear_location_specify]').val(extra.hearLocationSpecify);
	                $('[name=hear_platform_specify][value=\"' + extra.hearPlatformSpecify + '\"]').prop('checked', true);
	                $('[name=hear_platform_other_specify]').val(extra.hearPlatformOtherSpecify);
	                $('[name=hear_other_specify]').val(extra.hearProgramOtherSpecify);
	                
	                specifyFrail();
	            });
	            person.fetch();
	        });
	        
	        // How did you hear about us?
            $('#hear_row3, #hear_row4').hide();
            $('#hear_row5, #hear_row6').hide();
            $('#hear_row7, #hear_row8').hide();
            $('#hear_row9, #hear_row10').hide();
            
            function specifyFrail(){
                if($(\"select[name='hear_field\").val() == 'Poster, flyer, or pamphlet at community venue'){
                    $('#hear_row3, #hear_row4').show();
                    $('#hear_row5, #hear_row6').hide();
                    $('#hear_row7, #hear_row8').hide();
                    $('#hear_row9, #hear_row10').hide();
                }
                else if($(\"select[name='hear_field\").val() == 'Social media'){
                    $('#hear_row3, #hear_row4').hide();
                    $('#hear_row5, #hear_row6').show();
                    $('#hear_row7, #hear_row8').hide();
                    $('#hear_row9, #hear_row10').hide();
                }
                else if($(\"select[name='hear_field\").val() == 'Other'){
                    $('#hear_row3, #hear_row4').hide();
                    $('#hear_row5, #hear_row6').hide();
                    $('#hear_row7, #hear_row8').hide();
                    $('#hear_row9, #hear_row10').show();
                }
                else{ 
                    $('#hear_row3, #hear_row4').hide();
                    $('#hear_row5, #hear_row6').hide();
                    $('#hear_row7, #hear_row8').hide();
                    $('#hear_row9, #hear_row10').hide();
                }
                
                if($(\"input:radio[name='hear_platform_specify']\").is(':visible') && 
                   $(\"input:radio[name='hear_platform_specify']:checked\").val() == 'Other'){
                    $('#hear_row7, #hear_row8').show();
                }
                else{ 
                    $('#hear_row7, #hear_row8').hide();
                }
            }
            
            $(\"select[name='hear_field']\").change(specifyFrail);
            $(\"input:radio[name='hear_platform_specify']\").change(specifyFrail);
            specifyFrail();

	    </script>");
    }
    
    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
        $person = Person::newFromWgUser();
        if($person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "HowDidYouHearOfUs") ? "selected" : false;
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("Hear Of Us?", "{$wgServer}{$wgScriptPath}/index.php/Special:HowDidYouHearOfUs", $selected);
        }
        return true;
    }

}

?>
