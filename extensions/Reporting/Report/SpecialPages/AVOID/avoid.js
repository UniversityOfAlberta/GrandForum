$(document).ready(function(){

    $('#bodyContent').append("<div id='avoidButtons' class='program-body'></div>");
    
    if($("h1").text() == "Please Login" || $("h1").text() == "Login required"){
        // For HAA
        $("#mw-returnto").hide();
        $("#avoidButtons").hide();
    }
    
    $('#avoidButtons').append("<a id='scrollToTop' class='program-button' style='min-width: 14em;margin-left:5px;margin-right:5px;' href='#'><en>Top of Page</en><fr>Haut de la page</fr></a>");
    if(me.isLoggedIn()){
        var selected = $(".top-nav-element.selected a");
        if(selected.text().indexOf("My Profile") == -1){
            $('#avoidButtons').append("<a class='program-button' style='min-width: 14em;margin-left:5px;margin-right:5px;' href='" + wgServer + wgScriptPath + "/index.php/Special:AVOIDDashboard'><en>Back to My Profile</en><fr>Retour à Mon Profil</fr></a>");
        }
        if(selected.text() != "" && selected.text().indexOf("My Profile") == -1){
            $('#avoidButtons').append("<a class='program-button' style='min-width: 14em;margin-left:5px;margin-right:5px;' href='" + selected.attr('href') + "'><en>Back to</en><fr>Retour à</fr> " + selected.html() + "</a>");
        }
    }
    
    if($('.top-nav-element.selected').text().indexOf("Manager") != -1 || $('.top-nav-element.selected').text().trim() == "Assessor"){
        $("#submenu").show();
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
    
    $("#wgMessages").css("font-size", "1.5em")
                    .css("line-height", "1.5em");
    
    _.defer(function(){
        $("div#reportIssueDialog").closest(".ui-dialog").addClass("program-body");
        $("div#contactUsDialog").closest(".ui-dialog").addClass("program-body");
        $("div#helpDialog").closest(".ui-dialog").addClass("program-body");
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

    // Gamification
    $(document).on("mousemove", function(e){
        var x = e.originalEvent.clientX;
        var y = e.originalEvent.clientY;
        var pos = $("#achievementContainer").position();
        var width = $("#achievementContainer").outerWidth();
        var height = $("#achievementContainer").outerHeight();
        
        if(x >= pos.left && x <= pos.left + width &&
           y >= pos.top  && y <= pos.top  + height){
            $("#achievement").addClass("hover");
        }
        else{
            $("#achievement").removeClass("hover");
        }
    });
    
    setInterval(function(){
        if(document.hasFocus()){
            if($.cookie('gamification') != undefined){
                if($("#achievementContainer").css("opacity") == 0){
                    var gamification = JSON.parse($.cookie('gamification'));
                    var achievement = gamification.shift();
                    if(achievement != null){
                        $("#achievementPoints").text(achievement.points);
                        $("#achievementText").text(achievement.text);
                        showAchievement();
                        $.cookie('gamification', JSON.stringify(gamification), {path: wgScriptPath});
                        setTimeout(function(){
                            hideAchievement();
                        }, 5000);
                    }
                }
            }
        }
    }, 250);
});

function showAchievement(){
    $("#achievementContainer").animate({opacity: 1, right: '20px'}, 250);
    document.getElementById("ding").play();
}

function hideAchievement(){
    $("#achievementContainer").animate({opacity: 0, right: '-5em'}, 250);
}
