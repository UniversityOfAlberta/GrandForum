<?php
    require_once( "commandLine.inc" );
    $wgUser=User::newFromId("1");
    for($num=0;$num<=26;$num++){
        if(file_exists("grant_awards/partner".$num.".csv")){
            $handle = fopen("grant_awards/partner".$num.".csv", "r");
            echo "\nImporting Partners{$num}.csv\n";
            $iterationsSoFar = 0;
            $lines = array();
            while (($data = fgetcsv($handle, 0, ",")) !== false) {
                $lines[] = $data;
            }
            fclose($handle);
            foreach($lines as $i => $cells){
                if($i > 0 && count($cells)>1){
                    foreach($cells as $key => $cell){
                        $cells[$key] = utf8_encode($cell);
                    }
                    $cle = $cells[0];
                    $datacheck = DBFunctions::select(array('grand_new_grants'),
                                                     array('*'),
                                                     array('cle' => $cle));
                    if(count($datacheck)>0){
                        $part_organization_id = str_replace("'", "''",$cells[1]);
                        $part_institution = str_replace("'", "''",$cells[2]);
                        $province = str_replace("'", "''",$cells[3]);
                        $country = str_replace("'", "''",$cells[5]);
                        $fiscal_year = $cells[7].'-01-01 00:00:00';
                        $org_type = str_replace("'", "''",$cells[8]);
                        $committee_name = "";
                        $status = DBFunctions::insert('grand_new_grant_partner',
                                                      array('award_id' => $datacheck[0]['id'],
                                                            'part_institution' => $part_institution,
                                                            'province' => $province,
                                                            'country' => $country,
                                                            'committee_name' => $committee_name,
                                                            'fiscal_year' => $fiscal_year,
                                                            'org_type' => $org_type));
                        if($status){
                            DBFunctions::commit();
                        }
                    }
                    show_status(++$iterationsSoFar, count($lines)-1);
                }
            }
        }
    }
?>
