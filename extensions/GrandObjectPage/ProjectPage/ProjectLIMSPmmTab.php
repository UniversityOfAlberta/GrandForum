<?php

class ProjectLIMSPmmTab extends AbstractEditableTab {
    var $project;
    var $visibility;

    function __construct($project, $visibility)
    {
        parent::__construct("Activities");
        $this->project = $project;
        $this->visibility = $visibility;
        $this->editText = "Edit";
        $this->saveText = "Save";
    }

    function canEdit() {
        return $this->project->isAllowedToView();
    }

    function generateEditBody(){
        return $this->generateView(true);
    }

    function handleEdit() {}

    function generateBody(){
        return $this->generateView(false);
    }

    private function generateView($isEditMode) {
        global $wgUser, $wgServer, $wgScriptPath, $wgOut;
        if ($wgUser->isLoggedIn()) {
            $projectId = $this->project->getId();

            $limsPmm = new LIMSPmm();
            $limsPmm->loadTemplates();
            $limsPmm->loadModels();
            $limsPmm->loadHelpers();
            $limsPmm->loadViews();
            $wgOut->addScript("<link href='$wgServer$wgScriptPath/extensions/GrandObjectPage/LIMSPmm/style.css' type='text/css' rel='stylesheet' />");
            $isEditModeJS = $isEditMode ? 'true' : 'false';
            $this->html = "
                <div id='lims_pmm_project_task_container'></div>
                <script>
                    $(document).ready(function() {
                        new ProjectTaskView({
                            el: '#lims_pmm_project_task_container',
                            projectId: {$projectId},
                            isEditMode: {$isEditModeJS}
                        });
                    });
                </script>
            ";
        }
        return $this->html;
    }
}

?>
