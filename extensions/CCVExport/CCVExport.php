<?php
$dir = dirname(__FILE__) . '/';

$wgHooks['UnknownAction'][] = 'getack';

$wgSpecialPages['CCVExport'] = 'CCVExport';
$wgExtensionMessagesFiles['CCVExport'] = $dir . 'CCVExport.i18n.php';
$wgSpecialPageGroups['CCVExport'] = 'grand-tools';

function runCCVExport($par) {
	CCVExport::run($par);
}


class CCVExport extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('CCVExport');
		SpecialPage::SpecialPage("CCVExport", HQP.'+', true, 'runCCVExport');
	}
	
	static function run(){
	    global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgMessage;
	   
	    if(isset($_GET['getXML'])){
            $table_type = $_GET['getXML'];
            $xml = "";
            //if($table_type == "HQP"){
                $xml = CCVExport::exportXML();
            //}else if($table_type == "NI"){
            //    $excel = EthicsTable::niTable();
            //}
            $wgOut->disable();
            ob_clean();

            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");;
            header("Content-Disposition: attachment;filename=export.xml"); 
            header("Content-Transfer-Encoding: binary ");
            echo $xml;
            exit;
        }
	    
	    $wgOut->setPageTitle("Export To CCV");
	  
	    // $wgOut->addScript("<script type='text/javascript'>
     //                            $(document).ready(function(){
	    //                             $('.indexTable').dataTable({'iDisplayLength': 100,
	    //                                                         'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']]});
     //                                $('.dataTables_filter input').css('width', 250);
     //                                $('#ackTabs').tabs();
                                    
     //                                $('input[name=date]').datepicker();
     //                                $('input[name=date]').datepicker('option', 'dateFormat', 'dd-mm-yy');
     //                            });
     //                        </script>");

        $wgOut->addHTML("<p><a target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:CCVExport?getXML'>[Download XML]</a></p>");

    }
    
   

    static function exportXML(){
        global $wgOut, $wgUser;

        $map_file = getcwd()."/extensions/CCVExport/Products.xml";
        $hqp_file = getcwd()."/extensions/CCVExport/HQP.xml";
        $ccv_tmpl = getcwd()."/extensions/CCVExport/ccv_template.xml";
        $map = simplexml_load_file($map_file);
        $hqp_map = simplexml_load_file($hqp_file);

        $ccv = simplexml_load_file($ccv_tmpl);

        $person = Person::newFromId($wgUser->getId());

        $all_products = $person->getPapers("Publication");

        $prod_sorted = array();

        foreach($all_products as $p){
            $t = $p->getType();
            if(isset($prod_sorted[$t])){
                $prod_sorted[$t][] = $p;
            }
            else{
                $prod_sorted[$t] = array();
                $prod_sorted[$t][] = $p;
            }
        }

        $counter = 0;
        foreach($prod_sorted as $type => $products){
            foreach($products as $product){
        //  $type = $product->getType();
            //if($type == "Review Article" || $type == "Book Review"){
                //$ccv_pub = $ccv->section->section[0]->addChild("section");
                $res = CCVExport::mapItem($map->Publications->Publication, $product, $ccv->section->section[1]);

                // if($res == 0){
                //     echo "NOT EXPORTED========". $product->getType() ."  ||||  ". $product->getId() ."\n";
                // }else{
                //     echo ":-) EXPORTED========". $product->getType() ."  ||||  ". $product->getId() ."\n";
                // }
                $counter += $res;
                
            //}
            }
        }


        $rels = $person->getRelations('Supervises', true);
        foreach($rels as $rel){

            $res = CCVExport::mapHQP($hqp_map->HQP->data, $rel, $ccv->section->section[0]);

        }

        return $ccv->asXML();
    }

    static function mapHQP($section, $rel, $ccv){
        global $wgUser;
        $person = Person::newFromId($wgUser->getId());

        $hqp = $rel->getUser2();

        $success = 0;
       
        $ccv_item = $ccv->addChild("section");
        $ccv_item->addAttribute('id', $section['lov_id']);
        $ccv_item->addAttribute('label', $section['lov_name']);
       
        foreach($section->field as $item){
               
            $item_id = $item['lov_id'];
            $item_name = $item['lov_name'];

            //echo $item_name ."<br>";

            // $ccv_item = $new_section->addChild("section");
            // $ccv_item->addAttribute('id', $item_id);
            // $ccv_item->addAttribute('label', $item_name);
           
            if($item_name == "Supervision Role"){
                $field = $ccv_item->addChild("field");
                $field->addAttribute('id', $item_id);
                $field->addAttribute('label', $item_name);
                $lov = $field->addChild('lov');
                $lov->addAttribute('id', '00000000000000000000000100002900');
                $field->lov = "Principal Supervisor";
            }
            else if($item_name == "Supervision Start Date"){
                $field = $ccv_item->addChild("field");
                $field->addAttribute('id', $item_id);
                $field->addAttribute('label', $item_name);
                $val = $field->addChild('value');
                $val->addAttribute('type', "YearMonth");
                $val->addAttribute('format', "yyyy/MM");
                $start_date = preg_split('/\-/', $rel->getStartDate());
                $field->value = $start_date[0].'/'.$start_date[1];
            }
            else if($item_name == "Supervision End Date"){
                $field = $ccv_item->addChild("field");
                $field->addAttribute('id', $item_id);
                $field->addAttribute('label', $item_name);
                $val = $field->addChild('value');
                $val->addAttribute('type', "YearMonth");
                $val->addAttribute('format', "yyyy/MM");
                $end_date = preg_split('/\-/', $rel->getEndDate());
                $field->value = $end_date[0].'/'.$end_date[1];
            }
            else if($item_name == "Supervision End Date"){
                $field = $ccv_item->addChild("field");
                $field->addAttribute('id', $item_id);
                $field->addAttribute('label', $item_name);
                $val = $field->addChild('value');
                $val->addAttribute('type', "YearMonth");
                $val->addAttribute('format', "yyyy/MM");
                $end_date = preg_split('/\-/', $rel->getEndDate());
                $field->value = $end_date[0].'/'.$end_date[1];
            }
            else if($item_name == "Student Name"){
                $field = $ccv_item->addChild("field");
                $field->addAttribute('id', $item_id);
                $field->addAttribute('label', $item_name);
                $val = $field->addChild('value');
                $val->addAttribute('type', "String");
                
                $hqp_name = $hqp->getNameForForms();
                $field->value = $hqp_name;
            }
            else if($item_name == "Student Institution"){
                $field = $ccv_item->addChild("field");
                $field->addAttribute('id', $item_id);
                $field->addAttribute('label', $item_name);
                $val = $field->addChild('value');
                $val->addAttribute('type', "String");
                
                $hqp_uni = $hqp->getUni();
                $field->value = $hqp_uni;
            }
            else if($item_name == "Student Canadian Residency Status"){
                
                $status_map = array('Canadian'=>array("00000000000000000000000000000034","Canadian Citizen"),
                                    'Landed Immigrant'=>array("00000000000000000000000000000035","Permanent Resident"),
                                    'Foreign'=>array("00000000000000000000000000000040","Study Permit"),
                                    'Visa Holder'=>array("00000000000000000000000000000040","Study Permit"));

                $field = $ccv_item->addChild("field");
                $field->addAttribute('id', $item_id);
                $field->addAttribute('label', $item_name);
                $val = $field->addChild('lov');
               
                
                $hqp_status = $hqp->getNationality();
                if(!empty($hqp_status) && isset($status_map[$hqp_status])){
                    $lov_id = $status_map[$hqp_status][0];
                    $val->addAttribute('id', $lov_id);
                    $field->lov = $status_map[$hqp_status][1];
                }
            }
            else if($item_name == "Study / Postdoctoral Level"){
                
                $degree_map = array('Masters Student'=>array("00000000000000000000000000000072","Master's Thesis"),
                                    'PhD Student'=>array("00000000000000000000000000000073","Doctorate"),
                                    'Undergraduate'=>array("00000000000000000000000000000071","Bachelor's"),
                                    'PostDoc'=>array("00000000000000000000000000000074","Post-doctorate"));

                $field = $ccv_item->addChild("field");
                $field->addAttribute('id', $item_id);
                $field->addAttribute('label', $item_name);
                $val = $field->addChild('lov');
               
                
                $hqp_pos = $hqp->getPosition();
                if(!empty($hqp_pos) && isset($degree_map[$hqp_pos])){
                    $lov_id = $degree_map[$hqp_pos][0];
                    $val->addAttribute('id', $lov_id);
                    $field->lov = $degree_map[$hqp_pos][1];
                }
            }
            else if($item_name == "Student Degree Status"){
                
              
            }
            else if($item_name == "Student Degree Start Date"){
                
                
            }
            else if($item_name == "Student Degree Received Date"){
                
                
            }
            else if($item_name == "Student Degree Expected Date"){
                
                
            }
            else if($item_name == "Thesis/Project Title"){
                $field = $ccv_item->addChild("field");
                $field->addAttribute('id', $item_id);
                $field->addAttribute('label', $item_name);
                $val = $field->addChild('value');
                $val->addAttribute('type', "String");
                
                $hqp_thesis = $hqp->getThesis();
                if(!is_null($hqp_thesis)){
                    $field->value = $hqp_thesis->getTitle(); 
                }  
            }
            else if($item_name == "Project Description"){
                $field = $ccv_item->addChild("field");
                $field->addAttribute('id', $item_id);
                $field->addAttribute('label', $item_name);
                $val = $field->addChild('value');
                $val->addAttribute('type', "Bilingual");
                $bilin = $field->addChild("bilingual");
                $bilin->addChild("english");
                
                $hqp_proj = $hqp->getThesis();
                if(!is_null($hqp_proj)){
                    $bilin->english = $hqp_proj->getTitle(); 
                }  
            }
            else if($item_name == "Present Position"){
                $field = $ccv_item->addChild("field");
                $field->addAttribute('id', $item_id);
                $field->addAttribute('label', $item_name);
                $val = $field->addChild('value');
                $val->addAttribute('type', "String");
                
                $hqp_pos = $hqp->getPosition();
                if(!empty($hqp_pos)){
                    $field->value = $hqp_pos;
                }
            }

            $success = 1;
            
        }

        return $success;
        
    }


    static function mapItem($section, $product, $ccv){
        global $wgUser;
        $person = Person::newFromId($wgUser->getId());

        $type = $product->getType();
        $success = 0;
        foreach($section as $item){
            if(
                (($type == "Masters Thesis" || $type == "PHD Thesis") && ($type == $item['type']) && $person->isAuthorOf($product) 
                && isset($item['supervised']) && $item['supervised']=="false" 
                && isset($item['ccv_id']) && isset($item['ccv_name'])) 
                || 
                (($type == "Masters Thesis" || $type == "PHD Thesis") && ($type == $item['type']) && !$person->isAuthorOf($product) 
                && isset($item['supervised']) && $item['supervised']=="true" 
                && isset($item['ccv_id']) && isset($item['ccv_name']))
                ||
                (($type != "Masters Thesis" && $type != "PHD Thesis") && ($type == $item['type'])
                && isset($item['ccv_id']) && isset($item['ccv_name']))
            ){ 
                //if(($type == $item['type']) && isset($item['ccv_id']) && isset($item['ccv_name'])){
                $ccv_item = $ccv->addChild("section");
                $ccv_id = $item['ccv_id'];
                $ccv_name = $item['ccv_name'];

                $ccv_item->addAttribute('id', $ccv_id);
                $ccv_item->addAttribute('label', $ccv_name);
                //$ccv_item->addAttribute('recordId', "a8c67f9d407c4eda9cc7818ab89fa1ba");

                //Title
                $title = $product->getTitle();
                $status_field = $ccv_item->addChild("field");
                $status_field->addAttribute('id', $item->title['ccv_id']);
                $status_field->addAttribute('label', $item->title['ccv_name']);
                $val = $status_field->addChild('value');
                $val->addAttribute('type', "String");
                $status_field->value = $title;

                

                //Add Fields
                $product_data = $product->getData();
                foreach($item->data->field as $field){
                    if(isset($field['ccv_id'])){
                        $status_field = $ccv_item->addChild("field");
                        $status_field->addAttribute('id', $field['ccv_id']);
                        $status_field->addAttribute('label', $field['ccv_name']);

                        $val = $status_field->addChild('value');
                        $val->addAttribute('type', "String");
                        $key = (string) $field;
                        //echo $product_data[$key] ."\n";
                        $status_field->value = (isset($product_data[$key]))? $product_data[$key] : "";
                    }
                }

                //Date
                $status_field = $ccv_item->addChild("field");
                $status_field->addAttribute('id', $item->date['ccv_id']);
                $status_field->addAttribute('label', $item->date['ccv_name']);
                $val = $status_field->addChild('value');
                $val->addAttribute('type', "YearMonth");
                $val->addAttribute('format', "yyyy/MM");
                $product_date = preg_split('/\-/', $product->getDate());
                $status_field->value = $product_date[0].'/'.$product_date[1];
                
                //Authors
                $status_field = $ccv_item->addChild("field");
                $status_field->addAttribute('id', $item->authors['ccv_id']);
                $status_field->addAttribute('label', $item->authors['ccv_name']);
                $val = $status_field->addChild('value');
                $val->addAttribute('type', "String");
                
                $product_authors = $product->getAuthors();
                $auth_arr = array();
                foreach($product_authors as $a){
                    $auth_arr[] = $a->getNameForForms();
                }
                $status_field->value = implode(', ', $auth_arr);

                // //Description
                $status_field = $ccv_item->addChild("field");
                $status_field->addAttribute('id', $item->description['ccv_id']);
                $status_field->addAttribute('label', $item->description['ccv_name']);
                $val = $status_field->addChild('value');
                $val->addAttribute('type', "Bilingual");
                $bilin = $status_field->addChild("bilingual");
                $bilin->addChild("english");
                $bilin->english = substr($product->getDescription(), 0, 1000);

                $success = 1;
            }
        }

        return $success;
        
    }

}

?>