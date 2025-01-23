<?php

define('CONTROLS_DISABLED', "");

abstract class AbstractDuplicatesHandler {
    
    var $id;
    var $upperId;
    var $ignoredCache;
    
    static $handlers;
    
    function __construct($id){
        $this->id = str_replace("/", "", str_replace("-", "", str_replace(" ", "", $id)));
        $this->upperId = ucfirst($this->id);
        self::$handlers[$this->id] = $this;
        $this->ignoredCache = null;
    }
    
    abstract function getArray();
    
    abstract function getArray2();
    
    abstract function handleDelete();
    
    function handleMerge(){
        // This one is optional, most probably won't implement this
        return;
    }
    
    function canShortCircuit($obj1, $obj2){
        return false;
    }
    
    function handleIgnore(){
        $data = DBFunctions::select(array('grand_ignored_duplicates'),
                                    array('*'),
                                    array('id1' => EQ($_POST['id1']),
                                          'id2' => EQ($_POST['id2']),
                                          'type' => EQ($this->id)));
        if(count($data) == 0){
            // Make sure we didn't already insert this entry
            $sql = "INSERT INTO `grand_ignored_duplicates`
                    (`id1`, `id2`, `type`) VALUES ('{$_POST['id1']}', '{$_POST['id2']}', '{$this->id}')";
            DBFunctions::execSQL($sql, true);
        }
    }
    
    function handleGet(){
    	ini_set('max_execution_time', 60*3);
        $array = $this->getArray();
        $array2 = $this->getArray2();
        $i = 0;
        $lastPerc = 0;
        $nResults = count($array);
        echo "<span style='display:none;' class='0'></span>";
        ob_flush();
        flush();
        if($nResults > 0){
            foreach($array as $key => $obj1){
                unset($array2[$key]);
                foreach($array2 as $obj2){
                    if($this->canShortCircuit($obj1, $obj2)){
                        break;
                    }
                    echo $this->showResult($obj1, $obj2);
                    ob_flush();
                    flush();
                }
                $i++;
                if(round(($i/$nResults)*100) != $lastPerc){
                    echo "<span style='display:none;' class='".round(($i/$nResults)*100)."'></span>";
                    ob_flush();
                    flush();
                }
                $lastPerc = round(($i/$nResults)*100);
            }
        }
        else{
            echo "<span style='display:none;' class='".round((1)*100)."'></span>";
            ob_flush();
            flush();
        }
    }
    
    function areIgnored($id1, $id2){
	    if(is_null($this->ignoredCache)){
	        $this->ignoredCache = array();
	        $sql = "SELECT `id1`, `id2`
	                FROM `grand_ignored_duplicates`
	                WHERE `type` = '{$this->id}'";
	        $data = DBFunctions::execSQL($sql);
	        foreach($data as $row){
	            $this->ignoredCache[$row['id1']][$row['id2']] = true;
	        }
	    }
	    return (isset($this->ignoredCache[$id1][$id2]) || isset($this->ignoredCache[$id2][$id1]));
	}
    
    function beginTable($id1, $id2, $text){
        return "<h2><a style='cursor:pointer;' onClick='$(\"#{$this->id}{$id1}_{$id2}\").toggle(400);'>{$text}</a></h2>
                <div id='{$this->id}{$id1}_{$id2}' class='{$this->id}{$id1} {$this->id}{$id2}' style='display:none;'>
                    <table cellspacing='1' cellpadding='3' frame='box' rules='all' style='background:#eee;width:100%;border-color:gray;'>\n";
    }
    
    function addControls($id1, $id2){
        return "<tr><td class='duplicateControls'><input style='vertical-align:middle;' type='button' value='Delete' onClick='delete{$this->upperId}(this, {$id1});' ".CONTROLS_DISABLED." /></td><td class='duplicateControls'><input type='button' value='Delete' onClick='delete{$this->upperId}(this, {$id2});' ".CONTROLS_DISABLED." /></td></tr>
                <tr><td colspan='2' class='duplicateControls'><input style='vertical-align:middle;' type='button' value='Not Duplicates' onClick='ignore{$this->upperId}(this, {$id1}, {$id2});' ".CONTROLS_DISABLED." /></td></tr>";
    }
    
