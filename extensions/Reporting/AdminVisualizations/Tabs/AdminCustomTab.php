<?php

UnknownAction::createAction('AdminCustomTab::showVisualization');

class AdminCustomTab extends AbstractTab {
	
	function AdminCustomTab(){
        parent::AbstractTab("Custom Visualizations");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath;
	    $this->html .= "<div id='customVisualizations'></div>
	    <script type='text/javascript'>
	        $.post('$wgServer$wgScriptPath/index.php?action=showVisualization', {data: [{name: 'Eleni Stroulia',
	                                                                                     visualizations:[{name: 'Timeline', url:'getTimelineData&person=3'},
	                                                                                                     {name: 'Productivity', url:'getDoughnutData&person=3'},
	                                                                                                     {name: 'Survey Graph', url:'getSurveyData&person=3&degree=1'},
	                                                                                                     {name: 'Tag Cloud', url:'getProjectGraphData&project=172'}
	                                                                                                    ]
	                                                                                    }
	                                                                                   ]
	            }, function(response){
	                $('#customVisualizations').html(response);
	            }
	        );
	    </script>";
	}
	
	static function showVisualization($action){
	    global $wgServer, $wgScriptPath;
	    if($action == "showVisualization"){
	        Visualization::$visIndex = 1000;
	        $data = $_POST['data'];
	        foreach($data as $obj){
	            echo '<h1>'.$obj['name'].'</h1>';
	            foreach($obj['visualizations'] as $vis){
	                $visName = $vis['name'];
	                $visUrl = "$wgServer$wgScriptPath/index.php?action=".$vis['url'];
	                echo "<h2>$visName</h2>";
	                if($visName == 'Timeline'){
	                    $vis = new Simile($visUrl);
	                    echo "<link href='$wgServer$wgScriptPath/extensions/Visualizations/Simile/simile.css' type='text/css' rel='stylesheet'>";
                        echo "<script src='$wgServer$wgScriptPath/extensions/Visualizations/Simile/Simile.js' type='text/javascript' charset='utf-8'></script>";
	                    echo $vis->show();
	                }
	                else if($visName == 'Productivity'){
	                    $vis = new Doughnut($visUrl);
	                    echo "<script src='$wgServer$wgScriptPath/extensions/Visualizations/Doughnut/doughnut/popup.js' type='text/javascript' charset='utf-8'></script>";
                        echo "<script src='$wgServer$wgScriptPath/extensions/Visualizations/Doughnut/doughnut/spinner.js' type='text/javascript' charset='utf-8'></script>";
                        echo "<script src='$wgServer$wgScriptPath/extensions/Visualizations/Doughnut/doughnut/doughnut.js' type='text/javascript' charset='utf-8'></script>";
	                    echo $vis->show();
	                }
	                else if($visName == 'Survey Graph'){
	                    $vis = new ForceDirectedGraph($visUrl);
	                    echo "<script src='$wgServer$wgScriptPath/extensions/Visualizations/ForceDirectedGraph/fdg.js' type='text/javascript' charset='utf-8'></script>";
	                    echo $vis->show();
	                }
	                else if($visName == 'Tag Cloud'){
	                    $vis = new Wordle($visUrl);
                        $vis->width = 640;
                        $vis->height = 480;
                        echo "<script src='$wgServer$wgScriptPath/extensions/Visualizations/Wordle/js/d3.layout.cloud.js' type='text/javascript' charset='utf-8'></script>";
                        echo $vis->show();
                        echo "<script type='text/javascript'>onLoad{$vis->index}();</script>";
	                }
	            }
	        }
	        exit;
	    }
	    return true;
	}
	
}
?>
