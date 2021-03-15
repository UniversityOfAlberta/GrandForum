<?php

class ProjectMilestoneHistoryAPI extends API{

    function ProjectMilestoneHistoryAPI(){
        $this->addGET("milestone_id", true, "The id of the milestone", "90");
        $this->addGET("revision_id", false, "The revision id of the milestone", "8343");
        $this->addGET("back_until_date", false, "How far back in time to show (Default is to get all history)", "2011-10-15");
	}

  function processParams($params){
    $i = 0;
	  foreach($params as $param){
	    if($i != 0){
	      if(!isset($_GET['milestone_id']) && is_numeric($param)){
	          $_GET['milestone_id'] = $param;
	      }
	      else if(!isset($_GET['revision_id']) && is_numeric($param)){
	          $_GET['revision_id'] = $param;
	      }
	      else if(!isset($_GET['back_until_date'])){
	        $_GET['back_until_date'] = $param;
	      }
	    }
	    $i++;
	  }
  }

	function doAction(){
	    if(!isset($_GET['milestone_id'])){
	        echo "A milestone id is required";
	        exit;
	    }
	    if(isset($_GET['revision_id'])){
	        $milestone = Milestone::newFromId($_GET['milestone_id'], $_GET['revision_id']);
	    }
	    else{
            $milestone = Milestone::newFromId($_GET['milestone_id']);
        }
        echo $this->historyHTML($milestone, $_GET['back_until_date']);
        exit;
	}
	
	function historyHTML($milestone, $backUntilDate){
	    $diff_html = "";
        $parents = array();

        $m_parent = $milestone;
        $earliestRevision = $m_parent->getRevisionByDate($backUntilDate);
        $earliestId = ($earliestRevision != null) ? $earliestRevision->getId() : 0;
        while(!is_null($m_parent)){
            $parents[] = $m_parent;
            $m_parent = $m_parent->getParent();
            if($m_parent != null && $earliestId == $m_parent->getId()){
                $parents[] = $m_parent;
                break;
            }
        }
        $parents = array_reverse($parents);
        
        $lastTitle = "";
        $lastDesc = "";
        $lastEnd = "";
        $lastComment = "";
        $lastLabel = "";
        $lastPeople = "";
        $lastRev = null;
        foreach($parents as $key => $m_parent){
            $p_status = $m_parent->getStatus();
            if($p_status == "Continuing"){
                continue;
            }
            $changed_on = $m_parent->getStartDate();
            $p_title = stripslashes($m_parent->getTitle());
            $p_end_date = $m_parent->getProjectedEndDate();
            $p_description = str_replace("\r", "", str_replace("<br />", "", str_replace("\n", " ", stripslashes($m_parent->getDescription()))));
            $p_comment = stripslashes(nl2br($m_parent->getComment()));
            if($p_comment){
                $p_comment = "$p_comment";
            }
            if($p_status == "New"){
                $label = "Created";
            }
            else{
                $label = $p_status;
            }
            
            $lastEdit = "";
            if($m_parent->getEditedBy() != null && $m_parent->getEditedBy()->getName() != ""){
                $lastEdit = "<tr><td valign='top'><strong>Last Edited By:</strong></td><td><a target='_blank' href='{$m_parent->getEditedBy()->getUrl()}'>{$m_parent->getEditedBy()->getNameForForms()}</a></td></tr>";
            }
            
            $people = $m_parent->getPeople();
            $p_names = array();
            foreach($people as $p){
                $p_names[] = $p->getNameForForms();
            }
        
            $diffTitle = @htmldiff($lastTitle, $p_title);
            $diffDesc = @htmldiffNL($lastDesc, $p_description);
            $diffEnd = @htmldiff($lastEnd, $p_end_date);
            $diffComment = @htmldiffNL($lastComment, $p_comment);
            $diffLabel = trim(@htmldiff($lastLabel, $label));
            $diffPeople = @htmldiff($lastPeople, implode(", ", $p_names));

            $history_html =<<<EOF
            <div id='milestone_{$m_parent->getId()}' style='padding: 0;'>
            <div><a class='button prev' style='float:left;width:65px;'>Previous</a><a class='button next' style='float:right;width:65px;'>Next</a></div>
             <br />
             <table>
                <tr><td valign='top' style='white-space:nowrap;'><strong>$diffLabel:</strong></td><td>$changed_on</td></tr>
                <tr><td valign='top'><strong>Projected&nbsp;End&nbsp;Date:</strong></td><td>$diffEnd</td></tr>
                <tr><td valign='top'><strong>Title:</strong></td><td>$diffTitle</td></tr>
                <tr><td valign='top'><strong>Description:</strong></td><td>$diffDesc</td></tr>
                <tr><td valign='top'><strong>Comment:</strong></td><td>$diffComment</td></tr>
                <tr><td valign='top'><strong>People&nbsp;Involved:</strong></td><td>$diffPeople</td></tr>
             $lastEdit
            </table>
            
            <script type='text/javascript'>
EOF;
            if($lastRev != null){
                $history_html .= "
                    $('#milestone_{$m_parent->getId()} a.prev').click(function(){
                        $('#milestone_{$m_parent->getId()}').css('display', 'none');
                        $('#milestone_{$lastRev->getId()}').css('display', 'block');
                    });
                    $('#milestone_{$lastRev->getId()}').css('display', 'none');\n";
            }
            else{
                $history_html .= "$('#milestone_{$m_parent->getId()} a.prev').addClass('disabledButton');\n";
            }
            if(isset($parents[$key+1]) && $parents[$key+1] != null && $parents[$key+1]->getStatus() != "Continuing"){
                $nextParent = $parents[$key+1];
                $history_html .= "
                    $('#milestone_{$m_parent->getId()} a.next').click(function(){
                        $('#milestone_{$m_parent->getId()}').css('display', 'none');
                        $('#milestone_{$nextParent->getId()}').css('display', 'block');
                    });\n";
            }
            else{
                $history_html .= "$('#milestone_{$m_parent->getId()} a.next').addClass('disabledButton');\n";
            }
            $history_html .= "</script></div>";
            $lastTitle = $p_title;
            $lastDesc = $p_description;
            $lastEnd = $p_end_date;
            $lastComment = $p_comment;
            $lastLabel = $label;
            $lastPeople = implode(", ", $p_names);
            $lastRev = $m_parent;
            
            $diff_html .= "$history_html";
        }
        
        return $diff_html;
	}
	
	function isLoginRequired(){
		return true;
	}
}

?>