    function endTable(){
        return "    </table>
                </div>";
    }
    
    function addDiffRow($str1, $str2){
        $diff1 = @htmlDiff($str1, $str2);
        $diff2 = @htmlDiff($str2, $str1);
        return "<tr><td class='left'>$diff2</td><td class='right'>$diff1</td></tr>\n";
    }

    function addDiffHeadRow($str1, $str2, $href1, $href2){
        $diff1 = @htmlDiff($str1, $str2);
        $diff2 = @htmlDiff($str2, $str1);
        return "<tr><td align='center' class='left'><a style='color:#039;' target='_blank' href='$href1'><b>$diff2</b></a></th><td align='center' class='right'><a style='color:#039;' target='_blank' href='$href2'><b>$diff1</b></a></th></tr>\n";
    }
    
    function addDiffNLRow($str1, $str2){
        $diff1 = @$this->htmlDiffNL($str1, $str2);
        $diff2 = @$this->htmlDiffNL($str2, $str1);
        return "<tr><td class='left'>$diff2</td><td class='right'>$diff1</td></tr>\n";
    }
    
    function htmlDiffNL($old, $new){
	    $diff = diff(explode(' ', $old), explode(' ', $new));
	    foreach($diff as $k){
		    if(is_array($k))
			    $ret .= (!empty($k['d'])?str_replace("\n", "</del><br><del>", "<del>".implode(' ',$k['d'])."</del> "):'').
				    (!empty($k['i'])?str_replace("\n", "</ins><br><ins>", "<ins>".implode(' ',$k['i'])."</ins> "):'');
		    else $ret .= str_replace("\n", "<br>", $k . ' ');
	    }
	    return str_replace("<ins></ins>", "", str_replace("<del></del>", "", $ret));
    }
    
    function addHTML(){
        global $wgServer, $wgScriptPath;
        return "<div>
                    <input style='vertical-align:middle;' id='{$this->id}DuplicatesButton' type='button' onClick='{$this->id}Duplicates();' value='Calculate Duplicates' />&nbsp;
                    <div style='vertical-align:middle;display:inline-block;width:250px;' id='{$this->id}DuplicatesProgress'></div>
                </div>
                <div id='{$this->id}Duplicates'>
                </div>";
    }
    
