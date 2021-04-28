<?php

require_once('commandLine.inc');

$sops = DBFunctions::select(array('grand_sop'),
                            array('id'));

foreach($sops as $sop){
    $data = DBFunctions::select(array('grand_sop'),
                                array('*'),
                                array('id' => $sop['id']));
    $data = $data[0];
    if($data['pdf_contents'] != ''){
        $size1 = strlen($data['pdf_contents']);
        $pdf = gzinflate($data['pdf_contents']);
        file_put_contents("input.pdf", $pdf);
        
        exec("../extensions/Reporting/PDFGenerator/gs -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -dPDFSETTINGS=/ebook -dCompatibilityLevel=1.4 -sOutputFile=output.pdf input.pdf &> /dev/null", $output, $ret);
        if(file_exists("output.pdf") && $ret === 0){
            $contents = file_get_contents("output.pdf");
            if(strlen($contents) > 0){
                $contents = gzdeflate($contents);
                $size2 = strlen($contents);
                if($size1 > $size2){
                    // Only update if the file is actually smaller
                    DBFunctions::update('grand_sop',
                                        array('pdf_contents' => $contents),
                                        array('id' => $sop['id']));
                    echo "Update {$sop['id']}: {$size1} -> {$size2}\n";
                }
                else {
                    echo "Skipped {$sop['id']}: {$size1} -> {$size2}\n";
                }
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
