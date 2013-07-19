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

        $map_file = getcwd()."/Products.xml";
        $ccv_tmpl = getcwd()."/ccv_template.xml";
        $map = simplexml_load_file($map_file);

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
                $res = mapItem($map->Publications->Publication, $product, $ccv->section->section[0]);

                // if($res == 0){
                //     echo "NOT EXPORTED========". $product->getType() ."  ||||  ". $product->getId() ."\n";
                // }else{
                //     echo ":-) EXPORTED========". $product->getType() ."  ||||  ". $product->getId() ."\n";
                // }
                $counter += $res;
                
            //}
            }
        }


        return $ccv->asXML();
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

                //ADD Status = Publishing Status
                $status = $product->getStatus();
                if($item->statuses){
                    foreach($item->statuses->status as $s){
                        if($s == $status && isset($s['lov_id']) && isset($s['lov_name'])){
                            $status_field = $ccv_item->addChild("field");
                            $status_field->addAttribute('id', $item->statuses['ccv_id']);
                            $status_field->addAttribute('label', $item->statuses['ccv_name']);
                            
                            $lov = $status_field->addChild('lov');
                            $lov->addAttribute('id', $s['lov_id']);
                            $status_field->lov = $s['lov_name'];
                        }
                    }
                }

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