<?php

require_once('commandLine.inc');
    $csvFile = 'COMBINE.csv';
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
'".str_replace("'", "\'",$resource[0])."',
'".str_replace("'", "\'",$resource[1])."',
'".str_replace("'", "\'",$resource[2])."',
'".str_replace("'", "\'",strip_tags($resource[3]))."',
'".str_replace("'", "\'",strip_tags($resource[4]))."',
'".str_replace("'", "\'",strip_tags($resource[5]))."',
'".str_replace("'", "\'",$resource[6])."',
'".str_replace("'", "\'",$resource[7])."',
'".str_replace("'", "\'",strip_tags($resource[8]))."',
'".str_replace("'", "\'",$resource[9])."',
'".str_replace("'", "\'",$resource[10])."',
'".str_replace("'", "\'",$resource[11])."',
'".str_replace("'", "\'",$resource[12])."',
'".str_replace("'", "\'",$resource[13])."',
'".str_replace("'", "\'",$resource[14])."',
'".str_replace("'", "\'",$resource[15])."',
'".str_replace("'", "\'",$resource[16])."',
'".str_replace("'", "\'",$resource[17])."',
'".str_replace("'", "\'",$resource[18])."',
'".str_replace("'", "\'",$resource[19])."',
'".str_replace("'", "\'",$resource[20])."',
'".str_replace("'", "\'",$resource[21])."',
'".str_replace("'", "\'",$resource[22])."',
'".str_replace("'", "\'",$resource[23])."',
'".str_replace("'", "\'",$resource[24])."',
'".str_replace("'", "\'",$resource[25])."',
'".str_replace("'", "\'",$resource[26])."',
'".str_replace("'", "\'",$resource[27])."',
'".str_replace("'", "\'",$resource[28])."',
'".str_replace("'", "\'",$resource[29])."',
'".str_replace("'", "\'",$resource[30])."',
'".str_replace("'", "\'",$resource[31])."',
'".str_replace("'", "\'",$resource[32])."',
'".str_replace("'", "\'",$resource[33])."',
'".str_replace("'", "\'",$resource[34])."',
'".str_replace("'", "\'",$resource[35])."',
'".str_replace("'", "\'",$resource[36])."',
'".str_replace("'", "\'",$resource[37])."',
'".str_replace("'", "\'",$resource[38])."',
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
