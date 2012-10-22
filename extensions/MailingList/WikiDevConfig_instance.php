<?php
/* Instance-specific configuration */

/* Information needed to connect to the bugzilla database for this instance of WikiDev */
$wgBugzillaReports = array(
  'host'        => "127.0.0.1", 
  'database'    => "wikidevbugs",
  'user'        => "wikidevbugs",
  'password'    => "annokirocks",
  'bzserver'    => "http://hypatia.cs.ualberta.ca/wikidevbugzilla/"
);

/* Path to the bugzilla installation for this instance of WikiDev */
$wdBugzillaPath = "/local/hypatia/projects/wikidev2.0/bugzilla-3.2.3/wikidev-bugzilla";

/* Connection information for JDEvAn PostgreSQL database. */
$wdJdevanDbInfo = array ( //Set $jdevanDbInfo to false to disable UML diagram generation and JDEvAn usage
                       'host'     => 'hypatia.cs.ualberta.ca', //host of the JDEvAn database server
                       'port'     => 5433, //Port number of JDEvAn database server (default is 5432)
                       'database' => 'tansey_test', //JDEvAn database in which UML diagram information will be stored
                       'user'     => 'ucosp',   //JDEvAn database username
                       'password' => 'wikidev'  //Passowrd for database user
                       );

/* 
 * If set to true will remove the requirement to specify a mailing list when registering a team.  
 */
$wdDisableMail = false;

/* 
 * If set to true will remove the requirement to specify a repository when registering a team.  
 */
$wdDisableSVN = false;
?>
