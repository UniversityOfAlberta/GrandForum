<?php
include_once('../commandLine.inc');

//change the schema
$sql = "ALTER TABLE `grand_products` CHANGE COLUMN `last_modified` `date_changed` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
DBFunctions::execSQL($sql, true);

// TODO: The default value might need changing
$sql = "ALTER TABLE `grand_products` ADD COLUMN `date_created` TIMESTAMP NOT NULL";
DBFunctions::execSQL($sql, true);

$sql = "UPDATE `grand_products` 
        SET `date_changed`=`date_changed`,
            `date_created`=`date_changed`";
DBFunctions::execSQL($sql, true);

$sql = "CREATE TABLE IF NOT EXISTS `grand_product_projects` (
  `product_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

DBFunctions::execSQL($sql, true);

$sql = "SELECT *
        FROM `grand_products`";

$products = DBFunctions::execSQL($sql);
foreach($products as $product){
    if(isset($product['projects'])){
        $projects = unserialize($product['projects']);
        foreach($projects as $project){
            $p = Project::newFromName($project);
            if($p != null){
                $sql = "INSERT INTO `grand_product_projects` (`product_id`,`project_id`)
                        VALUES ({$product['id']},{$p->getId()})";
                DBFunctions::execSQL($sql, true);
            }
        }
    }
}

$sql= "ALTER TABLE `grand_products` DROP `projects`";

DBFunctions::execSQL($sql, true);

echo "ALL DONE!\n";
  
?>
