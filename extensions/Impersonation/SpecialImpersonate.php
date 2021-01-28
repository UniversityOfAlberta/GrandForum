<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Impersonate'] = 'Impersonate'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Impersonate'] = $dir . 'SpecialImpersonate.i18n.php';
$wgSpecialPageGroups['Impersonate'] = 'network-tools';

$wgHooks['ToolboxLinks'][] = 'Impersonate::createDelegateLink';

function runImpersonate($par) {
  Impersonate::execute($par);
}

class Impersonate extends SpecialPage {

	function __construct() {
	    global $wgOut, $wgServer, $wgScriptPath;
	    SpecialPage::__construct("Impersonate", null, true, 'runImpersonate');
	    $wgOut->addScript("<script type='text/javascript'>
	        $(document).ready(function(){
	            $('#button').val('Impersonate');
	            $('#pageDescription').html('Select a user from the list below, and then click the \'Impersonate\' button.  You can filter out the selection box by searching a name, user role, or project below.');
	            $('#mainForm').attr('method', 'get');
	            $('#mainForm').attr('action', '$wgServer$wgScriptPath/index.php');
	            $('#button').click(function(){
                    var page = $('select option:selected').attr('name');
                    if(typeof page != 'undefined'){
                        document.location = '".$wgServer.$wgScriptPath."/index.php?impersonate=' + page;
                    }
                });
                $('#search').keyup(function(event) {
                    if(event.keyCode == 13){
                        // Enter key was pressed
                        var page = $('select option:selected').attr('name');
                        if(typeof page != 'undefined'){
                            document.location = '".$wgServer.$wgScriptPath."/index.php?impersonate=' + page;
                        }
                    }
                });
	        });
	    </script>");
	}
	
	function userCanExecute($user){
	    global $wgImpersonate, $wgDelegate;
	    if($wgImpersonate || $wgDelegate){
	        return false;
	    }
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF) || $person->isRole(SD) || count($person->getDelegates()) > 0);
    }
	
	function execute($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
	    $user = Person::newFromWgUser();
	    $wgOut->addScript('<script type="text/javascript">
	                        var sort = "first";
	                        var allPeople = new Array(');
	    $allPeople = array();
	    if($user->isRoleAtLeast(STAFF) || $user->isRole(SD)){
	        $allPeople = array_merge(Person::getAllPeople('all'), Person::getAllCandidates('all'));
	        $i = 0;
	        $names = array();
	        foreach($allPeople as $person){
	            $names[] = $person->getName();
	        }
            foreach(Person::getAllStaff() as $person){
                $names[] = $person->getName();
                $allPeople[] = $person;
            }
        }
        else if(count($user->getDelegates()) > 0){
            $allPeople = $user->getDelegates();
            foreach($allPeople as $person){
                $names[] = $person->getName();
            }
        }
        $names = array_unique($names);
	    $wgOut->addScript('\''.implode("','", $names).'\');
	    var oldOptions = Array();

        function filterResults(value){
            if(value == undefined){
                value = "";
            }
            $.each($("#names").children().not("#no"), function(index, value){
                var valSelect = value.id;
                oldOptions[valSelect] = $("#" + valSelect).detach();
            });
            if(value == ""){
                var no = $("#no").detach();
                if(no.length > 0){
                    oldOptions["no"] = no;
                }
            }
            var n = 0;
            for(i = 0; i < allPeople.length; i++){
                var val = allPeople[i];
                var valSelect = "";
                if(sort == "last" && val.indexOf(".") != -1){
                    if(val.split(".").length == 2){
                        var firstName = val.split(".")[0];
                        var lastName = val.split(".")[1];
                    }
                    else{
                        var firstName = val.substring(0, val.lastIndexOf("."));
                        var lastName = val.split(".")[val.split(".").length - 1];
                    }
                    valSelect = lastName + firstName;
                    valSelect = valSelect.replace(/\./g, "");
                }
                else{
                    valSelect = val.replace(/\./g, "");
                }
                valSelect = unaccentChars(valSelect);
                if(unaccentChars(val.replace(/\./g, " ")).regexIndexOf(unaccentChars(value)) != -1 || (typeof oldOptions[valSelect] != "undefined" && unaccentChars(oldOptions[valSelect].attr("class")).regexIndexOf(unaccentChars(value)) != -1)){
                    if(unaccentChars(val.replace(/\./g, " ")) == unaccentChars(value)){
                        oldOptions[valSelect].attr("selected", "selected");
                    }
                    else{
                        oldOptions[valSelect].removeAttr("selected");
                    }
                    if(typeof oldOptions[valSelect] != "undefined"){
                        oldOptions[valSelect].appendTo($("#names"));
                    }
                    n++;
                }
            }
            if(n == 0){
                if(typeof oldOptions["no"] != "undefined"){
                    oldOptions["no"].appendTo($("#names"));
                }
            }
            else{
                if(n == 1){
                    $("#names").children().attr("selected", "selected");
                }
                var no = $("#no").detach();
                if(no.length > 0){
                    oldOptions["no"] = no;
                }
            }
            var page = $("select option:selected").attr("name");
            if(typeof page == "undefined"){
                $("#button").attr("disabled", "disabled");
            }
            else{
                $("#button").removeAttr("disabled");
            }
        }
        
        function sortBy(type){
            var newAllPeople = Array();
            for(i = 0; i < allPeople.length; i++){
                var fullName = allPeople[i];
                var firstName = "";
                var lastName = "";
                if(fullName.indexOf(".") != -1){
                    if(type == "last" && sort == "first" || type == "first" && sort == "last"){
                        firstName = fullName.split(".")[0];
                        if(fullName.split(".").length == 2){
                            lastName = fullName.split(".")[1];
                        }
                        else{
                            lastName = fullName.substring(fullName.indexOf(".") + 1);
                        }
                    }
                    else{
                        if(fullName.split(".").length == 2){
                            firstName = fullName.split(".")[1];
                        }
                        else{
                            firstName = fullName.substring(fullName.indexOf(".") + 1);
                        }
                        lastName = fullName.split(".")[0];
                    }
                    newAllPeople[i] = lastName + "." + firstName;
                }
                else{
                    newAllPeople[i] = fullName;
                }
            }
            sort = type;
            allPeople = newAllPeople;
            allPeople.sort();
            filterResults($("#search").val());
        }
        
        $(document).ready(function(){
            var no = $("#no").detach();
            if(no.length > 0){
                oldOptions["no"] = no;
            }
            filterResults($("#search").val());
            
            $("#search").keypress(function(event) {
                if(event.keyCode == 40){        //DOWN
                    $.each($("#names").children(":selected").not("#no"), function(index, value){
                        if($(value).next().length > 0){
                            $(value).attr("selected", false);
                            $(value).next().attr("selected", true);
                        }
                    });
                }
                else if(event.keyCode == 38){   //UP
                    $.each($("#names").children(":selected").not("#no"), function(index, value){
                        if($(value).prev().length > 0){
                            $(value).attr("selected", false);
                            $(value).prev().attr("selected", true);
                        }
                    });
                }
            });
            
            $("#search").keyup(function(event) {
                if(event.keyCode == 13){
                    // Enter key was pressed
                    if($("#button").val() == "Go To User\'s Page"){
                        var page = $("select option:selected").attr("name");
                        if(typeof page != "undefined"){
                            document.location = "'.$wgServer.$wgScriptPath.'/index.php/" + page;
                        }
                    }
                }
                if(event.keyCode != 40 && event.keyCode != 38){
                    filterResults(this.value);
                }
            });
            sortBy("first");
            
            $("#button").click(function(){
                if($("#button").val() == "Go To User\'s Page"){
                    var page = $("select option:selected").attr("name");
                    if(typeof page != "undefined"){
                        document.location = "'.$wgServer.$wgScriptPath.'/index.php/" + page;
                    }
                }
            });
            
            $("select").change(function(){
                var page = $("select option:selected").attr("name");
                if(typeof page == "undefined"){
                    $("#button").attr("disabled", "disabled");
                }
                else{
                    $("#button").removeAttr("disabled");
                }
            });
        });
	    </script>');
	    
	    $wgOut->addHTML("<span id='pageDescription'>Select a user from the list below, and then click the 'Go To User&#39;s Page' button.  You can filter out the selection box by searching a name, user role, or project below.</span><table>
	                        <tr><td>
	                            <a href='javascript:sortBy(\"first\");'>Sort by First Name</a> | <a href='javascript:sortBy(\"last\");'>Sort by Last Name</a><br />
	                            <b>Search:</b> <input style='width:100%;' id='search' type='text' onKeyUp='filterResults(this.value);' />
	                        </td></tr>
	                        <tr><td>
	                        <form id='mainForm' action='$wgServer$wgScriptPath/index.php/Special:EditMember' method='post'>
	                            <select id='names' name='name' size='10' style='width:100%'>
	                                <option id='no' disabled>Search did not match anyone</option>\n");
	    foreach($allPeople as $person){
	        $projects = $person->getProjects();
	        $roles = $person->getRoles();
	        $projs = array();
	        foreach($projects as $project){
	            $projs[] = $project->getName();
	        }
	        $wgOut->addHTML("<option class='".implode(" ", $projs)."' name='{$person->getName()}' id='".unaccentChars(str_replace(".", "", $person->getName()))."'>".str_replace(".", " ", $person->getNameForForms())."</option>\n");
	    }
	    $wgOut->addHTML("</select>
	            </td></tr>
	            <tr><td>
	        <input type='button' id='button' name='next' value='Impersonate' />
	    </form></td></tr></table>");
	}
	
	static function createDelegateLink(&$toolbox){
        global $wgImpersonating, $wgDelegating, $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if(!$wgImpersonating && !$wgDelegating && count($me->getDelegates()) > 0){
            $link = TabUtils::createToolboxLink("Delegate", "$wgServer$wgScriptPath/index.php/Special:Impersonate");
            $toolbox['Other']['links'][] = $link;
        }
        else if(!$wgImpersonating && !$wgDelegating && ($me->isRoleAtLeast(STAFF) || $me->isRole(SD))){
            $link = TabUtils::createToolboxLink("Impersonate", "$wgServer$wgScriptPath/index.php/Special:Impersonate");
            $toolbox['Other']['links'][] = $link;
        }
        return true;
    }
	
}

?>
