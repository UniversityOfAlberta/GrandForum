<?php

require_once( 'commandLine.inc' );

echo "Creating new Tables...\n";
DBFunctions::execSQL("DROP TABLE IF EXISTS `grand_contributions_projects`", true);
DBFunctions::execSQL("CREATE TABLE IF NOT EXISTS `grand_contributions_projects` (
  `contribution_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  PRIMARY KEY (`contribution_id`,`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1", true);

DBFunctions::execSQL("DROP TABLE IF EXISTS `grand_contributions_partners`", true);
DBFunctions::execSQL("CREATE TABLE IF NOT EXISTS `grand_contributions_partners` (
  `contribution_id` int(11) NOT NULL,
  `partner` varchar(512) NOT NULL,
  `type` varchar(16) NOT NULL,
  `subtype` varchar(256) NOT NULL,
  `cash` int(11) NOT NULL,
  `kind` int(11) NOT NULL,
  `unknown` tinyint(1) NOT NULL,
  PRIMARY KEY (`contribution_id`,`partner`),
  KEY `type` (`type`),
  KEY `subtype` (`subtype`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;", true);

echo "\tDone!\n\n";

echo "Migrating Contribution Projects";
$sql = "SELECT *
        FROM `grand_contributions`";
$data = DBFunctions::execSQL($sql);
foreach($data as $row){
    $projects = unserialize($row['projects']);
    if($projects != null){
        $projects = array_unique($projects);
        foreach($projects as $projectId){
            $sql = "INSERT INTO `grand_contributions_projects` (`contribution_id`,`project_id`)
                    VALUES ('{$row['rev_id']}','{$projectId}')";
            DBFunctions::execSQL($sql, true);
        }
    }
    if($row['rev_id'] % round(count($data)/45) == 0){
        echo ".";
    }
}
echo "!\nDone!\n\n";

echo "Migrating Contribution Partners";
$sql = "SELECT *
        FROM `grand_contributions`";
$data = DBFunctions::execSQL($sql);
foreach($data as $row){
    $partners = unserialize($row['partner_id']);
    if($partners != null){
        $cashSum = 0;
        $kindSum = 0;
        foreach($partners as $key => $partner){
            if(isset($partner['id'])){
                $partnerId = $partner['id'];
            }
            else{
                $partnerId = $partner['name'];
            }
            $cash = floor($row['cash']/count($partners));
            $kind = floor($row['kind']/count($partners));
            $cashSum += $cash;
            $kindSum += $kind;
            if($key == count($partners)-1){
                if($cashSum != $row['cash']){
                    $cash += $row['cash'] - $cashSum;
                }
                if($kindSum != $row['kind']){
                    $kind += $row['kind'] - $kindSum;
                }
            }
            $sql = "SELECT COUNT(*) as count
                    FROM `grand_contributions_partners`
                    WHERE contribution_id = '{$row['rev_id']}'
                    AND partner = '{$partnerId}'";
            $data2 = DBFunctions::execSQL($sql);
            $unknown = 0;
            if(count($partners) > 1){
                $unknown = 1;
            }
            if($data2[0]['count'] == 0){
                if($row['type'] != "cash" &&
                   $row['type'] != "inki" &&
                   $row['type'] != "caki" && 
                   $row['type'] != "none"){
                    switch($row['type']){
                        case "soft":
                            $subtype="equi";
                            break;
                        case "conf":
                            $subtype="Conference Organization";
                            break;
                        case "work":
                            $subtype="Workshop Hosting";
                            break;
                        case "talk":
                            $subtype="Invited Talk";
                            break;
                        default:
                            $subtype=$row['type'];
                            break;
                    }
                    if($cash == 0){
                        $type = "inki";
                    }
                    else{
                        $type = "caki";
                    }
                }
                else{
                    $type = $row['type'];
                    $subtype = "none";
                }
                $sql = "INSERT INTO `grand_contributions_partners` (`contribution_id`,`partner`,`type`,`subtype`,`cash`,`kind`,`unknown`)
                        VALUES ('{$row['rev_id']}','{$partnerId}','$type','$subtype','$cash','$kind','$unknown')";
                DBFunctions::execSQL($sql, true);
            }
        }
        
    }
    if($partners == null || count($partners) == 0){
        $cash = $row['cash'];
        $kind = $row['kind'];
        
        if($row['type'] != "cash" &&
           $row['type'] != "inki" && 
           $row['type'] != "caki" && 
           $row['type'] != "none"){
            switch($row['type']){
                case "soft":
                    $subtype="equi";
                    break;
                case "conf":
                    $subtype="Conference Organization";
                    break;
                case "work":
                    $subtype="Workshop Hosting";
                    break;
                case "talk":
                    $subtype="Invited Talk";
                    break;
                default:
                    $subtype=$row['type'];
                    break;
            }
            if($cash == 0){
                $type = "inki";
            }
            else{
                $type = "caki";
            }
        }
        else{
            $type = $row['type'];
            $subtype = "none";
        }
        
        $sql = "INSERT INTO `grand_contributions_partners` (`contribution_id`,`partner`,`type`,`subtype`,`cash`,`kind`,`unknown`)
                    VALUES ('{$row['rev_id']}','6302','$type','$subtype','$cash','$kind','1')";
        DBFunctions::execSQL($sql, true);
    }
    if($row['rev_id'] % round(count($data)/45) == 0){
        echo ".";
    }
}
echo "!\nDone!\n\n";

?>
