<?php

class ThemeMainTab extends AbstractEditableTab {

    var $theme;
    var $visibility;

    function __construct($theme, $visibility){
        parent::__construct("Main");
        $this->theme = $theme;
        $this->visibility = $visibility;
    }
    
    function handleEdit(){
        DBFunctions::update('grand_themes',
                            array('description' => $_POST['description'],
                                  'resources'   => $_POST['resources'],
                                  'wiki'        => $_POST['wiki']),
                            array('id' => EQ($this->theme->getId())));
        redirect("{$this->theme->getUrl()}");
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath, $config;
        
        $this->showDescription();
        $this->showLeaders();
        $this->showProjects();
        $this->showResources();
        $this->showWiki();
    }
    
    function generateEditBody(){
        $this->generateBody();
    }
    
    function showDescription(){
        $description = $this->theme->getDescription();
        if($this->visibility['edit']){
            $description = str_replace("<", "&lt;", 
                           str_replace(">", "&gt;", $description));
            $this->html .= "<div>
                <b>Descrption</b><br />
                <textarea style='width:100%;height:300px;' name='description'>{$description}</textarea>
            </div>
            <script type='text/javascript'>
                $('textarea[name=description]').tinymce({
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
        else{
            $description = $description;
            $this->html .= "<div>{$description}</div>";
        }
    }
    
    function showLeaders(){
        $leaders = $this->theme->getLeaders();
        $coordinators = $this->theme->getCoordinators();
        if(count($leaders) > 0 || count($coordinators) > 0){
            $this->html .= "<h2>Leaders/Coordinators</h2><ul>";
            foreach($leaders as $leader){
                $this->html .= "<li><a href='{$leader->getUrl()}'>{$leader->getReversedName()}</a> (Leader)</li>";
            }
            foreach($coordinators as $coord){
                $this->html .= "<li><a href='{$coord->getUrl()}'>{$coord->getReversedName()}</a> (Coordinator)</li>";
            }
            $this->html .= "</ul>";
        }
    }
    
    function showProjects(){
        $projects = $this->theme->getProjects();
        if(count($projects) > 0){
            $this->html .= "<h2>Projects</h2><ul>";
            foreach($projects as $project){
                $this->html .= "<li><a href='{$project->getUrl()}'>{$project->getFullName()} ({$project->getName()})</a>";
                $subprojects = $project->getSubProjects();
                if(count($subprojects) > 0){
                    $this->html .= "<ul>";
                    foreach($subprojects as $sub){
                        $this->html .= "<li><a href='{$sub->getUrl()}'>{$sub->getFullName()} ({$sub->getName()})</a></li>";
                    }
                    $this->html .= "</ul>";
                }
                $this->html .= "</li>";
            }
            $this->html .= "</ul>";
        }
    }
    
    function showResources(){
        $resources = $this->theme->getResources();
        if($this->visibility['edit']){
            $this->html .= "<div>
                <h2>Resources</h2>
                <textarea style='width:100%;height:300px;' name='resources'>{$resources}</textarea>
            </div>
            <script type='text/javascript'>
                $('textarea[name=resources]').tinymce({
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
        else if($resources != ""){
            $this->html .= "<h2>Resources</h2><div>{$resources}</div>";
        }
    }
    
    function showWiki(){
        $wiki = $this->theme->getWiki();
        if($this->visibility['edit']){
            $this->html .= "<div>
                <h2>Wiki</h2>
                <textarea style='width:100%;height:300px;' name='wiki'>{$wiki}</textarea>
            </div>
            <script type='text/javascript'>
                $('textarea[name=wiki]').tinymce({
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
        else if($wiki != ""){
            $this->html .= "<h2>Wiki</h2><div>{$wiki}</div>";
        }
    }
    
    function canEdit(){
        return $this->theme->userCanEdit();
    }

}    
    
?>
