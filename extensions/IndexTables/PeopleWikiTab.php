<?php

class PeopleWikiTab extends AbstractTab {

    var $table;
    var $visibility;

    function PeopleWikiTab($table, $visibility){
        parent::AbstractTab("Wiki");
        $this->table = $table;
        $this->visibility = $visibility;
    }
    
    function uploadFile(){
        global $wgRequest, $wgUser, $wgMessage, $wgServer, $wgScriptPath, $config;
        
        $name = $this->table." ".$_FILES['wpUploadFile']['name'];

        $wgRequest->setVal("wpUpload", true);
        $wgRequest->setVal("wpSourceType", 'file');
        $wgRequest->setVal("action", 'submit');
        $wgRequest->setVal("wpDestFile", $name);
        $wgRequest->setVal("wpDestFileWarningAck", true);
        $wgRequest->setVal("wpIgnoreWarning", true);
        $wgRequest->setVal("wpEditToken", $wgUser->getEditToken());

        $upload = new SpecialUpload($wgRequest);
	    $upload->execute(null);
	    if($upload->mLocalFile != null){
	        $data = DBFunctions::select(array('mw_an_upload_permissions'),
	                                    array('*'),
	                                    array("upload_name" => "File:".str_replace("_", " ", ucfirst($name))));
	        if(count($data) == 0){
	            DBFunctions::insert("mw_an_upload_permissions",
	                                array("upload_name" => "File:".str_replace("_", " ", ucfirst($name)),
	                                      "nsName" => str_replace(" ", "_", $this->table)));
	        }
	        else{
	            DBFunctions::update("mw_an_upload_permissions",
	                                array("upload_name" => "File:".str_replace("_", " ", ucfirst($name)),
	                                      "nsName" => str_replace(" ", "_", $this->table)),
	                                array("upload_name" => "File:".str_replace("_", " ", ucfirst($name))));
	        }
	        $wgMessage->addSuccess("The file <b>{$_FILES['wpUploadFile']['name']}</b> was uploaded successfully");
	    }
	    else{
	        $wgMessage->addError("There was a problem uploading the file");
	    }
	    redirect("{$wgServer}{$wgScriptPath}/index.php/{$config->getValue('networkName')}:ALL_{$this->table}?tab=wiki");
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath, $config;
        
        if(isset($_FILES['wpUploadFile'])){
            $this->uploadFile();
        }
        
        $table = $this->table;
        $me = Person::newFromWgUser();
        $edit = $this->visibility['edit'];
        
        if(!$this->visibility['isMember'] && false){
            return $this->html;
        }
        $this->html .= "Below are all the $this->table Wikipages and Files in {$config->getValue('networkName')}.  To search for a file or page in particular, use the search boxes below.  You can search by name, date last edited, and last editor.<br /><br />"; 
        $this->html .= "<script type='text/javascript'>
            function clickButton(){
                clearWarning();
                var title = $('#newPageTitle').val().trim();
                if(title == ''){
                    addError('The title must not be empty');
                }
                else if(title.indexOf('%') !== -1 ||
                        title.indexOf(':') !== -1 ||
                        title.indexOf('|') !== -1 ||
                        title.indexOf('.') !== -1 ||
                        title.indexOf('?') !== -1 ||
                        title.indexOf('[') !== -1 ||
                        title.indexOf(']') !== -1 ||
                        title.indexOf('{') !== -1 ||
                        title.indexOf('}') !== -1 ||
                        title.indexOf('<') !== -1 ||
                        title.indexOf('>') !== -1){
                    addError('The title must not contain the following characters: <b>%</b>, <b>:</b>, <b>|</b>, <b>.</b>, <b>?</b>, <b>&lt;</b>, <b>&gt;</b>, <b>[</b>, <b>]</b>, <b>{</b>, <b>}</b>');
                }
                else{ 
                    document.location = '$wgServer$wgScriptPath/index.php/{$this->table}_Wiki:' + title + '?action=edit';
                }
                return false;
            }
        </script>
        <a class='button' id='newWikiPage'>New Wiki Page</a>&nbsp;<a class='button' id='newFilePage'>Upload File</a>
        <div id='newWikiPageDiv' style='display:none;'>
            <h2>Create New Wiki Page</h2>
            <form action='' onSubmit='clickButton'>
            <table>
                <tr>
                    <td><b>Title:</b></td><td><input id='newPageTitle' type='text' name='title' size='40' /></td><td><input type='submit' id='createPageButton' value='Create Page' /></td>
                </tr>
            </table>
            </form>
        </div>
        <div id='newFileDiv' style='display:none;'>
            <h2>Upload File</h2>
            <form action='$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:ALL_{$this->table}?tab=wiki' method='post' enctype='multipart/form-data' onSubmit='clickButton'>
            <table>
                <tr>
                    <td><b>File:</b></td>
                    <td><input id='newPageTitle' type='file' name='wpUploadFile' /></td>
                    <td><input type='submit' id='createPageButton' value='Upload' /></td>
                </tr>
            </table>
            </form>
        </div>
        <script type='text/javascript'>
            $('#createPageButton').click(clickButton);
            $('#newWikiPage').click(function(){
                $(this).css('display', 'none');
                $('#newWikiPageDiv').show('fast');
            });
            $('#newFilePage').click(function(){
                $(this).css('display', 'none');
                $('#newFileDiv').show('fast');
            });
        </script>";
        
	$pages = Wiki::getWikiPages($this->table);
        $this->html .= "<h2>Wiki Pages</h2><table id='projectWikiPages' style='background:#ffffff;' cellspacing='1' cellpadding='3' frame='box' rules='all'><thead><tr bgcolor='#F2F2F2'><th>Page Title</th><th>Last Edited</th><th>Last Edited By</th></tr></thead>\n";
        $this->html .= "<tbody>\n";
        foreach($pages as $page){
            if($page->getTitle()->getText() != "Main"){
                $this->html .= "<tr>\n";
                $revId = $page->getRevIdFetched();
                $revision = Revision::newFromId($revId);
			    $date = $revision->getTimestamp();
			    $year = substr($date, 0, 4);
			    $month = substr($date, 4, 2);
			    $day = substr($date, 6, 2);
			    $hour = substr($date, 8, 2);
			    $minute = substr($date, 10, 2);
			    $second = substr($date, 12, 2);
			    $editor = Person::newFromId($revision->getUser());
                $this->html .= "<td><a href='$wgServer$wgScriptPath/index.php/{$this->table}_Wiki:".str_replace("'", "%27", "{$page->getTitle()->getText()}")."'>{$page->getTitle()->getText()}</a></td>\n";
                $this->html .= "<td>{$year}-{$month}-{$day} {$hour}:{$minute}:{$second}</td>\n";
                $this->html .= "<td><a href='{$editor->getUrl()}'>{$editor->getReversedName()}</a></td>\n";
                $this->html .= "</tr>\n";
            }
        }
        $this->html .= "</tbody></table>";
        
        $pages = Wiki::getFiles($this->table);
        $this->html .= "<h2>Uploaded Files</h2><table id='projectFiles' style='background:#ffffff;' cellspacing='1' cellpadding='3' frame='box' rules='all'><thead><tr bgcolor='#F2F2F2'><th>Page Title</th><th>Last Edited</th><th>Last Edited By</th></tr></thead>\n";
        $this->html .= "<tbody>\n";
        foreach($pages as $page){
            if($page->getTitle()->getText() != "Main"){
                $this->html .= "<tr>\n";
                $revId = $page->getRevIdFetched();
                $revision = Revision::newFromId($revId);
			    $date = $revision->getTimestamp();
			    $year = substr($date, 0, 4);
			    $month = substr($date, 4, 2);
			    $day = substr($date, 6, 2);
			    $hour = substr($date, 8, 2);
			    $minute = substr($date, 10, 2);
			    $second = substr($date, 12, 2);
			    $editor = Person::newFromId($revision->getUser());
                $this->html .= "<td><a href='$wgServer$wgScriptPath/index.php/File:".str_replace("'", "%27", "{$page->getTitle()->getText()}")."'>{$page->getTitle()->getText()}</a></td>\n";
                $this->html .= "<td>{$year}-{$month}-{$day} {$hour}:{$minute}:{$second}</td>\n";
                $this->html .= "<td><a href='{$editor->getUrl()}'>{$editor->getReversedName()}</a></td>\n";
                $this->html .= "</tr>\n";
            }
        }
        $this->html .= "</tbody></table>";
        $this->html .= "<script type='text/javascript'>
            $('#projectWikiPages').dataTable({'iDisplayLength': 100, 'autoWidth': false});
        </script>";
        $this->html .= "<script type='text/javascript'>
            $('#projectFiles').dataTable({'iDisplayLength': 100, 'autoWidth': false});
        </script>";
        return $this->html;
    }

}    
    
?>
