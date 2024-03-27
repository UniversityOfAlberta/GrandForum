<?php
    require_once( "commandLine.inc" );
    $wgUser=User::newFromId("1");
    for($num=1;$num<=26;$num++){
        if(file_exists("grant_awards/award".$num.".csv")){
            $handle = fopen("grant_awards/award".$num.".csv", "r");
            $handle2 = fopen("grant_awards/co".$num.".csv", "r");
            $handle3 = fopen("grant_awards/partner".$num.".csv", "r");
            echo "\nImporting award{$num}.csv\n";
            $iterationsSoFar = 0;
            $lines = array();
            $lines2 = array();
            $lines3 = array();
            while (($data = fgetcsv($handle, 0, ",")) !== false) {
                $lines[] = $data;
            }
            while (($data2 = fgetcsv($handle2, 0, ",")) !== false) {
                $lines2[$data2[0]][] = $data2[1];
            }
            while (($data3 = fgetcsv($handle3, 0, ",")) !== false) {
                $lines3[$data3[0]][] = $data3;
            }
            fclose($handle);
            fclose($handle2);
            fclose($handle3);
            $offset = 0;
            
            foreach($lines as $i => $cells){
                if($i == 0 && array_search("Num_Partie", $cells) !== false){
                    $offset++;
                }
                if($i > 0 && count($cells)>1){
                    foreach($cells as $key => $cell){
                        $cells[$key] = trim(utf8_encode($cell));
                    }
                    $fullname = $cells[1];
                    $cle = $cells[0];
                    $name_array = explode(",", $fullname);
                    $first_name = @trim($name_array[1]);
                    $last_name = @trim($name_array[0]);
                    
                    $username = str_replace(" ", "", $first_name.".".$last_name);
                    $username = str_replace("'", "", $username);
                    $username = preg_replace("/\(.*\)/", "", trim($username, " -\t\n\r\0\x0B"));
                    $username = preg_replace("/\".*\"/", "", $username);
                    
                    $coapplicants = array();
                    $partners = array();
                    $person = Person::newFromName($username);
                    if(@is_array($lines2[$cle])){
                        foreach($lines2[$cle] as $name){
                            $name_array = explode(",", $name);
                            $first = @trim(utf8_encode($name_array[1]));
                            $last = @trim(utf8_encode($name_array[0]));
                            $coapplicant = Person::newFromNameLike($first." ".$last);
                            if($coapplicant->getId() != 0){
                                $coapplicants[] = $coapplicant->getId();
                            }
                            else{
                                $coapplicants[] = $first." ".$last;
                            }
                        }
                    }
                    if(@is_array($lines3[$cle])){
                        foreach($lines3[$cle] as $cells3){
                            $part_organization_id = str_replace("'", "''",$cells3[1]);
                            $part_institution = str_replace("'", "''",$cells3[2]);
                            $province = str_replace("'", "''",$cells3[3]);
                            $country = str_replace("'", "''",$cells3[5]);
                            $fiscal_year = $cells3[7].'-01-01 00:00:00';
                            $org_type = str_replace("'", "''",$cells3[8]);
                            $committee_name = "";
                            $partners[] = array('part_institution' => $part_institution,
                                                'province' => $province,
                                                'country' => $country,
                                                'committee_name' => $committee_name,
                                                'fiscal_year' => $fiscal_year,
                                                'org_type' => $org_type);
                        }
                    }
                    if($person->getId() == 0){
                        // Double check the co-applicants to make sure that at least one of them is from UofA
                        $found = false;
                        foreach($coapplicants as $co){
                            if(is_numeric($co)){
                                $coapplicant = Person::newFromId($co);
                                if($coapplicant->isRoleAtLeast(CI)){
                                    $found = true;
                                    break;
                                }
                            }
                        }
                        if($found){
                            // Create the user
                            $user = User::createNew($username, array('real_name' => "$first_name $last_name", 
                                                                     'password' => User::crypt(mt_rand())));
                            if($user != null){
                                $person = new LimitedPerson(array());
                                $person->id = $user->getId();
                                $person->name = $username;
                                $person->realname = "$first_name $last_name";
                                Person::$namesCache[$username] = $person;
                                Person::$idsCache[$person->getId()] = $person;
                                Person::$cache[strtolower($username)] = $person;
                                Person::$cache[$person->getId()] = $person;
                            }
                            else{
                                echo "ERROR Adding {$username}\n";
                            }
                        }
                    }
                    if($person->getId() != 0){
                        $user_id = $person->getId();
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
                        $application_summary = strip_tags(str_replace("'", "''",$cells[35+$offset]));
                        $grantAward = GrantAward::newFromTitle($application_title);
                        if($grantAward->getId() != 0 && 
                           $grantAward->user_id == $user_id && 
                           $grantAward->competition_year == $competition_year &&
                           ($grantAward->start_year > $fiscal_year || $grantAward->end_year < $fiscal_year)){
                            $grantAward->start_year = min($grantAward->start_year, $fiscal_year);
                            $grantAward->end_year   = max($grantAward->end_year,   $fiscal_year);
                            $grantAward->amount += $amount;
                            $grantAward->update();
                        }
                        else{
                            DBFunctions::insert('grand_new_grants',
                                                array('user_id' => $user_id,
                                                      'department' => $department,
                                                      'institution' => $institution,
                                                      'province' => $province,
                                                      'country' => $country,
                                                      'start_year' => $fiscal_year,
                                                      'end_year' => $fiscal_year,
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
                                                      'application_summary' => $application_summary,
                                                      'coapplicants' => serialize($coapplicants)));
                            $grant_id = DBFunctions::insertId();
                            foreach($partners as $partner){
                                $partner['award_id'] = $grant_id;
                                DBFunctions::insert('grand_new_grant_partner', $partner);
                            }
                            DBFunctions::commit();
                        }
                    }
                    show_status(++$iterationsSoFar, count($lines)-1);
                }
            }
        }
    }
?>
