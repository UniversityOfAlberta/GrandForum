<?php

require_once('commandLine.inc');
$wgUser = User::newFromId(1);

if(count($argv) != 3){
    echo "Must provide three arguments: php copyApplication.php <userId> <fromYear> <toYear>\n";
    exit;
}

$userId = $argv[0];
$fromYear = $argv[1];
$toYear = $argv[2];

DBFunctions::delete('grand_report_blobs',
                    array('rp_type' => 'RP_CS',
                          'user_id' => $userId,
                          'year' => $toYear));

$data = DBFunctions::select(array('grand_report_blobs'),
                            array('*'),
                            array('rp_type' => 'RP_CS',
                                  'user_id' => $userId,
                                  'year' => $fromYear));

foreach($data as $row){
    $value = $row['data'];
    switch ($row['blob_type']) {
        case BLOB_TEXT:
        case BLOB_HTML:
        case BLOB_WIKI:
        case BLOB_PDF:
        case BLOB_EXCEL:
        case BLOB_RAW:
            // Don't transform the data.
            $value = $row['data'];
            break;
        case BLOB_ARRAY:
        case BLOB_CSV:
        case BLOB_OPTIONANDTEXT:
        case BLOB_TEXTANDAPPROVE:
        case BLOB_ARTIFACT:
        case BLOB_PUBLICATION:
        case BLOB_NEWMILESTONE:
        case BLOB_CURRENTMILESTONE:
        case BLOB_MILESTONESTATUS:
        case BLOB_CONTRIBUTION:
            // Un-Serialize.
            $value = unserialize($row['data']);
            break;
    }
    $blob = new ReportBlob($row['blob_type'], $toYear, $userId, 0);
    $blob_address = ReportBlob::create_address($row['rp_type'], $row['rp_section'], $row['rp_item'], $row['rp_subitem']);
    $blob->store($value, $blob_address);
}

?>
