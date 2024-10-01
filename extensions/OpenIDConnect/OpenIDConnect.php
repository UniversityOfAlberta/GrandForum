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
    'scope' => [ 'openid', 'profile', 'email' ],
    'providerConfig' => array (
        'issuer' => $config->getValue('oidcUrl'),
        'authorization_endpoint' => $config->getValue('oidcUrl') . '/authorize',
        'token_endpoint' => $config->getValue('oidcUrl') . '/oauth/token',
        'device_authorization_endpoint' => $config->getValue('oidcUrl') . '/oauth/device/code',
        'userinfo_endpoint' => $config->getValue('oidcUrl') . '/userinfo',
        'mfa_challenge_endpoint' => $config->getValue('oidcUrl') . '/mfa/challenge',
        'jwks_uri' => $config->getValue('oidcUrl') . '/.well-known/jwks.json',
        'registration_endpoint' => $config->getValue('oidcUrl') . '/oidc/register',
        'revocation_endpoint' => $config->getValue('oidcUrl') . '/oauth/revoke',
        'scopes_supported' => array (
            0 => 'openid',
            1 => 'profile',
            2 => 'offline_access',
            3 => 'name',
            4 => 'given_name',
            5 => 'family_name',
            6 => 'nickname',
            7 => 'email',
            8 => 'email_verified',
            9 => 'picture',
            10 => 'created_at',
            11 => 'identities',
            12 => 'phone',
            13 => 'address',
        ),
        'response_types_supported' => 
        array (
            0 => 'code',
            1 => 'token',
            2 => 'id_token',
            3 => 'code token',
            4 => 'code id_token',
            5 => 'token id_token',
            6 => 'code token id_token',
        ),
        'code_challenge_methods_supported' => 
        array (
            0 => 'S256',
            1 => 'plain',
        ),
        'response_modes_supported' => 
        array (
            0 => 'query',
            1 => 'fragment',
            2 => 'form_post',
        ),
        'subject_types_supported' => 
        array (
            0 => 'public',
        ),
        'token_endpoint_auth_methods_supported' => 
        array (
            0 => 'client_secret_basic',
            1 => 'client_secret_post',
            2 => 'private_key_jwt',
        ),
        'claims_supported' => 
        array (
            0 => 'aud',
            1 => 'auth_time',
            2 => 'created_at',
            3 => 'email',
            4 => 'email_verified',
            5 => 'exp',
            6 => 'family_name',
            7 => 'given_name',
            8 => 'iat',
            9 => 'identities',
            10 => 'iss',
            11 => 'name',
            12 => 'nickname',
            13 => 'phone_number',
            14 => 'picture',
            15 => 'sub',
        ),
        'request_uri_parameter_supported' => false,
        'request_parameter_supported' => false,
        'id_token_signing_alg_values_supported' => 
        array (
            0 => 'HS256',
            1 => 'RS256',
            2 => 'PS256',
        ),
        'token_endpoint_auth_signing_alg_values_supported' => 
        array (
            0 => 'RS256',
            1 => 'RS384',
            2 => 'PS256',
        ),
        'end_session_endpoint' => $config->getValue('oidcUrl') . '/oidc/logout',
    )
];


?>
