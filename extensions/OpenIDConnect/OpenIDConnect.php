<?php

// Docs for older version of OpenIDConnect: https://www.mediawiki.org/w/index.php?title=Extension:OpenID_Connect&oldid=4306495

wfLoadExtension( 'OpenIDConnect/PluggableAuth' );
wfLoadExtension( 'OpenIDConnect' );

$wgGroupPermissions['*']['autocreateaccount'] = true;
$wgPluggableAuth_EnableLocalLogin = false;
$wgInvalidUsernameCharacters = ' ';
$wgUserrightsInterwikiDelimiter = '#';
$wgOpenIDConnect_MigrateUsersByEmail = true;
$wgOpenIDConnect_MigrateUsersByUserName = true;
$wgOpenIDConnect_UseEmailNameAsUserName = true;
$wgOpenIDConnect_ForceLogout = true;

$wgOpenIDConnect_Config[$config->getValue('oidcUrl')] = [
    'clientID' => $config->getValue('oidcClientId'),
    'clientsecret' => $config->getValue('oidcSecret'),
    'name' => "Auth0",
    'scope' => [ 'openid', 'profile', 'email', 'email_verified']
];


?>