    function addScripts(){
        global $wgOut, $wgServer, $wgScriptPath;
        $wgOut->addScript("<script type='text/javascript'>
            function delete{$this->upperId}(button, id){
                $(button).parent().append(\"<img src='$wgServer$wgScriptPath/skins/Throbber.gif' />\");
                var data = 'id=' + id;
                $.ajax({
                    type: 'POST',
                    url: 'index.php?action=deleteDuplicates&handler={$this->id}',
                    data: data,
                    success: function (data) {
                        $('.{$this->id}' + id).prev().html('DELETED - ' + $('.{$this->id}' + id).prev().html());
                        $('.{$this->id}' + id).prev().css('color', '#00aa00');
                        $('.{$this->id}' + id).remove();
                    }
                });
            }
            
            function merge{$this->upperId}(button, id1, id2){
                $(button).parent().append(\"<img src='$wgServer$wgScriptPath/skins/Throbber.gif' />\");
                var data = 'id1=' + id1 + '&id2=' + id2;
                $.ajax({
                    type: 'POST',
                    url: 'index.php?action=mergeDuplicates&handler={$this->id}',
                    data: data,
                    success: function (data) {
                        $('.{$this->id}' + id).prev().html('MERGED - ' + $('#{$this->id}' + id1 + '_' + id2).prev().html());
                        $('.{$this->id}' + id).prev().css('color', '#00aa00');
                        $('.{$this->id}' + id).remove();
                    }
                });
            }
            
            function ignore{$this->upperId}(button, id1, id2){
                $(button).parent().append(\"<img src='$wgServer$wgScriptPath/skins/Throbber.gif' />\");
                var data = 'id1=' + id1 + '&id2=' + id2;
                $.ajax({
                    type: 'POST',
                    url: 'index.php?action=ignoreDuplicates&handler={$this->id}',
                    data: data,
                    success: function (data) {
                        $('#{$this->id}' + id1 + '_' + id2).prev().html('NOT DUPLICATES - ' + $('#{$this->id}' + id1 + '_' + id2).prev().html());
                        $('#{$this->id}' + id1 + '_' + id2).prev().css('color', '#00aa00');
                        $('#{$this->id}' + id1 + '_' + id2).remove();
                    }
                });
            }

	        $(document).ready(function(){
	            $('#{$this->id}DuplicatesProgress').progressbar({
	                value: 0
                });
	        });
	        
	        function {$this->id}Duplicates(){
	            $('#{$this->id}DuplicatesButton').attr('disabled', 'true');
	            $('#{$this->id}DuplicatesProgress').progressbar({
		            value: 0
	            });
	            $('#{$this->id}Duplicates').html('');
	            var outputSoFar = '';
	            var ajaxRequest;  // The variable that makes Ajax possible!
                
                try{
	                // Opera 8.0+, Firefox, Safari
	                ajaxRequest = new XMLHttpRequest();
                } catch (e){
	                // Internet Explorer Browsers
	                try{
		                ajaxRequest = new ActiveXObject('Msxml2.XMLHTTP');
	                } catch (e) {
		                try{
			                ajaxRequest = new ActiveXObject('Microsoft.XMLHTTP');
		                } catch (e){

		                }
	                }
                }
	            // Create a function that will receive data sent from the server
	            var processing = false;
                ajaxRequest.onreadystatechange = function(){
                    var text = ajaxRequest.responseText;
                    if((ajaxRequest.readyState == 3 || ajaxRequest.readyState == 4) && (text.match(/<\/div>$/) || text.match(/<\/span>$/))){
                        diffText = text.replace(outputSoFar, '');
                        
                        outputSoFar += diffText;
                        $('#{$this->id}Duplicates').append(diffText);
                        var percent = parseInt($('#{$this->id}Duplicates span:last').attr('class'));
                        $('#{$this->id}DuplicatesProgress').progressbar({
                            value: percent
                        });
                        
                        $('#{$this->id}DuplicatesProgress div').html('<center>' + percent + '%</center>');
                        
                        $.each($('#{$this->id}Duplicates div table'), function(index, val){
                            if(!$(val).hasClass('recommended')){
                                var leftInsLength = 0;
                                var leftDelLength = 0;
                                var rightInsLength = 0;
                                var rightDelLength = 0;
                                var leftLength = 0;
                                var rightLength = 0;
                                $.each($('.left', $(val)), function(i, v){
                                    leftLength += $(v).text().length;
                                });
                                $.each($('.right', $(val)), function(i, v){
                                    rightLength += $(v).text().length;
                                });
                                
                                $.each($('.left ins', $(val)), function(i, v){
                                    leftInsLength += $(v).text().length;
                                });
                                $.each($('.left del', $(val)), function(i, v){
                                    leftDelLength += $(v).text().length;
                                });
                                $.each($('.right ins', $(val)), function(i, v){
                                    rightInsLength += $(v).text().length;
                                });
                                $.each($('.right del', $(val)), function(i, v){
                                    rightDelLength += $(v).text().length;
                                });
                                leftLength -= (leftInsLength + leftDelLength);
                                rightLength -= (rightInsLength + rightDelLength);
                                
                                leftConfidence = (leftInsLength/(leftInsLength + rightInsLength + 1) - leftDelLength/(leftDelLength + rightDelLength + 1));
                                rightConfidence = (rightInsLength/(leftInsLength + rightInsLength + 1) - rightDelLength/(leftDelLength + rightDelLength + 1));
                                
                                leftConfidence += (1 - Math.abs(leftConfidence)) * leftLength/Math.max(1, (leftLength + leftInsLength + leftDelLength));
                                rightConfidence += (1 - Math.abs(rightConfidence)) * rightLength/Math.max(1, (rightLength + rightInsLength + rightDelLength));
                                
                                leftConfidence *= 100;
                                rightConfidence *= 100;
                                
                                if(leftConfidence >= 100 || rightConfidence >= 100){
                                    $(val).children().append('<tr><td><b>Recommendation:</b> Delete</td><td><b>Recommendation:</b> Keep/Merge</td></tr>');
                                    $(val).parent().append('<b>Confidence:</b> ' + 100.00 + '%');
                                    $(val).parent().prev().html('Identical (100.00%) - ' + $(val).parent().prev().html());
                                    $(val).parent().prev().css('color', '#ff0000');
                                }
                                else if(leftConfidence >= 80 && rightConfidence <= leftConfidence){
                                    $(val).children().append('<tr><td><b>Recommendation:</b> Keep/Merge</td><td><b>Recommendation:</b> Delete</td></tr>');
                                    $(val).parent().append('<b>Confidence:</b> ' + leftConfidence.toFixed(2) + '%');
                                    $(val).parent().prev().html('High Prob (' + leftConfidence.toFixed(2) + '%) - ' + $(val).parent().prev().html());
                                    $(val).parent().prev().css('color', '#ff8800');
                                }
                                else if(rightConfidence >= 80 && leftConfidence <= rightConfidence){
                                    $(val).children().append('<tr><td><b>Recommendation:</b> Delete</td><td><b>Recommendation:</b> Keep/Merge</td></tr>');
                                    $(val).parent().append('<b>Confidence:</b> ' + rightConfidence.toFixed(2) + '%');
                                    $(val).parent().prev().html('High Prob (' + rightConfidence.toFixed(2) + '%) - ' + $(val).parent().prev().html());
                                    $(val).parent().prev().css('color', '#ff8800');
                                }
                                else {
                                    $(val).children().append('<tr><td><b>Recommendation:</b> Ignore</td><td><b>Recommendation:</b> Ignore</td></tr>');
                                    $(val).parent().append('<b>Confidence:</b> ' + Math.max(Math.abs(rightConfidence), Math.abs(leftConfidence)).toFixed(2) + '%');
                                    $(val).parent().prev().html('Low Prob (' + Math.max(Math.abs(rightConfidence), Math.abs(leftConfidence)).toFixed(2) + '%) - ' + $(val).parent().prev().html());
                                    $(val).parent().prev().css('color', '#0088ff');
                                }
                                $(val).addClass('recommended');
                                var confidence = Math.max(Math.abs(rightConfidence), Math.abs(leftConfidence)).toFixed(2);
                                $(val).parent().prev().attr('confidence', confidence);
                                
                                $.each($('#{$this->id}Duplicates div table'), function(i, v){
                                    if($(v).hasClass('recommended')){
                                        conf = $(v).parent().prev().attr('confidence');
                                        if(parseFloat(conf) < parseFloat(confidence)){
                                            var header = $(val).parent().prev().detach();
                                            var table = $(val).parent().detach();
                                            header.insertBefore($(v).parent().prev());
                                            table.insertBefore($(v).parent().prev());
                                            return false;
                                        }
                                    }
                                });
                            }
                        });
                        
                    }
	                if(ajaxRequest.readyState == 4){
	                    $('#{$this->id}DuplicatesButton').removeAttr('disabled');
	                    $('#{$this->id}DuplicatesProgress').progressbar({
		                    value: 100
	                    });
	                    $('#{$this->id}Duplicates span').remove();
	                }
                }
                ajaxRequest.open('GET', '$wgServer$wgScriptPath/index.php?action=getDuplicates&handler={$this->id}', true);
                ajaxRequest.send(null);
            }
	    </script>");
    }
    
}

?>
