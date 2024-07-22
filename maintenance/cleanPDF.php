<?php

    require_once('commandLine.inc');
    global $wgUser;
    
    $alreadyDone = array();
    $total = 0;
    
    $data = DBFunctions::execSQL("SELECT user_id, year, report_id, len_pdf
                                  FROM grand_pdf_report
                                  WHERE type = 'RPTP_FEC'
                                  AND year != '".YEAR."'
                                  ORDER BY user_id, year, report_id DESC");
                                  
    foreach($data as $row){
        $id = "{$row['user_id']}_{$row['year']}";
        if(!isset($alreadyDone[$id])){
            $alreadyDone[$id] = $row;
            echo "KEEP {$id}_{$row['report_id']}\n";
        }
        else{
            $total += $row['len_pdf'];
            echo "DELETE {$id}_{$row['report_id']} (".number_format($row['len_pdf']/1024/1024, 3)." MB)\n";
            DBFunctions::execSQL("DELETE FROM grand_pdf_report WHERE report_id = '{$row['report_id']}'", false);
        }
    }
    
    echo number_format($total/1024/1024, 3)." MB\n";
    
?>
