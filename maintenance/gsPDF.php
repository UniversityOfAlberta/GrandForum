<?php

require_once('commandLine.inc');

$sops = DBFunctions::select(array('grand_gsms'),
                            array('id'));

foreach($sops as $sop){
    $data = DBFunctions::select(array('grand_gsms'),
                                array('*'),
                                array('id' => $sop['id']));
    $data = $data[0];
    if($data['pdf_contents'] != ''){
        $size1 = strlen($data['pdf_contents']);
        $pdf = gzinflate($data['pdf_contents']);
        file_put_contents("input.pdf", $pdf);
        
        system("../extensions/Reporting/PDFGenerator/gs -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -sOutputFile=output.pdf input.pdf &> /dev/null");
        
        if(file_exists("output.pdf")){
            $contents = file_get_contents("output.pdf");
            if(strlen($contents) > 0){
                $contents = gzdeflate($contents);
                $size2 = strlen($contents);
                DBFunctions::update('grand_gsms',
                                    array('pdf_contents' => $contents),
                                    array('id' => $sop['id']));
                echo "Update {$sop['id']}: {$size1} -> {$size2}\n";
            }
            else{
                echo "Fail {$sop['id']}\n";
            }
        }
        else{
            echo "Fail {$sop['id']}\n";
        }
        @unlink("input.pdf");
        @unlink("output.pdf");
    }
}

?>
