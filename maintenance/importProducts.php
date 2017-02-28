<?php

// CSV Format: Date,Member Name,Visibility,Related Projects,Category,Type,Title,Details

require_once('commandLine.inc');

$wgUser = User::newFromId(1);

$rows = explode("\n", file_get_contents("Activities.csv"));
foreach($rows as $row){
    $cols = str_getcsv($row);
    
    if(count($cols) == 8){
        $date = $cols[0];
        $members = explode(",", $cols[1]);
        $visibility = $cols[2];
        $projects = explode(",", $cols[3]);
        $category = $cols[4];
        $type = $cols[5];
        $title = $cols[6];
        $details = $cols[7];
        
        $product = new Product(array());
        $product->date = $date;
        foreach($members as $member){
            $person = new Person(array());
            $person->name = $member;
            $product->authors[] = $person;
        }
        $product->access = $visibility;
        foreach($projects as $project){
            $proj = new Project(array());
            $proj->name = $project;
            $product->projects[] = $proj;
        }
        $product->category = $category;
        $product->type = $type;
        $product->title = $title;
        $product->description = $details;
        $product->create();
    }
}


?>
