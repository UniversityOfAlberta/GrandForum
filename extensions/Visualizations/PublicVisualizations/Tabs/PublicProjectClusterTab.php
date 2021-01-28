<?php

UnknownAction::createAction('PublicProjectClusterTab::getProjectClusterData');

class PublicProjectClusterTab extends AbstractTab {
	
	function PublicProjectClusterTab(){
        parent::AbstractTab("Project Cluster");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath;
        $cluster = new Cluster("{$wgServer}{$wgScriptPath}/index.php?action=getProjectClusterData");
        $cluster->height = 800;
        $cluster->width = 800;
        $this->html .= $cluster->show();
        $this->html .= "<script type='text/javascript'>
            $('#publicVis').bind('tabsselect', function(event, ui) {
                if(ui.panel.id == 'project-cluster'){
                    onLoad{$cluster->index}();
                }
            });
            </script><br />";
	}
	
	static function getProjectClusterData($action, $article){
	    global $wgServer, $wgScriptPath;
	    $me = Person::newFromWgUser();
	    if($action == "getProjectClusterData"){
	        session_write_close();
	        $data = array("name" => "",
	                      "children" => array());
	        
	        $themes = array();
	        $projects = Project::getAllProjectsEver();
	        foreach($projects as $project){
	            if($project->getPhase() == PROJECT_PHASE){
	                $theme = $project->getChallenge();
	                @$themes[$theme->getAcronym()][$project->getId()] = $project;
	            }
	        }
	        
	        foreach($themes as $name => $projs){
	            $theme = Theme::newFromName($name);
	            $tFullName = $theme->getName();
	            $tDesc = $theme->getDescription();
	            $tleaders = $theme->getLeaders();
	            $color = $theme->getColor();
	            $turl = $theme->getUrl();
	            $image = "";
	            switch($name){
	                case "(Big) Data":
	                    $image = "data.png";
	                    break;
	                case "Citizenship":
	                    $image = "citizenship.png";
	                    break;
	                case "Entertainment":
	                    $image = "entertainment.png";
	                    break;
	                case "Health":
	                    $image = "health.png";
	                    break;
	                case "Learning":
	                    $image = "learning.png";
	                    break;
	                case "Sustainability":
	                    $image = "sustainability.png";
	                    break;
	                case "Work":
	                    $image = "work.png";
	                    break;
	            }
	            
	            $themeChildren = array();
	            foreach($projs as $proj){
	                $subs = $proj->getSubProjects();
	                $projChildren = array();
	                $pleaders = $proj->getLeaders();
	                foreach($subs as $sub){
	                    $sleaders = $sub->getLeaders();
	                    $slead = array("name" => "",
	                                   "uni" => "");
	                    if(count($sleaders) > 0){
	                        $sleaders = array_values($sleaders);
	                        $slead['name'] = $sleaders[0]->getNameForForms();
	                        $slead['uni'] = $sleaders[0]->getUni();
	                    }
	                    $projChildren[] = array("name" => $sub->getName(),
	                                            "fullname" => $sub->getFullName(),
	                                            "description" => $sub->getDescription(),
	                                            "color" => $color,
	                                            "url" => $sub->getUrl(),
	                                            "leader" => $slead);
	                }
	                $plead = array("name" => "",
	                               "uni" => "");
	                if(count($pleaders) > 0){
	                    $pleaders = array_values($pleaders);
	                    $plead['name'] = $pleaders[0]->getNameForForms();
	                    $plead['uni'] = $pleaders[0]->getUni();
	                }
	                $themeChildren[] = array("name" => $proj->getName(),
	                                         "fullname" => $proj->getFullName(),
	                                         "description" => $proj->getDescription(),
	                                         "color" => $color,
	                                         "url" => $proj->getUrl(),
	                                         "leader" => $plead,
	                                         "children" => $projChildren);
	            }
	            
	            $tlead = array("name" => "",
	                           "uni" => "");
	            if(count($tleaders) > 0){
	                $tleaders = array_values($tleaders);
	                $tlead['name'] = $tleaders[0]->getNameForForms();
	                $tlead['uni'] = $tleaders[0]->getUni();
	            }
	            if($image != ""){
	                $image = "{$wgServer}{$wgScriptPath}/extensions/Visualizations/Cluster/images/{$image}";
	                $data['children'][] = array("name" => $name,
	                                            "fullname" => $tFullName,
	                                            "description" => $tDesc,
	                                            "color" => $color,
	                                            "image" => $image,
	                                            "url" => $turl,
	                                            "text" => "below",
	                                            "leader" => $tlead,
	                                            "children" => $themeChildren);
	            }
	            else{
	                $data['children'][] = array("name" => $name,
	                                            "color" => $color,
	                                            "url" => $turl,
	                                            "leader" => $tlead,
	                                            "children" => $themeChildren);
	            }
	        }
	        
	        header("Content-Type: application/json");
	        echo json_encode($data);
	        exit;
            echo <<<EOF
{
 "name": "",
 "children": [
  {
   "name": "(Big) Data",
   "color": "#02a6ea",
   "image": "{$wgServer}{$wgScriptPath}/extensions/Visualisations/Cluster/images/data.png",
   "text": "below",
   "leader": ["Clarke (Waterloo)", "Gouglas (Alberta)"],
   "children": [
    {"name": "DIGHUM", "color": "#02a6ea", "leader": ["Geoffrey Rockwell", "Michael Sinatra"], "children": [
        {"name": "BigLit", "color": "#02a6ea"},
        {"name": "BigViz", "color": "#02a6ea"},
        {"name": "DigiCultH", "color": "#02a6ea"},
        {"name": "InfraDH", "color": "#02a6ea"},
        {"name": "ScholEd", "color": "#02a6ea"},
        {"name": "TroFish", "color": "#02a6ea"}
    ]},
    {"name": "PLATFORM2", "color": "#02a6ea", "leader": ["Martin Müller", "Alexandra Fedorova"], "children": [
        {"name": "CONFIG", "color": "#02a6ea"},
        {"name": "LSHP", "color": "#02a6ea"},
        {"name": "PARALLEL", "color": "#02a6ea"},
        {"name": "PIPES", "color": "#02a6ea"},
        {"name": "SYSTEM", "color": "#02a6ea"}
    ]},
    {"name": "SENSE-I", "color": "#02a6ea", "leader": ["Wolfgang Stuerzlinger", "Charles Clarke"], "children": [
        {"name": "EXPLAIN", "color": "#02a6ea"},
        {"name": "INTERALTER", "color": "#02a6ea"},
        {"name": "LEARNSOCIAL", "color": "#02a6ea"},
        {"name": "MUBE", "color": "#02a6ea"},
        {"name": "OPENGOV", "color": "#02a6ea"},
        {"name": "UBILYTICS", "color": "#02a6ea"}
    ]},
    {"name": "AVID", "color": "#02a6ea", "leader": ["Sheelagh Carpendale", "Christopher Collins"], "children": [
        {"name": "Applied", "color": "#02a6ea"},
        {"name": "Borrowed", "color": "#02a6ea"},
        {"name": "Critique", "color": "#02a6ea"},
        {"name": "Interact", "color": "#02a6ea"},
        {"name": "Massive", "color": "#02a6ea"},
        {"name": "Personal", "color": "#02a6ea"}
    ]}
   ]
  },
  {
   "name": "Citizenship",
   "image": "{$wgServer}{$wgScriptPath}/extensions/Visualisations/Cluster/images/citizenship.png",
   "text": "below",
   "leader": ["Trowsow (Western)", "Middleton (Ryerson)"],
   "color": "#E6B507",
   "children": [
    {"name": "NEWS2", "color": "#E6B507", "leader": ["Jacqelyn Burkell", "Luanne Freund"], "children": [
        {"name": "LocNews", "color": "#E6B507"},
        {"name": "NewsAPPP", "color": "#E6B507"},
        {"name": "NEWSLegPol", "color": "#E6B507"},
        {"name": "NewsMine", "color": "#E6B507"},
        {"name": "NewsWorld", "color": "#E6B507"},
        {"name": "PauseButton", "color": "#E6B507"},
        {"name": "Reconfig-J", "color": "#E6B507"},
        {"name": "SocNews", "color": "#E6B507"}
    ]},
    {"name": "PROTECT", "color": "#E6B507", "leader": ["Robert Biddle", "Catherine Middleton"], "children": [
        {"name": "ACCESS", "color": "#E6B507"},
        {"name": "DRTRUST", "color": "#E6B507"},
        {"name": "NIND", "color": "#E6B507"},
        {"name": "PPDINFRA", "color": "#E6B507"},
        {"name": "PSAWARE", "color": "#E6B507"},
        {"name": "PSONLINE", "color": "#E6B507"}
    ]}
   ]
  },
  {
   "name": "Entertainment",
   "image": "{$wgServer}{$wgScriptPath}/extensions/Visualisations/Cluster/images/entertainment.png",
   "text": "below",
   "leader": ["Poulin (Montreal)", "Mandryk (Saskatchewan)"],
   "color": "#692d97",
   "children": [
    {"name": "BELIEVE2", "color": "#692d97", "leader": ["Duane Szafron", "David Mould"], "children": [
        {"name": "BAVC", "color": "#692d97"},
        {"name": "CHARANIM", "color": "#692d97"},
        {"name": "EMERGESTORY", "color": "#692d97"},
        {"name": "EMOCHAR", "color": "#692d97"},
        {"name": "IDEATION", "color": "#692d97"},
        {"name": "SIMMOTOR", "color": "#692d97"}
    ]},
    {"name": "CREATE", "color": "#692d97", "leader": ["Faramarz Samavati", "Karan Singh"], "children": [
        {"name": "Environment-based", "color": "#692d97"},
        {"name": "Interaction", "color": "#692d97"},
        {"name": "Medical", "color": "#692d97"},
        {"name": "Modeling&Animation", "color": "#692d97"},
        {"name": "Performance", "color": "#692d97"},
        {"name": "Physical", "color": "#692d97"}
    ]},
    {"name": "DATUM", "color": "#692d97", "leader": ["Richard Zhang", "Pierre Poulin"], "children": [
        {"name": "2D3D", "color": "#692d97"},
        {"name": "COLT", "color": "#692d97"},
        {"name": "DATA", "color": "#692d97"},
        {"name": "INVERSE", "color": "#692d97"},
        {"name": "NATURAL", "color": "#692d97"},
        {"name": "TEMPORAL", "color": "#692d97"}
    ]}
   ]
  },
  {
   "name": "Health",
   "image": "{$wgServer}{$wgScriptPath}/extensions/Visualisations/Cluster/images/health.png",
   "text": "below",
   "color": "#ee1e23",
   "leader": ["Gromala (SFU)", "Graham (Queens)"],
   "children": [
    {"name": "CHRONIC", "color": "#ee1e23", "leader": ["Chris Shaw", "Linda Li"], "children": [
        {"name": "B-AWARE", "color": "#ee1e23"},
        {"name": "DECIDE", "color": "#ee1e23"},
        {"name": "OneSpace", "color": "#ee1e23"},
        {"name": "PersonData", "color": "#ee1e23"},
        {"name": "RELATE", "color": "#ee1e23"},
        {"name": "VR", "color": "#ee1e23"}
    ]},
    {"name": "G4HLTH", "color": "#ee1e23", "leader": ["Nicholas Graham", "Kevin Stanley"], "children": [
        {"name": "CLAP", "color": "#ee1e23"},
        {"name": "COAP", "color": "#ee1e23"},
        {"name": "EVALU8", "color": "#ee1e23"},
        {"name": "IN2GAME", "color": "#ee1e23"},
        {"name": "MOTIV8", "color": "#ee1e23"},
        {"name": "SENSE", "color": "#ee1e23"}
    ]},
    {"name": "HLTHSIM2", "color": "#ee1e23", "leader": ["Roy Eagleson", "Bill Kapralos"], "children": [
        {"name": "ARVRDISPLAY", "color": "#ee1e23"},
        {"name": "GAMES", "color": "#ee1e23"},
        {"name": "MODELING", "color": "#ee1e23"},
        {"name": "SCENARIO", "color": "#ee1e23"},
        {"name": "SIMDEV", "color": "#ee1e23"},
        {"name": "VIZPATIENT", "color": "#ee1e23"}
    ]},
    {"name": "INCLUDE2", "color": "#ee1e23", "leader": ["Deborah Fels", "McGrenere Joanna"], "children": [
        {"name": "COG1", "color": "#ee1e23"},
        {"name": "COMM1", "color": "#ee1e23"},
        {"name": "CREATE1", "color": "#ee1e23"},
        {"name": "SENS-MOT", "color": "#ee1e23"},
        {"name": "SOCIAL1", "color": "#ee1e23"}
    ]}
   ]
  },
  {
   "name": "Learning",
   "image": "{$wgServer}{$wgScriptPath}/extensions/Visualisations/Cluster/images/learning.png",
   "text": "below",
   "color": "#f68312",
   "leader": ["Jenson (York)", "Gutwin (Saskatchewan)"],
   "children": [
    {"name": "CONNECT", "color": "#f68312", "leader": ["Regan Mandryk", "Carman Neustaedter"], "children": [
        {"name": "EVALUATE", "color": "#f68312"},
        {"name": "FOSTER", "color": "#f68312"},
        {"name": "GAMIFY", "color": "#f68312"},
        {"name": "MATCH", "color": "#f68312"},
        {"name": "SCAFFOLD", "color": "#f68312"},
        {"name": "SOCIALIZE", "color": "#f68312"}
    ]},
    {"name": "ENGAGE", "color": "#f68312", "leader": ["Jennifer Jenson", "Sean Gouglas"], "children": [
        {"name": "Borders", "color": "#f68312"},
        {"name": "LANGLRN", "color": "#f68312"},
        {"name": "LevelUp", "color": "#f68312"},
        {"name": "MOOC", "color": "#f68312"},
        {"name": "p2P", "color": "#f68312"},
        {"name": "PLAY", "color": "#f68312"},
        {"name": "RLIC", "color": "#f68312"}
    ]},
    {"name": "KIDZ", "color": "#f68312", "leader": ["Alissa Antle", "Karon MacLean"], "children": [
        {"name": "Cog1-Games", "color": "#f68312"},
        {"name": "Cog2-Personalize", "color": "#f68312"},
        {"name": "Cult1-Connect", "color": "#f68312"},
        {"name": "Cult2-Media", "color": "#f68312"},
        {"name": "SocEm1-SelfRegulation", "color": "#f68312"},
        {"name": "SocEm2-Autism", "color": "#f68312"}
    ]}
   ]
  },
  {
   "name": "Sustainability",
   "image": "{$wgServer}{$wgScriptPath}/extensions/Visualisations/Cluster/images/sustainability.png",
   "text": "below",
   "color": "#00a550",
   "leader": ["Bartram (SFU)", "Woodbury (SFU)"],
   "children": [
    {"name": "IIDEMS", "color": "#00a550", "leader": ["Ronald Kellett", "Robert Woodbury"], "children": [
        {"name": "CASE", "color": "#00a550"},
        {"name": "DMBIM", "color": "#00a550"},
        {"name": "DMUDC", "color": "#00a550"},
        {"name": "DREV", "color": "#00a550"},
        {"name": "ISAIRA", "color": "#00a550"},
        {"name": "MLDSDUE", "color": "#00a550"},
        {"name": "TCDNLA", "color": "#00a550"}
    ]},
    {"name": "NMSL", "color": "#00a550", "leader": ["Lyn Bartram", "Melanie Tory"], "children": [
        {"name": "COMMUNICATE", "color": "#00a550"},
        {"name": "CONTROL", "color": "#00a550"},
        {"name": "HOMEINTERACTION", "color": "#00a550"},
        {"name": "MOTIVATE", "color": "#00a550"},
        {"name": "PICS-GRAND", "color": "#00a550"},
        {"name": "SIW", "color": "#00a550"}
    ]}
   ]
  },
  {
   "name": "Work",
   "image": "{$wgServer}{$wgScriptPath}/extensions/Visualisations/Cluster/images/work.png",
   "text": "below",
   "color": "#234f98",
   "leader": ["Simon (Concordia)", "Cooperstock (McGill)"],
   "children": [
    {"name": "EXPERT", "color": "#234f98", "leader": ["Gerald Penn", "Michael Terry"], "children": [
        {"name": "CROWDDOM", "color": "#234f98"},
        {"name": "LRNOBSASSIST", "color": "#234f98"},
        {"name": "NETCOORD", "color": "#234f98"},
        {"name": "REFLECT", "color": "#234f98"},
        {"name": "SITADAPT", "color": "#234f98"},
        {"name": "SITUAWARE", "color": "#234f98"}
    ]},
    {"name": "INDIEGAME", "color": "#234f98", "leader": ["Lynn Hughes", "Bart Simon", "Brian Greenspan"], "children": [
        {"name": "ART-INDIE", "color": "#234f98"},
        {"name": "CULTECONEMY-INDIE", "color": "#234f98"},
        {"name": "INCLUDSION-INDIE", "color": "#234f98"},
        {"name": "INCUBATE-INDIE", "color": "#234f98"},
        {"name": "LOCATIVE-INDIE", "color": "#234f98"},
        {"name": "ORGANIZATION-INDIE", "color": "#234f98"}
    ]},
    {"name": "KNOW", "color": "#234f98", "leader": ["Eleni Stroulia", "Anatoliy Gruzd"], "children": [
        {"name": "ACRI", "color": "#234f98"},
        {"name": "CoDYn", "color": "#234f98"},
        {"name": "DaD", "color": "#234f98"},
        {"name": "ENOW", "color": "#234f98"},
        {"name": "FiRE", "color": "#234f98"},
        {"name": "Forum", "color": "#234f98"},
        {"name": "HH", "color": "#234f98"},
        {"name": "MVW", "color": "#234f98"},
        {"name": "SNETS", "color": "#234f98"}
    ]},
    {"name": "SHREXP", "color": "#234f98", "leader": ["Jeremy Cooperstock", "Tony Tang"], "children": [
        {"name": "CDPS", "color": "#234f98"},
        {"name": "Crowdsourcing", "color": "#234f98"},
        {"name": "Flex3D", "color": "#234f98"},
        {"name": "Sensemaking", "color": "#234f98"},
        {"name": "SharedCreative", "color": "#234f98"},
        {"name": "Tangible", "color": "#234f98"},
        {"name": "VidExp", "color": "#234f98"}
    ]}
   ]
  },
  {
   "name": "Strategic",
   "color": "#555555",
   "children": [
    {"name": "SYNTHIUS", "color": "#555555", "leader": ["Sidney Fels", "Ian Stavness"], "children": [
        {"name": "BEHV", "color": "#555555"},
        {"name": "BIO", "color": "#555555"},
        {"name": "COG", "color": "#555555"},
        {"name": "EXP", "color": "#555555"},
        {"name": "PHYS", "color": "#555555"},
        {"name": "SOC", "color": "#555555"},
        {"name": "SYN", "color": "#555555"}
    ]}
   ]
  }
 ]
}
EOF;
            exit;
        }
        return true;
	}
}
?>
