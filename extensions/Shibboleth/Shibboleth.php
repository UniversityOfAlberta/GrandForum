<?php

// Shibboleth Authentication Stuff
// Load ShibAuthPlugin
require_once('ShibAuthPlugin.php');

if(isset($_SERVER['uid'])){
     
    // Last portion of the shibboleth WAYF url for lazy sessions.
    // This value is found in your shibboleth.xml file on the setup for your SP
    // WAYF url will look something like: /Shibboleth.sso/WAYF/$shib_WAYF
    $shib_WAYF = "";
     
    //Are you using an old style WAYF (Shib 1.3) or new style Discover Service (Shib 2.x)?
    //Values are WAYF or DS, defaults to WAYF
    $shib_WAYFStyle = "WAYF";
     
    // Is the assertion consumer service located at an https address (highly recommended)
    // Default for compatibility with previous version: false
    $shib_Https = true;
     
    // Prompt for user to login
    $shib_LoginHint = "Login via Single Sign-on";
     
    // Where is the assertion consumer service located on the website?
    // Default: "/Shibboleth.sso"
    $shib_AssertionConsumerServiceURL = "/Shibboleth.sso";
     
    // Map Real Name to what Shibboleth variable(s)?
    $shib_RN = ucfirst(strtolower($_SERVER['givenName'])) . ' '
	     . ucfirst(strtolower($_SERVER['sn']));

    // Map e-mail to what Shibboleth variable?
    $shib_email = $_SERVER['uid']."@ualberta.ca";

    // Field containing groups for the user and field containing the prefix to be searched (and stripped) from wiki groups
    # $shib_groups = $_SERVER['isMemberOf'];
    # $shib_group_prefix = "wiki";

    // The ShibUpdateUser hook is executed on login.
    // It has two arguments:
    // - $existing: True if this is an existing user, false if it is a new user being added
    // - &$user: A reference to the user object. 
    //           $user->updateUser() is called after the function finishes.
    // In the event handler you can change the user object, for instance set the email address or the real name
    // The example function shown here should match behavior from previous versions of the extension:
     
    # $wgHooks['ShibUpdateUser'][] = 'ShibUpdateTheUser';

    #function ShibUpdateTheUser($existing, $user) {
    #	global $shib_email;
    #	global $shib_RN;
    #	if (! $existing) {
    #		if($shib_email != null)
    #			$user->setEmail($shib_email);
    #		if($shib_RN != null)
    #			$user->setRealName($shib_RN);
    #	}
    #}

    // This is required to map to something
    // You should beware of possible namespace collisions, it is best to chose
    // something that will not violate MW's usual restrictions on characters
    // Map Username to what Shibboleth variable?
    $shib_UN = ucfirst($_SERVER['uid']);
    $shib_UN = @str_replace(" ", "", ucfirst($_SERVER['givenName']).".".ucfirst($_SERVER['sn']));
     
    // Shibboleth doesn't really support logging out very well.  To take care of
    // this we simply get rid of the logout link when a user is logged in through
    // Shib.  Alternatively, you can uncomment and set the variable below to a link
    // that will either clear the user's cookies or log the user out of the Idp and
    // instead of deleting the logout link, the extension will change it instead.
    # $shib_logout = "https://weblogin.srv.ualberta.ca/logout/";

    // Activate Shibboleth Plugin
    SetupShibAuth();
}
else if(isset($_GET['clearSession'])){
    global $wgUser;
    session_unset();
    session_destroy();
    if($wgUser != null){
        $wgUser->doLogout();
    }
}

?>
