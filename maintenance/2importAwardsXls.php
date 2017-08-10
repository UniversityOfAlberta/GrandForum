<?php
    require_once( "commandLine.inc" );
    $wgUser=User::newFromId("1");
    for($num=1;$num<=25;$num++){
        if(file_exists("grant_awards/award".$num.".csv")){
            $handle = fopen("grant_awards/award".$num.".csv", "r");
            echo "\nImporting award{$num}.csv\n";
            $iterationsSoFar = 0;
            $lines = array();
            while (($data = fgetcsv($handle, 0, ",")) !== false) {
                $lines[] = $data;
            }
            fclose($handle);
            $offset = 0;
            foreach($lines as $i => $cells){
                if($i == 0 && array_search("Num_Partie", $cells) !== false){
                    $offset++;
                }
                if($i > 0 && count($cells)>1){
                    foreach($cells as $key => $cell){
                        $cells[$key] = utf8_encode($cell);
                    }
                    $fullname = $cells[1];
                    $name_array = explode(",", $fullname);
                    $first_name = @$name_array[1];
                    $last_name = @$name_array[0];
                    $person = Person::newFromNameLike($first_name." ".$last_name);
                    if($person->getId() != ""){
                        $user_id = $person->getId();
                        $cle = $cells[0];
                        $department = str_replace("'", "''",$cells[2]);
                        $institution = str_replace("'", "''",$cells[4]);
                        $province = str_replace("'", "''",$cells[5]);
                        $country = str_replace("'", "''",$cells[7]);
                        $fiscal_year = $cells[9];
                        $competition_year = $cells[10];
                        $amount = $cells[11];
                        $program_id = $cells[12];
                        $program_name = str_replace("'", "''",$cells[13]);
                        $group = str_replace("'", "''",$cells[15]);
                        $committee_name = str_replace("'", "''",$cells[18]);
                        $area_of_application_group = str_replace("'", "''",$cells[21]);
                        $area_of_application = str_replace("'", "''",$cells[23]);
                        $research_subject_group = str_replace("'", "''",$cells[26]);
                        $installment_date = strtotime($cells[30]."-".$cells[9]);
                        $installment = date('Y-m-d', $installment_date);
                        if($cells[30] == ""){
                            $installment = "0000-00-00 00:00:00";
                        }
                        $partie = $cells[31];
                        if($cells[31] == ""){
                            $partie = 0;
                        }
                        $nb_partie = $cells[32+$offset];
                        if($cells[31] == ""){
                            $nb_partie = 0;
                        }
                        $application_title = str_replace("'", "''",$cells[33+$offset]);
                        $keyword = str_replace("'", "''",$cells[34+$offset]);
                        if($cells[34+$offset] == ""){
                            $keyword = '';
                        }
                        $application_summary = str_replace("'", "''",$cells[35+$offset]);

                        $status = DBFunctions::insert('grand_new_grants',
                                                      array('user_id' => $user_id,
                                                            'cle' => $cle,
                                                            'department' => $department,
                                                            'institution' => $institution,
                                                            'province' => $province,
                                                            'country' => $country,
                                                            'fiscal_year' => $fiscal_year,
                                                            'competition_year' => $competition_year,
                                                            'amount' => $amount,
                                                            'program_id' => $program_id,
                                                            'program_name' => $program_name,
                                                            '`group`' => $group,
                                                            'committee_name' => $committee_name,
                                                            'area_of_application_group' => $area_of_application_group,
                                                            'area_of_application' => $area_of_application,
                                                            'research_subject_group' => $research_subject_group,
                                                            'installment' => $installment,
                                                            'partie' => $partie,
                                                            'nb_partie' => $nb_partie,
                                                            'application_title' => $application_title,
                                                            'keyword' => $keyword,
                                                            'application_summary' => $application_summary));
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
