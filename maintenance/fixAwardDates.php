<?php

    require_once('commandLine.inc');

    $wgUser = User::newFromId(1);
    
    $awards = Product::getAllPapers('all', 'Award');
    $iterationsSoFar = 0;
    foreach($awards as $award){
        $acceptanceDate = $award->getAcceptanceDate();
        $date = $award->getDate();
        $award->getData();
        $award->getAuthors();
        
        if($acceptanceDate == '0000-00-00'){
            $award->acceptance_date = '0000-00-00';
        }
        else{
            $award->acceptance_date = '0000-00-00';
            $award->date = $acceptanceDate;
        }
        
        $award->data['start_date'] = $acceptanceDate;
        $award->data['end_date'] = $date;
        
        $award->update();
        show_status(++$iterationsSoFar, count($awards));
    }
    

?>
