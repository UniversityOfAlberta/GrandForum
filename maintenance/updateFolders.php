<?php
     require_once( "commandLine.inc" );
     $wgUser=User::newFromId(1);

        //get all unique gsms_ids
     $sql = "select DISTINCT(gsms_id) from grand_gsms";
     $data = DBFunctions::execSQL($sql);
        //go through each gsms_id and find duplicates
     foreach($data as $gsms_id){
        $gsms_get_id = $gsms_id['gsms_id'];
        $data2 = DBFunctions::select(array('grand_gsms'),
                                     array('id'),
                                     array('gsms_id' => $gsms_get_id));
                //If there is a duplicate get the one with the folder (updated by csv upload) and change duplicates
        if(count($data2)>1){
            $get_id = $data2[0]['id'];
            $gsms_folders = DBFunctions::select(array('grand_gsms'),
                                         array('folder'),
                                         array('id' => $get_id));
            $folder = $gsms_folders[0]['folder'];
                //update duplicates here
            if($folder != ""){
                $sql2 = "UPDATE grand_gsms
                        SET folder = '$folder'
                        WHERE gsms_id = $gsms_get_id";
                $update = DBFunctions::execSQL($sql2, true);
            }
        }
     }
?>
