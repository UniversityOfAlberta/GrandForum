<?php
$wdGroupFile = "/etc/group";
$wdPasswdFile = "/etc/passwd";
$pwAuthPath = "/local/hypatia/projects/wikidev2.0/bin/pwauth";

$wdLocalSVNFilePath = dirname(__FILE__) . '/svn'; //Relative to the root WikiDev (current) directory.
$wdSVNPrefix = "svn+ssh://hypatia.cs.ualberta.ca";
$wdMailmanBin = "~mailman/bin";
$wdMailmanArchives = "/var/lib/mailman/archives/private";

/**
 * These users are allowed to authenticate either through the global pwauth system OR through the standard
 * authentication system for this wiki. They can also change their local password.
 */
$wdAllowLocalAuth = array("WikiSysop", "Admin");

/** 
 * If the required version of Java (1.6+) is not on the path, this can be set to a custom
 * installation.  This should be set to the path of the actual java executable.  If the given
 * file cannot be found, WikiDev will revert back to whatever is available on the path.
 */
$wdCustomJavaPath = '/usr/java/latest/bin/java';



if (file_exists($wdCustomJavaPath))
  $wdJavaPath = $wdCustomJavaPath;
else
  $wdJavaPath = 'java';

if (!file_exists("$IP/extensions/MailingList/WikiDevConfig_instance.php")) {
	print "Instance specific configuration file not found.<br>
	Please edit the settings in WikiDevConfig_instance.sample.php and rename it to WikiDevConfig_instance.php";
	die;
}

require_once("WikiDevConfig_instance.php");

?>
