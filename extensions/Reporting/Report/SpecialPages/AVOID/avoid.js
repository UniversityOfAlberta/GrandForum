$(document).ready(function(){
    if($('.top-nav-element.selected').text().trim() == "Manager"){
        $("#submenu").show();
    }

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
    
    $(".members-only a").each(function(i, el){
        var text = $(el).text();
        $(el).replaceWith("<span>" + text + "</span>");
    });
    $(".members-only").removeAttr("href");
    $(".members-only *").off('click');
    $(".members-only").append("<div class='members-only-overlay'><span style='background: #FFF8;display: inline-block;border-radius: 0.5em;padding: 0.25em;box-shadow: 0 0 12px #0008;'>Members Only<br /><span style='font-size: 0.75em;line-height: 1em !important;display: inline-block;'><a class='becomeMember' href='#'>Click</a> to become a member for free.</span></span></div>");
    
    $(".becomeMember").click(function(){
        $("#becomeMemberDialog").dialog({
            width: 'auto',
            resizable: false,
            draggable: false,
            modal: true
        });
        $("#becomeMemberDialog a").blur();
        $('.ui-dialog').addClass('program-body')
        $(window).resize();
    });
    
    $("#becomeMember").click(function(){
        var id = _.first(_.pluck(_.filter(me.get('roles'), function(role){ return role.role == "Provider"; }), 'id'));
        var role = new Role({id: id, name: CI});
        role.save(null, {
            success: function(){
                document.location = wgServer + wgScriptPath;
            },
            error: function(){
                addError("There was a problem becoming a full member.", false, "#memberMessages");
            }
        });
    });
    
    $(window).resize(function(){
        if($('#becomeMemberDialog').is(':visible')){
            $('#becomeMemberDialog').dialog({
                width: 'auto'
            });
            $('#becomeMemberDialog').dialog({
                position: { 'my': 'center', 'at': 'center' }
            });
        }
    });
});
