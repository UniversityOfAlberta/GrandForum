<?php

class ProjectDescriptionTab extends AbstractEditableTab {

    var $project;
    var $visibility;

    function ProjectDescriptionTab($project, $visibility){
        parent::AbstractTab("Description");
        $this->project = $project;
        $this->visibility = $visibility;
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath, $config;
        if($wgUser->isLoggedIn()){
            $project = $this->project;
            $this->showDescription();
        }
        return $this->html;
    }
    
    function handleEdit(){
        global $wgOut, $wgMessage;
        $_POST['project'] = $this->project->getName();
        $_POST['fullName'] = $this->project->getFullName();
        $_POST['long_description'] = @$_POST['long_description'];
        $_POST['description'] = $this->project->getDescription();
        if($_POST['long_description'] != $this->project->getLongDescription()){
            $error = APIRequest::doAction('ProjectDescription', true);
            if($error != ""){
                return $error;
            }
            Project::$cache = array();
            Project::$projectDataCache = array();
            $this->project = Project::newFromId($this->project->getId());
            $wgOut->setPageTitle($this->project->getFullName());
        }
    }
    
    function generateEditBody(){
        $this->generateBody();
    }
    
    function canEdit(){
        return (!$this->project->isFeatureFrozen(FREEZE_DESCRIPTION) && $this->project->userCanEdit());
    }
    
    function showDescription(){
        global $wgServer, $wgScriptPath;
        
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        $project = $this->project;
        
        $description = $project->getLongDescription();
        
        if(!$edit){
            $this->html .= $description;
        }
        else{
            $this->html .= "<textarea name='long_description' style='height:500px;'>{$description}</textarea>";
            $this->html .= "<script type='text/javascript'>
                $('textarea[name=long_description]').tinymce({
                    theme: 'modern',
                    relative_urls : false,
                    convert_urls: false,
                    menubar: false,
                    plugins: 'link image charmap lists table paste wordcount',
                    toolbar: [
                        'undo redo | bold italic underline | link charmap | table | bullist numlist outdent indent | alignleft aligncenter alignright alignjustify'
                    ],
                    paste_postprocess: function(plugin, args) {
                        var p = $('p', args.node);
                        p.each(function(i, el){
                            $(el).css('line-height', 'inherit');
                        });
                    }
                });
            </script>";
        }
        
    }

}    
    
?>
