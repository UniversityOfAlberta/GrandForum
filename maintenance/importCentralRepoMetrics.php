<?php
        /**used to transfer data from centralrepo to main database.**/
	
   require_once( "commandLine.inc" );
   $servername = "199.116.235.47";
   $username = "new_root";
   $password = "shoutTEARstreamTAIL";
   $DB = "dev";
      //create connection
   $conn = new mysqli($servername, $username, $password);
   if($conn->connect_error){
   	echo($conn->connect_error);
   }
   else{
   	print_r("connected");
	$sql = "SELECT * FROM $DB.author_metrics, $DB.author, $DB.author_has_metrics WHERE
			$DB.author_metrics.metric_set_id = $DB.author_has_metrics.metric_set_id
			AND $DB.author.id_number = $DB.author_has_metrics.author_id";
	$result = $conn->query($sql);
        $count=0;
        if ($result->num_rows > 0){
              //setting to null incase there are null values from central repo
	    while($row = $result->fetch_assoc()){
		    //try with fullname
		$fullname=$row['full_name'];
                $person = Person::newFromName($fullname);
                $id = $person->getId();
		    //if fullname is empty try email
                if($id == 0){
                    $email = $row['email'];
                    $person = Person::newFromEmail($email);
                    if($person != null){
                         $id = $person->getId();
		    //if email is empty just use name
                         if($id == 0){
			   $person->name = $fullname;
                           $id = $fullname;
                         }
                    }
		}
		if($id == 0){
		    continue;
		}
		 
		$date = explode("-",$row["acm_publication_years"]);
		if(count($date)>1){
		    $startDate = $date[0];
		    $endDate = $date[1];
		}
		  //set all defaults to 0 incase comes back empty
		$acm_publication_count = (str_replace(",","",$row['acm_publication_count']) ?: 0);
		$acm_avg_citations = (str_replace(",","",$row['acm_avg_citations_per_article']) ?: 0);
		$acm_citation_count = (str_replace(",","",$row['acm_citation_count']) ?: 0);
		$acm_avg_download_per_article = (str_replace(",","",$row['acm_avg_download_per_article']) ?: 0);
		$acm_available_download	= (str_replace(",","",$row['acm_available_download']) ?: 0);
		$acm_download_cumulative = (str_replace(",","",$row['acm_download_cumulative']) ?: 0);
		$acm_download_6_weeks = (str_replace(",","",$row['acm_download_6_weeks']) ?: 0);
		$acm_download_1_year = (str_replace(",","",$row['acm_download_1_year']) ?: 0);;
		$sciverse_coauthor_count = (str_replace(",","",$row['sciverse_coauthor_count']) ?: 0);
		$sciverse_hindex = (str_replace(",","",$row['sciverse_hindex']) ?: 0);
		$sciverse_citation_count = (str_replace(",","",$row['sciverse_citation_count']) ?: 0);
		$sciverse_cited_by_count = (str_replace(",","",$row['sciverse_cited_by_count']) ?: 0);
		$sciverse_doc_count = (str_replace(",","",$row['sciverse_doc_count']) ?: 0);

		$sql = "INSERT INTO grand_user_metrics
			(`user_id`,
			`acm_start_date`,
			`acm_end_date`,
			`acm_publication_count`,
			`acm_avg_citations_per_article`,
			`acm_citation_count`,
			`acm_avg_download_per_article`,
			`acm_available_download`,
			`acm_download_cumulative`,
			`acm_download_6_weeks`,
			`acm_download_1_year`,
			`sciverse_coauthor_count`,
			`sciverse_hindex`,
			`sciverse_citation_count`,
			`sciverse_cited_by_count`,
			`sciverse_doc_count`) VALUES
			($id,
			'$startDate-01-01 00:00:00',
			'$endDate-01-01 00:00:00',
			$acm_publication_count,
			$acm_avg_citations,
			$acm_citation_count,
			$acm_avg_download_per_article,
			$acm_available_download,
			$acm_download_cumulative,
			$acm_download_6_weeks,
			$acm_download_1_year,
			$sciverse_coauthor_count,
			$sciverse_hindex,
			$sciverse_citation_count,
			$sciverse_cited_by_count,
			$sciverse_doc_count)";
		DBFunctions::execSQL($sql,true);
		$count++;		
	     }
	}
    }
?>
