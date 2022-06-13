<?php
    require_once( "commandLine.inc" );
    $wgUser=User::newFromId(1);
    
    $year = "";
    $dbYear = "";
    
    if($year != "" && $year != 0){
        $dbYear = "_$year";
    }

    $data = DBFunctions::select(array("grand_sop$dbYear"),
                                array('id'));
    foreach($data as $row){
        $data2 = DBFunctions::select(array("grand_sop$dbYear"),
                                     array('*'),
                                     array('id' => $row['id']));
        $row = $data2[0];
        if(strlen($row['pdf_contents']) > 0){
            $inflated = gzinflate($row['pdf_contents']);
            
            file_put_contents("input.pdf", $inflated);
            exec("../extensions/Reporting/PDFGenerator/gs -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -dPDFSETTINGS=/ebook -dColorConversionStrategy=/LeaveColorUnchanged -dColorImageResolution=120 -dColorImageDownsampleType=/Bicubic -dGrayImageResolution=120 -dGrayImageDownsampleType=/Bicubic -dCompatibilityLevel=1.4 -sOutputFile=output.pdf input.pdf  &> /dev/null", $output, $ret);
            if(file_exists("output.pdf") && $ret === 0){
                $contents = file_get_contents("output.pdf");
                if(strlen($contents) > 0){
                    $deflated = gzdeflate($contents);
                    if(strlen($row['pdf_contents']) > strlen($deflated)){
                        DBFunctions::update("grand_sop$dbYear",
                                            array('pdf_contents' => $deflated),
                                            array('id' => $row['id']));
                        echo strlen($row['pdf_contents'])." -> ".strlen($deflated)."\n";
                    }
                    else{
                        echo "Skipped {$row['id']}\n";
                    }
                }
                else{
                    echo "Fail {$row['id']}\n";
                }
            }
            else{
                echo "Fail {$row['id']}\n";
            }
            @unlink("input.pdf");
            @unlink("output.pdf");
        }
    }
?>
