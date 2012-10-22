<?php
header("Content-type: text/xml");

include_once("db.inc.php");
include_once("SociQL.php");

SociQL::setDialect("MySQL");

$query = "";
if (isset($_GET["txt_query"])) {
    $query = $_GET['txt_query'];
}

SociQL::getFacebookInitialization();

echo SociQL::getResult($query, "XML");


function compareScore($a, $b) {
    if ($a['score'] < $b['score']) {
        return 1;
    } else if ($a['score'] > $b['score']) {
        return -1;
    } else {
        return 0;
    }
}
?>
