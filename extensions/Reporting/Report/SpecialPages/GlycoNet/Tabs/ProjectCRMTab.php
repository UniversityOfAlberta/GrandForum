<?php

class ProjectCRMTab extends AbstractTab {

    var $project;

    function __construct($project){
        parent::__construct("CRM");
        $this->project = $project;
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath, $config;
        $crm = new CRM();
        $crm->loadTemplates();
        $crm->loadModels();
        $crm->loadHelpers();
        $crm->loadViews();
        $this->html = "<div id='crmTable'></div>
            <script type='text/javascript'>
                var contacts = new CRMContacts();
                var crmView = new CRMProjectContactsTableView({el: $('#crmTable'), 
                                                               model: contacts, 
                                                               projectId: {$this->project->getId()}
                                                              });
            </script>";
    }

}    
    
?>
