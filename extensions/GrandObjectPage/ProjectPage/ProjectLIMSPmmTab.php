<?php

class ProjectLIMSPmmTab extends AbstractEditableTab {


    var $project;
    var $visibility;

    function __construct($project, $visibility)
    {
        parent::__construct("Activity Management");
        $this->project = $project;
        $this->visibility = $visibility;
    }

    function canEdit() {
        return $this->project->userCanEdit();
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
                        console.log( $('form'));

                        $('form').on('submit', function(e){
                                if(this.submitted == 'Cancel'){
                                    return true;
                                }
                                if($('button[value=\"Save {$this->name}\"]').is(':visible')){
                                    var requests = contactModel.save();
                                    e.preventDefault();
                                    $('button[value=\"Save {$this->name}\"]').prop('disabled', true);
                                    $.when.apply($, requests).then(function(){
                                        $('form').off('submit');
                                        $('button[value=\"Save {$this->name}\"]').prop('disabled', false);
                                        _.delay(function(){
                                            $('button[value=\"Save {$this->name}\"]').click();
                                        }, 10);
                                    });
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
                        contactModel.fetch();

                 });
            </script>
        ";
    }
    return $this->html;
}


}

?>