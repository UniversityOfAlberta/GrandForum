<?php
    class UpdateGoogleScholarCitationsAPI extends API{
        var $courses = array();
        function processParams($params){
            //TODO
        }

        function doAction($noEcho=false){
            global $wgMessage;
            $person = Person::newFromId($_POST['id']);

            //this is where google scholar url will be set and grabbed as an html
            $person_google_scholar = $person->getGoogleScholar();
            if($person_google_scholar != ""){
                $url = @file_get_contents($person_google_scholar);
                if($url === false){
                    $wgMessage->addError("There was a problem retrieving your Google Scholar Profile");
                    echo "<html>
                        <head>
                            <script type='text/javascript'>
                                parent.ccvUploaded([], 'Updated');
                            </script>
                        </head>
                    </html>";
                    exit;
                }
                $gs_metric = new GsMetric(array());
                $gs_metric->user_id = $person->getId();
                //grabbing hindex and citation count information using regex
                $index_regex = '/\<td class\=\"gsc\_rsb\_std\"\>(.+?)\<\/td\>/';
                preg_match_all($index_regex, $url, $index);
                //setting the info in gs_metric
                if(!isset($index[1][0])){
                    $wgMessage->addError("There was a problem retrieving your Google Scholar Profile");
                    exit;
                }
                $gs_metric->citation_count = $index[1][0];
                $gs_metric->hindex = $index[1][2];
                $gs_metric->hindex_5_years = $index[1][3];
                $gs_metric->i10_index = $index[1][4];
                $gs_metric->i10_index_5_years = $index[1][5];
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
                $gs_metric->gs_citations = $citationArray;
                //save to db
                $status =$gs_metric->create();
                $wgMessage->addSuccess("Updated Google Citations.");
                exit;
            }
            else{
                $wgMessage->addError("Please update your bio with your Google Scholar information.");
                exit;
            }
        }

        function isLoginRequired(){
            return true;
        }
    }
?>
