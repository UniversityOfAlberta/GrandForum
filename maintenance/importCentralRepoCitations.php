<?php
        /**used to transfer data from centralrepo to main database.**/

        require_once( "commandLine.inc" );
        $servername = "199.116.235.47";
        $username = "new_root";
        $password = "shoutTEARstreamTAIL";

          //create connection
        $conn = new mysqli($servername, $username, $password);
        if($conn->connect_error){
                echo($conn->connect_error);
        }
        else{
                print_r("connected");
                  //deleting from tables so can update them
                DBFunctions::execSQL("TRUNCATE TABLE grand_product_citations",true);
		$sql = "SELECT * FROM dev.article, dev.author, dev.author_wrote_article where
			dev.article.article_id_number = dev.author_wrote_article.article_id_number
			AND dev.author.id_number = dev.author_wrote_article.author_id";
		$result = $conn->query($sql);
                $count=0;
                if ($result->num_rows > 0){
                        while($row = $result->fetch_assoc()){
                                $title = $row["article_title"];
                                $citation_count_gs = $row["citation_count_googlescholar"];
                                $citation_count_sv = $row["citation_count_sciverse"];
				$data = DBFunctions::execSQL("SELECT id FROM grand_products WHERE title LIKE '$title'");
				if(count($data) > 0){
					$product_id = $data[0]['id'];
					if($citation_count_gs>0){
						$type = "Google Scholar";
						DBFunctions::execSQL("INSERT into grand_product_citations(`product_id`, `type`, `citation_count`) VALUES ($product_id, $type, $citation_count_gs)", true); 
					}
					if($citation_count_sv>0){
						$type = "Sciverse Scopus";
						DBFunctions::execSQL("INSERT into grand_product_citations(`product_id`, `type`, `citation_count`) VALUES ($product_id, '$type', $citation_count_sv)", true);
					}
				}
				else{
					print_r($row);
				}
				$count++;
				
			}
		}
	}
