<?php
    require_once( "commandLine.inc" );
    $wgUser=User::newFromId(1);

  //----------- Remove duplicates from GRAND_SOP using user_id ------------//
    $sql = "SELECT `user_id`, count(*)
            FROM `grand_sop`
            GROUP BY `user_id`
            HAVING COUNT(`user_id`) > 1";
    $data = DBFunctions::execSQL($sql);

      //go through each user_id and find all sop rows 
    foreach($data as $row){
        $count = $row['count(*)'];
        $user_id = $row['user_id'];
        $sql2 = "SELECT `id`, `user_id`, `pdf_contents`
                 FROM `grand_sop`
                 WHERE `user_id` = $user_id";
        $data2 = DBFunctions::execSQL($sql2);

          //delete duplicate rows keeping track of to make sure to keep one that has a non-empty PDF blob if all empty keep one.
        $saved_row = false;
        for($i = 0; $i < $count; $i++){
            $id = $data2[$i]['id'];
              //check for sop annotation too
            $sql4 = "SELECT `sop_id`
                    FROM `grand_sop_annotation`
                    WHERE `sop_id` = '{$id}'";
            $data4 = DBFunctions::execSQL($sql4);

              //first check if there are any annotations on sop .. if so skip
            if(count($data4) > 0){
                $saved_row = true;
                continue;
            }
              //save one with a non-empty PDF blob if there is not one saved yet
            elseif($data2[$i]['pdf_contents'] != "" && $saved_row == false){
                $saved_row = true;
                continue;
            }
              //if all empty save one row
            elseif($i == $count-1 && $saved_row == false){
                continue;
            }
            $sql3 = "DELETE FROM `grand_sop`
                     WHERE `id` = $id";
            $data3 = DBFunctions::execSQL($sql3, true);
        }
    }

  //----------- Remove duplicates from GRAND_GSMS using user_id ------------//
    $sql = "SELECT `user_id`, count(*)
            FROM `grand_gsms`
            GROUP BY `user_id`
            HAVING COUNT(`user_id`) > 1";
    $data = DBFunctions::execSQL($sql);

      //go through each user_id and find all gsms rows 
    foreach($data as $row){
        $count = $row['count(*)'];
        $user_id = $row['user_id'];
        $sql2 = "SELECT `id`, `user_id`
                 FROM `grand_gsms`
                 WHERE `user_id` = $user_id";
        $data2 = DBFunctions::execSQL($sql2);

          //delete duplicate rows keeping only the first row or if it has a comment
        for($i = 1; $i < $count; $i++){
            $id = $data2[$i]['id'];
              //check for comment here
            $sql4 = "SELECT `blob_id`
                     FROM `grand_report_blobs`
                     WHERE (`rp_type` LIKE 'RP_OTT' OR `rp_type` LIKE 'RP_COM')
                     AND `proj_id` = {$id}";
            $data4 = DBFunctions::execSQL($sql4);
            if(count($data4) > 0){
                continue;
            }
            $sql3 = "DELETE FROM `grand_gsms`
                     WHERE `id` = $id";
            $data3 = DBFunctions::execSQL($sql3, true);
        }
    }

  //----------- Remove duplicates from GRAND_REPORT_PDF using user_id and year------------//
    $sql = "SELECT `user_id`, `year`, count(*)
            FROM `grand_pdf_report`
            GROUP BY `user_id`, `year`
            HAVING COUNT(`user_id`) > 1";
    $data = DBFunctions::execSQL($sql);

      //go through each user_id and year find all pdf report rows 
    foreach($data as $row){
        $count = $row['count(*)'];
        $user_id = $row['user_id'];
        $year = $row['year'];

        $sql2 = "SELECT `report_id`, `user_id`, `timestamp`
                 FROM `grand_pdf_report`
                 WHERE `user_id` = $user_id AND `year` = $year
                 ORDER BY `timestamp` DESC";
        $data2 = DBFunctions::execSQL($sql2);

          //delete duplicate rows keeping only the first row (this is the one that is updated during cv upload so it has most current updates keep for now).
          //or keep if it has a faculty comment in blobs table
        for($i = 1; $i < $count; $i++){
            $id = $data2[$i]['report_id'];
            $sql3 = "DELETE FROM `grand_pdf_report`
                     WHERE `report_id` = $id";
            $data3 = DBFunctions::execSQL($sql3, true);
        }
    } 
?>
