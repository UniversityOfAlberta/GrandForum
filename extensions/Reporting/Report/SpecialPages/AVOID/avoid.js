$(document).ready(function(){
    $('#bodyContent').append("<div id='avoidButtons' class='program-body'></div>");
    
    $('#avoidButtons').append("<a id='scrollToTop' class='program-button' style='min-width: 14em;margin-left:5px;margin-right:5px;' href='#'>Top of Page</a>");
    if(me.isLoggedIn()){
        var selected = $(".top-nav-element.selected a");
        if(selected.text() != "My Profile"){
            $('#avoidButtons').append("<a class='program-button' style='min-width: 14em;margin-left:5px;margin-right:5px;' href='" + wgServer + wgScriptPath + "/index.php/Special:AVOIDDashboard'>Back to My Profile</a>");
        }
        if(selected.text() != "" && selected.text() != "My Profile"){
            $('#avoidButtons').append("<a class='program-button' style='min-width: 14em;margin-left:5px;margin-right:5px;' href='" + selected.attr('href') + "'>Back to " + selected.html() + "</a>");
        }
    }
    
    $('#scrollToTop').click(function(){
        $("#bodyContent").scrollTop(0);
        $('#scrollToTop').blur();
    });
});
