<?php

class ProjectLIMSPmmTab extends AbstractEditableTab {


    var $project;
    var $visibility;

    function __construct($project, $visibility)
    {
        parent::__construct("Activity Management");
        $this->project = $project;
        $this->visibility = $visibility;
        $this->editText = "Edit";
        $this->saveText = "Save";
    }

    function canEdit() {
        $me = Person::newFromWgUser();
        return ($me->isRoleAtLeast(STAFF) || $me->isMemberOf($this->project));
        // return $this->project->userCanEdit();
    }

    function generateEditBody(){
        global $wgUser, $wgServer, $wgScriptPath, $config, $wgOut;
    if ($wgUser->isLoggedIn()) {
        $project = $this->project;

        $limsPmm = new LIMSPmm();
        $limsPmm->loadTemplates();
        $limsPmm->loadModels();
        $limsPmm->loadHelpers();
        $limsPmm->loadViews();
        $wgOut->addScript("<link href='$wgServer$wgScriptPath/extensions/GrandObjectPage/LIMSPmm/style.css' type='text/css' rel='stylesheet' />");

        $this->html = "

            <div id='lims-contact-container'></div>
            <script>
                $(document).ready(function() {
                        var contactModel = new LIMSContactPmm({ projectId: {$project->getId()} });
                        var contactView = new LIMSContactEditViewPmm({ 
                            model: contactModel,
                             el: '#lims-contact-container',
                             isDialog: true
                        });
                        contactModel.fetch();
                        
                        $('form').on('submit', function(e){
                                if(this.submitted == 'Cancel'){
                                    return true;
                                }
                                if($('button[value=\"Save {$this->name}\"]').is(':visible')){
                                    e.preventDefault();
                                    $('button[value=\"Save {$this->name}\"]').prop('disabled', true);
                                    
                                    // Save Contact
                                    $.when.apply(null, contactView.save()).done(function(){
                                        // Save Opportunities
                                        $.when.apply(null, contactView.saveOpportunities()).done(function(){
                                            // Save Tasks
                                            $.when.apply(null, contactView.saveTasks()).done(function(){
                                                $('form').off('submit');
                                                $('button[value=\"Save {$this->name}\"]').prop('disabled', false);
                                                _.delay(function(){
                                                    $('button[value=\"Save {$this->name}\"]').click();
                                                }, 10);
                                            }.bind(this));
                                        }.bind(this));
                                    }.bind(this)).fail(function(e){
                                        $('button[value=\"Save {$this->name}\"]').prop('disabled', false);
                                        clearAllMessages();
                                        addError(e.responseText, true);
                                    }.bind(this));
                                }
                        });
                 });
            </script>
        ";
    }
    return $this->html;
    }

    function handleEdit() {
        
    }

    function generateBody()
{
    global $wgUser, $wgServer, $wgScriptPath, $config, $wgOut;
    if ($wgUser->isLoggedIn()) {
        $project = $this->project;

        $limsPmm = new LIMSPmm();
        $limsPmm->loadTemplates();
        $limsPmm->loadModels();
        $limsPmm->loadHelpers();
        $limsPmm->loadViews();
        $wgOut->addScript("<link href='$wgServer$wgScriptPath/extensions/GrandObjectPage/LIMSPmm/style.css' type='text/css' rel='stylesheet' />");

        $this->html = "

            <div id='lims-contact-container'></div>
            <script>
                $(document).ready(function() {
                        var contactModel = new LIMSContactPmm({ projectId: {$project->getId()} });
                        var contactView = new LIMSContactViewPmm({ 
                            model: contactModel,
                             el: '#lims-contact-container',
                             isDialog: true
                        });
                 });
            </script>
        ";
    }
    return $this->html;
}


}

?>
