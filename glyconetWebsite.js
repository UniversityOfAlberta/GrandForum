// Create IE + others compatible event handler
var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
var eventer = window[eventMethod];   
var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";   

// Listen to message from child window
eventer(messageEvent,function(e) {
    if(e.data > 0){
        jQuery("iframe").height(e.data);
    }
    jQuery("img.throbber").hide(); 
}, false);

function initSideBar(){
    jQuery("#menu-members").html("");
    jQuery.get("https://forum.glyconet.ca/index.php?action=api.university", function(response){
        for(id in response){
            var university = response[id];
            if(university.color != "#888888" && university.name != null){
                jQuery("#menu-members").append("<li class='menu-item menu-item-type-post_type menu-item-object-page' id='" + id + "'>" + 
                                               "<a style='cursor:pointer;'>" + university.name + "</a>" + 
                                               "</li>");
            }
        }
        jQuery(".right-sidebar-wrapper").height("auto");
        jQuery("#menu-members li a").click(function(e){
            // Click University
            var id = jQuery(e.currentTarget).parent().attr('id');
            var university = response[id];
            jQuery("#universities_header").show();
            jQuery("#universities_header").text(university.name);
            jQuery(".gdl-tabs").hide();
            jQuery(".gdl-tabs-content").hide();
            jQuery("#universities").show();
            initTab("PNI,CNI,RMC,BOD,Staff,Manager/" + university.name, "#universities", "", false);
            jQuery(".back_button").show();
        });
        jQuery(".page-wrapper").css('min-height', jQuery(".right-sidebar-wrapper").height());
    });
    jQuery(".back_button").click(function(){
        // Click Back Link
        jQuery(".back_button").hide();
        jQuery(".gdl-tabs").show();
        jQuery(".gdl-tabs-content").show();
        jQuery("#universities").hide();
        jQuery("#universities_frame").hide();
        jQuery(".gdl-page-content iframe").each(function(i, el){
            this.contentWindow.location.replace('about:blank');
        });
        jQuery(".gdl-page-content iframe").hide();
        jQuery("img.throbber").hide(); 
        jQuery("#universities_header").hide();
        jQuery(".gdl-page-float-left").animate({'width': "660px"});
        jQuery(".gdl-page-item").animate({'width': "660px"});
        jQuery(".gdl-page-item > div").animate({'width': "640px"}, function(){jQuery(".gdl-right-sidebar").show();});
    });
}

function initTab(role, selector, tabSelector, fields){
    jQuery.get("https://forum.glyconet.ca/index.php?action=api.people/" + role, function(response){
        jQuery(selector + " > div").empty();
        var j = 0;
        for(id in response){
            var person = response[id];
            if(person.name == 'Admin'){
                // Don't include Admin
                continue;
            }
            var html = "<div class='tshowcase-box ts-col_4' id='" + id + "'>" + 
                       "<div class='tshowcase-inner-box' style='cursor:pointer;'>" + 
                       "<div class='tshowcase-box-photo ts-rounded ts-white-border' style='height:105px;position:relative;'>" +
                       "<div class='overlay' style='width:80px;height:105px;background:#126480;opacity:0.2;display:none;position:absolute;top:0;left:0;'></div>" + 
                       "<img width='80' title='" + person.fullName + "' src='" + person.cachedPhoto + "' style='border-radius:0%;'>" +
                       "</div>" + 
                       "<div class='tshowcase-box-info ts-align-center'>" + 
                       "<div class='tshowcase-box-title'>" + person.fullName + "</div>";
            for(i in fields){
                var field = fields[i];
                html += "<div class='tshowcase-box-details'>" + person[field] + "</div>";
            }
            html +=    "</div></div></div>";
            j++;
            console.log(i % 4);
            if(j % 4 == 0){
                html += "<br>";
            }
            jQuery(selector + " > div").append(html);
        }
        
        jQuery(selector + " .tshowcase-box > div").hover(function(){
            // Mouseover
            jQuery(".overlay", jQuery(this)).stop().fadeIn();
        }, function(){
            // Mouseout
            jQuery(".overlay", jQuery(this)).stop().fadeOut();
        });
        
        jQuery(selector + " .tshowcase-box > div").click(function(e){
            // Click Profile
            var id = jQuery(e.currentTarget).parent().attr('id');
            var person = response[id];
            jQuery(selector).fadeOut();
            jQuery(selector + " > h1").fadeOut();
            jQuery(".gdl-right-sidebar").hide();
            jQuery(".gdl-page-float-left").animate({width: jQuery(".page-wrapper").width() + "px"});
            jQuery(".gdl-page-item").width("100%");
            jQuery(".gdl-page-item > div").width("100%");
            jQuery(".gdl-page-content iframe").each(function(i, el){
                this.contentWindow.location.replace('about:blank');
            });
            jQuery("img.throbber").show();
            jQuery("iframe" + selector + "_frame")[0].contentWindow.location.replace(person.url + '?embed&amp;font=Arial');
            jQuery("iframe" + selector + "_frame").show();
        });
        if(tabSelector != ""){
            jQuery("a[data-href=" + tabSelector + "]").click(function(e){
                // Click Tab
                jQuery(selector).fadeIn();
                jQuery(selector + " > h1").fadeIn();
                jQuery(".gdl-page-content iframe").each(function(i, el){
                    this.contentWindow.location.replace('about:blank');
                });
                jQuery("img.throbber").hide(); 
                jQuery(".gdl-page-content iframe").hide();
                jQuery(".gdl-page-float-left").animate({'width': "660px"});
                jQuery(".gdl-page-item").animate({'width': "660px"});
                jQuery(".gdl-page-item > div").animate({'width': "640px"}, function(){jQuery(".gdl-right-sidebar").show();});
            });
        }
    });
}

jQuery(".page-wrapper").css('min-height', 500);
jQuery(document).ready(initSideBar);
initTab("SD,BOD Chair,ASD,Manager", "#executive-leadership", "tab-0", ['university','position']);
initTab("PNI", "#network-investigators", "tab-1", ['university']);
initTab("CNI", "#collaborators", "tab-2", ['university']);
initTab("SD,Staff,Manager", "#administrative-centre", "tab-3", ['university']);
