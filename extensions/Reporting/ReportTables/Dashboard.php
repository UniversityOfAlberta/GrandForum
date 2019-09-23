<?php

function compareLastNames($a, $b){
    $aname = preg_split('/\./', $a->getName(), 2);
    $alname = (isset($aname[1]))? $aname[1] : "";

    $bname = preg_split('/\./', $b->getName(), 2);
    $blname = (isset($bname[1]))? $bname[1] : "";

    return strcmp($alname, $blname);
}

class Dashboard{

	//Static helper functions

	static function hqpDetails($hqps){   
        global $wgServer, $wgScriptPath;
        $url_prefix = "$wgServer$wgScriptPath/index.php/";
        
        $hqp_type_map = array(
            "Masters Student" => "MSc",
            "PhD Student" => "PhD",
            "Undergraduate" => "Ugrad"
        );
        
        usort($hqps, "compareLastNames");
        $html = "";    
        foreach($hqps as $h){
            $hqp_name = $h->getName();
            $hqp_name_read = preg_split('/\./', $hqp_name, 2);
            $hqp_name_read = @$hqp_name_read[1].", ".@$hqp_name_read[0];
            
            $hqp_university = $h->getUniversity(); 
            $hqp_uni = ($hqp_university['university'])? ", ".$hqp_university['university'] : "";
            $hqp_type = $hqp_university['position'];
            if (empty($hqp_type)){
                $hqp_type = ", Unknown";
            }    
            else if(isset($hqp_type_map[$hqp_type])){
                $hqp_type = ", ".$hqp_type_map[$hqp_type];
            }else{
                 $hqp_type = ", ".$hqp_type;
            } 
            
            $role = ($h->isRole(HQP))? "HQP" : (($h->isRole(NI))? "NI" : "");
            if($h->isActive() && $role != ""){
                $html.=<<<EOF
                    <li>
                    <a target='_blank' href='{$h->getUrl()}'>
                    $hqp_name_read</a>{$hqp_uni}{$hqp_type}
                    </li>
EOF;
            }
            else{
                $html.=<<<EOF
                    <li>
                    $hqp_name_read (Inactive)
                    </li>
EOF;
            }     
        }
        return $html; 
    }

    static function niDetails($hqps, $year=YEAR){   
        global $wgServer, $wgScriptPath, $config;
        $url_prefix = "$wgServer$wgScriptPath/index.php/";
        
        usort($hqps, "compareLastNames");
        $html = "";    
        foreach($hqps as $h){
            $hqp_name = $h->getName();
            $hqp_name_read = preg_split('/\./', $hqp_name, 2);
            $hqp_name_read = @$hqp_name_read[1].", ".@$hqp_name_read[0];
            
            $hqp_university = $h->getUniversity(); 
            $hqp_uni = ($hqp_university['university'])? ", ".$hqp_university['university'] : "";
            $hqp_type = $hqp_university['position'];
            if (empty($hqp_type)){
                $hqp_type = ", Unknown";
            }    
            else{
                 $hqp_type = ", ".$hqp_type;
            } 

            $role = ($h->isRole(HQP))? "HQP" : (($h->isRole(NI))? "NI" : "");
            if($h->isActive() && $role != ""){
                $html.=<<<EOF
                    <li>
                    <a target='_blank' href='{$h->getUrl()}'>
                    $hqp_name_read</a>, {$role}{$hqp_uni}{$hqp_type}
                    </li>
EOF;
            }
            else{
                $html.=<<<EOF
                    <li>
                    $hqp_name_read (Inactive)
                    </li>
EOF;
            }     
        }
        return $html; 
    }
    
    static function paperDetails($papers){
        global $wgServer, $wgScriptPath;
        
        $html = "";
        if(is_array($papers)){
            foreach($papers as $p){
                if($p instanceof Paper){
                    $pap_data = $p->getData();
                    $event_title = "";
                    if(isset($pap_data['event_title'])){
                        $event_title = "; Event Title: ".$pap_data['event_title'];
                    }else if(isset($pap_data['book_title'])){
                        $event_title = "; Book Title: ".$pap_data['book_title'];
                    }else if(isset($pap_data['journal_title'])){
                        $event_title = "; Journal Title: ".$pap_data['journal_title'];
                    }
                    
                    $pap_authors = $p->getAuthors();    
                    $author_arr = array();    
                    foreach ($pap_authors as $pap_auth){
                        $author_arr[] = $pap_auth->getNameForForms();
                    }
                    $author_str = implode(',', $author_arr);
                    
                    $pap_title = $p->getTitle();
                    $pap_status = $p->getStatus();
                    $html.= "EOF
                        <li>
                        <a target='_blank' href='{$p->getUrl()}'>
                        $pap_title
                        </a>$event_title; $pap_status; Authors: $author_str
                        </li>";
                }
            }
        }
        return $html;
    }
}
	
?>
