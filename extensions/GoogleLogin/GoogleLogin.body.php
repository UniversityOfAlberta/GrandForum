<?php

$wgGLSecret = $config->getValue('googleLoginSecret');
$wgGLAppId = $config->getValue('googleLoginAppId');
$wgGroupPermissions['*']['createaccount'] = true;
$wgGroupPermissions['*']['autocreateaccount'] = true;
$wgWhitelistRead = [
    'Special:Login',
    'Special:GoogleLoginReturn',
    'Special:CreateAccount',
    'Special:CreateAccount/return'
];
$wgAuthManagerConfig = [
    'primaryauth' => [
        GoogleLogin\Auth\GooglePrimaryAuthenticationProvider::class => [
            'class' => GoogleLogin\Auth\GooglePrimaryAuthenticationProvider::class,
            'sort' => 0
        ]
    ],
    'preauth' => [],
    'secondaryauth' => []
];
$wgInvalidUsernameCharacters = ':~';
$wgUserrightsInterwikiDelimiter = '~';
//$wgGLAuthoritativeMode = true;

wfLoadExtension('GoogleLogin');

?>
