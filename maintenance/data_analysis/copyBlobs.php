<?php

require_once('../commandLine.inc');

if(count($args) > 0){
    if($args[0] == "help"){
        showHelp();
        exit;
    }
}


copyNIBlobs(PNI);
copyNIBlobs(CNI);
copyProjectBlobs();


function copyNIBlobs($type){
	//$type = PNI;
	if($type == PNI){
		$reportType = RP_EVAL_RESEARCHER;
	}
	else if($type == CNI){
		$reportType = RP_EVAL_CNI;
	}

	$radio_questions = array(EVL_OVERALLSCORE, EVL_CONFIDENCE, EVL_EXCELLENCE, EVL_HQPDEVELOPMENT, EVL_NETWORKING, EVL_KNOWLEDGE, EVL_MANAGEMENT, EVL_REPORTQUALITY);
    $stock_comments = array(EVL_EXCELLENCE_COM, EVL_HQPDEVELOPMENT_COM, EVL_NETWORKING_COM, EVL_KNOWLEDGE_COM, EVL_MANAGEMENT_COM, EVL_REPORTQUALITY_COM);
	$text_question = EVL_OTHERCOMMENTS;
	
	$nis = Person::getAllEvaluates($type, 2012);

	foreach($nis as $ni){
		$ni_id = $ni->getId();

		$evaluators = $ni->getEvaluators($type, 2012);

		foreach ($evaluators as $ev) {
			$evaluator_id = $ev->getId();
			copyBlob($reportType, BLOB_TEXT, 2012, $evaluator_id, $text_question, $ni_id);

			foreach($radio_questions as $q){
				copyBlob($reportType, BLOB_TEXT, 2012, $evaluator_id, $q, $ni_id);
			}

			foreach($stock_comments as $q){
				copyBlob($reportType, BLOB_ARRAY, 2012, $evaluator_id, $q, $ni_id);
			}

		}


	}
}

function copyProjectBlobs(){
	$type = 'Project';
	
	$reportType = RP_EVAL_PROJECT;
	

	$radio_questions = array(EVL_OVERALLSCORE, EVL_CONFIDENCE, EVL_EXCELLENCE, EVL_HQPDEVELOPMENT, EVL_NETWORKING, EVL_KNOWLEDGE, EVL_REPORTQUALITY);
    $stock_comments = array(EVL_EXCELLENCE_COM, EVL_HQPDEVELOPMENT_COM, EVL_NETWORKING_COM, EVL_KNOWLEDGE_COM, EVL_REPORTQUALITY_COM);
	$text_question = EVL_OTHERCOMMENTS;
	
	$projs = Person::getAllEvaluates($type, 2012);

	foreach($projs as $proj){
		$proj_id = $proj->getId();

		$evaluators = $proj->getEvaluators($type, 2012);

		foreach ($evaluators as $ev) {
			$evaluator_id = $ev->getId();
			copyBlob($reportType, BLOB_TEXT, 2012, $evaluator_id, $text_question, $proj_id);

			foreach($radio_questions as $q){
				copyBlob($reportType, BLOB_TEXT, 2012, $evaluator_id, $q, $proj_id);
			}

			foreach($stock_comments as $q){
				copyBlob($reportType, BLOB_ARRAY, 2012, $evaluator_id, $q, $proj_id);
			}

		}


	}
}

function copyBlob($reportType, $blobType, $year, $evaluator_id, $blobItem, $blobSubItem){
    $project_id = 0;
    if($reportType == RP_EVAL_PROJECT){
        $project_id = $blobSubItem;
    }
	
	$blob1 = new ReportBlob($blobType, $year, $evaluator_id, $project_id);
    $blob_address1 = ReportBlob::create_address($reportType, SEC_NONE, $blobItem, $blobSubItem);
	$blob1->load($blob_address1);
	if($orig_data = $blob1->getData()){

		$new_data = array();
		$new_data['original'] = $orig_data;

		if($blobType == BLOB_TEXT){
			$new_data['revised'] = "";
		}
		else if($blobType == BLOB_ARRAY){
			$new_data['revised'] = array();
		}

		$blob2 = new ReportBlob(BLOB_ARRAY, $year, $evaluator_id, $project_id);
        $blob_address2 = ReportBlob::create_address($reportType, SEC_NONE, $blobItem, $blobSubItem);
        $blob2->store($new_data, $blob_address2);
    }
    
}