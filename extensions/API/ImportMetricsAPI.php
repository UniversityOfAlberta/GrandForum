<?php
    class ImportMetricsAPI extends API {

        function processParams($params){
            //TODO
        }

        function doAction($noEcho=false){
            global $wgMessage, $config;
            $person = Person::newFromId($_POST['id']);
            
            $metrics = $person->getMetrics();
            $metrics->user_id = $person->getId();
            if($metrics->id == 0 || 
               (isset($_GET['forceUpdate']) && $person->isMe()) || 
               strtotime("{$metrics->change_date} +1 week") < time()){  
                if(isset($_GET['getGS'])){
                    //this is where google scholar url will be set and grabbed as an html
                    $person_google_scholar = $person->getGoogleScholar();
                    if($person_google_scholar != ""){
                        $url = @file_get_contents($person_google_scholar);
                        if($url !== false){
                            //grabbing hindex and citation count information using regex
                            $index_regex = '/\<td class\=\"gsc\_rsb\_std\"\>(.+?)\<\/td\>/';
                            preg_match_all($index_regex, $url, $index);
                            //setting the info in gs_metric
                            if(isset($index[1][0])){
                                $metrics->gs_citation_count = $index[1][0];
                                $metrics->gs_hindex = $index[1][2];
                                $metrics->gs_hindex_5_years = $index[1][3];
                                $metrics->gs_i10_index = $index[1][4];
                                $metrics->gs_i10_index_5_years = $index[1][5];
                                //grabbing all citation years
                                $citationArray = array();
                                $year_regex = '/\<span class\=\"gsc_g_t\"(.+?)\>(.+?)\<\/span\>/';
                                preg_match_all($year_regex, $url, $yearmatch);
                                $years = $yearmatch[2];
                                //grabbing all citation counts
                                $counts_regex = '/\<span class\=\"gsc_g_al\"\>(.+?)\<\/span\>/';
                                preg_match_all($counts_regex, $url, $countsmatch);
                                $counts = $countsmatch[1];
                                $i = 0;
                                foreach($years as $year){
                                    $citationArray[$year] = $counts[$i];
                                    $i++;
                                }
                                //setting citation counts in array
                                $metrics->gs_citations = $citationArray;
                                //save to db
                                $metrics->update();
                                if(isset($_GET['forceUpdate'])){
                                    $wgMessage->addSuccess("Updated Google Metrics.");
                                }
                            }
                            else if(isset($_GET['forceUpdate'])){
                                $wgMessage->addError("There was a problem retrieving your Google Scholar Profile");
                            }
                        }
                        else if(isset($_GET['forceUpdate'])){
                            $wgMessage->addError("There was a problem retrieving your Google Scholar Profile");
                        }
                    }
                    else if(isset($_GET['forceUpdate'])){
                        $wgMessage->addError("Please update your bio with your Google Scholar information.");
                    }
                }
                if(isset($_GET['getScopus'])){
                    if($person->getScopus() != "" && $config->getValue('scopusApi') != ""){
                        $data = @json_decode(file_get_contents("https://api.elsevier.com/content/author/author_id/{$person->getScopus()}?apiKey={$config->getValue('scopusApi')}&view=METRICS&httpAccept=application/json"));
                        if(isset($data->{"author-retrieval-response"}) && isset($data->{"author-retrieval-response"}[0]->coredata->{"document-count"})){
                            $metrics->scopus_document_count = $data->{"author-retrieval-response"}[0]->coredata->{"document-count"};
                            $metrics->scopus_cited_by_count = $data->{"author-retrieval-response"}[0]->coredata->{"cited-by-count"};
                            $metrics->scopus_citation_count = $data->{"author-retrieval-response"}[0]->coredata->{"citation-count"};
                            $metrics->scopus_h_index = $data->{"author-retrieval-response"}[0]->{"h-index"};
                            $metrics->scopus_coauthor_count = $data->{"author-retrieval-response"}[0]->{"coauthor-count"};
                            $metrics->update();
                            if(isset($_GET['forceUpdate'])){
                                $wgMessage->addSuccess("Updated Scopus Metrics.");
                            }
                        }
                        else if(isset($_GET['forceUpdate'])){
                            $wgMessage->addError("There was a problem retrieving your Scopus Information");
                        }
                    }
                    else if(isset($_GET['forceUpdate'])){
                        $wgMessage->addError("Please update your bio with your Sciverse id.");
                    }
                }
            }
        }

        function isLoginRequired(){
            return true;
        }
    }
?>
