<?php
require_once( 'commandLine.inc' );
chdir(__DIR__);
$wgUser = User::newFromId(1);

require __DIR__ . '/../symfony/vendor/autoload.php';

if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient(){
    $client = new Google_Client();
    $client->setApplicationName('Google Sheets API');
    $client->setScopes([Google_Service_Sheets::SPREADSHEETS]);
    $client->setAccessType('offline');
    $client->setAuthConfig('credentials.json');
    return $client;
}

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Sheets($client);

// Prints the names and majors of students in a sample spreadsheet:
// https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit
$spreadsheetId = '1DlWOsQH_3tbYFKUohmFyat7xHHiqU8xH7KPxW-VXGnM';
$range = 'Form Responses 1';

$response = $service->spreadsheets_values->get($spreadsheetId, $range);
$values = $response->getValues();
var_dump($values);