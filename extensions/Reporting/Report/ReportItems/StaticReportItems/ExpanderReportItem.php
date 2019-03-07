<?php

class ExpanderReportItem extends StaticReportItem {

    function render(){
        global $wgOut;
        $class = $this->getAttr('class', " ");
        $id = $this->projectId;

        $html =<<<EOF
        <script type="text/javascript">
            $(document).ready(function(){ 

                $('#expand-q_{$id}').click(function(e){
                    e.preventDefault();

                    var lbl = $('#expand-q_{$id}').text();
                    if(lbl == "+Expand All"){
                        $('.toggleDiv_{$id}').show(200);
                        $('#expand-q_{$id}').text("-Shrink All");
                    }else{
                        $('.toggleDiv_{$id}').hide(200);
                        $('#expand-q_{$id}').text("+Expand All");
                    }
                }); 
            })
        </script>
        <p><a style="color: #8C529D; font-weight:bold; font-size: 14px;" id="expand-q_{$id}" href="#">+Expand All</a></p>
EOF;

        $item = $this->processCData($html);
        $wgOut->addHTML($item);
    }

    function renderForPDF(){
        $this->render();
    }

}

?>
