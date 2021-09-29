<?php

class EliteProjectSelectorReportItem extends CheckboxReportItem {

	function render(){
		global $wgOut;
		$output = "";
		$blob = $this->getBlobValue();
		//var_dump($blob);
        $set = new ElitePostingsReportItemSet();
        $data = $set->getData();
        $output = "<style>
                        #projects #selected_projects li:hover {
                            cursor: move;
                            background: #eeeeee;
                        }
                        
                        #projects .listcontainer {
                            max-height: 300px;
                            overflow-y: auto; 
                            margin: 0 0 0.4em 0;
                        }
                    </style>
                    <div id='projects' style='display:flex;border: 1px solid #dfdfdf;'>
                    <div style='width:50%;padding:0 10px;border-right: 1px solid #dfdfdf;'>
                        <h3 style='margin-top: 0;'>Selected Projects</h3>
                        <div class='listcontainer'>
                            <ol id='selected_projects'>
                                
                            </ol>
                        </div>
                    </div>
                    <div style='width:50%;padding:0 10px;'>
                        <h3 style='margin-top: 0;'>Available Projects</h3>
                        <input type='hidden' name='{$this->getPostId()}[]' value='' />
                        <div class='listcontainer'>
                            <ul id='available_projects' >";
        foreach(@$blob as $row){
            $posting = ElitePosting::newFromId($row);
            $output .= "<li>
                            <input type='checkbox' name='{$this->getPostId()}[]' value='{$posting->getId()}' checked />
                            <a id='{$posting->getId()}' class='elite_link' style='cursor:pointer;'>{$posting->getExtra('companyName')} - {$posting->getTitle()}</a>
                        </li>";
        }
        
        foreach($data as $row){
            $posting = ElitePosting::newFromId($row['product_id']);
            if(@in_array($posting->getId(), $blob)){
                continue;
            }
            
            $output .= "<li>
                            <input type='checkbox' name='{$this->getPostId()}[]' value='{$posting->getId()}' />
                            <a id='{$posting->getId()}' class='elite_link' style='cursor:pointer;'>{$posting->getExtra('companyName')} - {$posting->getTitle()}</a>
                        </li>";
        }
        $output .= "</ul></div>
                </div></div>
                <div id='postingDialog' title='Project Proposal' style='display:none;'></div>
                <script type='text/javascript'>
                    $('#projects #selected_projects').sortable({
                        stop: function(){
                            saveAll();
                        }
                    });
                    
                    function updateProjects(){
                        $('#projects input').each(function(i, el){
                            var checked = $(el).is(':checked');
                            if(checked){
                                var row = $(el).closest('li').detach();
                                $('#projects #selected_projects').append(row);
                            }
                            else{
                                var row = $(el).closest('li').detach();
                                $('#projects #available_projects').append(row);
                            }
                        });
                        if($('#projects #selected_projects li').length >= 5){
                            $('#projects #available_projects input').prop('disabled', true);
                        }
                        else {
                            $('#projects #available_projects input').prop('disabled', false);
                        }
                    }
                    
                    $('#projects input').change(updateProjects);
                    
                    updateProjects();
                
                    var postingDialog = $('#postingDialog').dialog({
                        autoOpen: false,
                        modal: true,
                        show: 'fade',
                        resizable: false,
                        draggable: false,
                        beforeClose: function(){
                            postingDialog.view.stopListening();
                            postingDialog.view.undelegateEvents();
                            postingDialog.view.\$el.empty();
                        }.bind(this)
                    });
                    
                    $(window).resize(function(){
                        postingDialog.dialog({height: $(window).height()*0.75});
                    }.bind(this));
                    
                    $('.elite_link').click(function(e){
                        var id = $(e.target).attr('id');
                        var model = new ElitePosting({id: id});
                        var view = new ElitePostingView({el: postingDialog, model: model, isDialog: true});
                        postingDialog.view = view;
                        postingDialog.dialog({
                            height: $(window).height()*0.75, 
                            width: 800
                        });
                        postingDialog.dialog('open');
                    });
                </script>";
        $output = $this->processCData("<div>{$output}</div>");
		$wgOut->addHTML($output);
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $delimiter = $this->getAttr("delimiter", ", ");
	    $attr = strtolower($this->getAttr("onlyShowIfNotEmpty"));
	    $val = $this->getBlobValue();
        if(is_array($val)){
            $value = array_filter($val);
        }
	    if($attr == "true" && empty($val)){
	        return "";
	    }
	    else if(empty($val)){
	    	$val = array("N/A");
	    }

	    $item = $this->processCData("<i>".implode($delimiter, $val)."</i>");
		$wgOut->addHTML($item);
	}
}

?>
