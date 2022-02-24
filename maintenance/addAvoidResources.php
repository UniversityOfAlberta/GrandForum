<?php

require_once('commandLine.inc');

/*foreach($papers as $paper){
    if(!$paper->hasBeenReported($year, $type) && ($type == "RMC" || ($type == "NCE" && $paper->isPublished()))){
        $sql = "INSERT INTO `grand_products_reported` (`product_id`,`reported_type`,`year`,`data`)
                VALUES ('{$paper->getId()}','{$type}','{$year}','".addslashes(serialize($paper))."')";
        DBFunctions::execSQL($sql, true);
        $nPapers++;
    }
    $i++;
    show_status($i, count($papers));
    flush();
}*/
    $csvFile = 'csv/education.csv';
	$file = fopen($csvFile,"r");
  while(! feof($file))
  {
    //get csid
	  $resource = fgetcsv($file);
	  //$sql = "$resource[0]";
	  $sql = "INSERT INTO `grand_avoid_resources` 
(
`ParentAgency`, 
`PublicName_Program`, 
`ResourceAgencyNum`, 
`AgencyDescription`, 
`HoursOfOperation`, 
`Eligibility`, 
`LanguagesOffered`, 
`LanguagesOfferedList`,
`ApplicationProcess`, 
`Coverage`, 
`CoverageAreaText`, 
`PhysicalAddress1`, 
`PhysicalAddress2`, 
`PhysicalCity`, 
`PhysicalCounty`, 
`PhysicalStateProvince`,
`PhysicalPostalCode`, 
`MailingAttentionName`, 
`MailingAddress1`, 
`MailingAddress2`, 
`MailingCity`, 
`MailingStateProvince`, 
`MailingPostalCode`, 
`DisabilitiesAccess`, 
`Phone1Name`, 
`Phone1Number`, 
`Phone1Description`, 
`PhoneNumberBusinessLine`, 
`PhoneTollFree`, 
`PhoneFax`, 
`EmailAddressMain`, 
`WebsiteAddress`, 
`Custom_Facebook`, 
`Custom_Instagram`, 
`Custom_LinkedIn`, 
`Custom_Twitter`, 
`Custom_YouTube`, 
`Categories`, 
`LastVerifiedOn`,
`Split`,
`PublicName`,
`Category`,
`SubCategory`,
`SubSubCategory`,
`TaxonomyTerms`
)
VALUES 
(
'$resource[0]',
'$resource[1]',
'$resource[2]',
'".strip_tags($resource[3])."',
'".strip_tags($resource[4])."',
'".strip_tags($resource[5])."',
'$resource[6]',
'$resource[7]',
'".strip_tags($resource[8])."',
'$resource[9]',
'$resource[10]',
'$resource[11]',
'$resource[12]',
'$resource[13]',
'$resource[14]',
'$resource[15]',
'$resource[16]',
'$resource[17]',
'$resource[18]',
'$resource[19]',
'$resource[20]',
'$resource[21]',
'$resource[22]',
'$resource[23]',
'$resource[24]',
'$resource[25]',
'$resource[26]',
'$resource[27]',
'$resource[28]',
'$resource[29]',
'$resource[30]',
'$resource[31]',
'$resource[32]',
'$resource[33]',
'$resource[34]',
'$resource[35]',
'$resource[36]',
'$resource[37]',
'$resource[38]',
'',
'',
'',
'',
'',
''
)";
	  
	  DBFunctions::execSQL($sql, true);

  
  
  } 
  flush();

  //close conn and file
  fclose($file);

?>
