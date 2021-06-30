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
    for(var i = 0; i < window.frames.length; i++){
        var frame = window.frames[i];
        frame.postMessage({projectUrl: "http://canadianglycomics.ca/projects/?project="}, "*");
    };
}, false);

function getUrlVars() {
var vars = {};
var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
vars[key] = value;
});
return vars;
}

function isNumeric(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}

var firstTab = getUrlVars()["tab"];
var firstPerson = getUrlVars()["person"];
if(firstTab == "" || firstTab == undefined){
    firstTab = "main-page";
}

var lastPage = firstTab;

var section = null;
var leftPart = null;
var rightPart = null;

function initSideBar(){
    section = jQuery(".back_button").closest("section");
    leftPart = jQuery(jQuery('.elementor-row > .elementor-element', section).get(0));
    rightPart = jQuery(jQuery('.elementor-row > .elementor-element', section).get(1));
    
    jQuery("#menu-members").html("");
    jQuery("#nav_menu-4").hide();
    
    jQuery("#" + firstTab).show();
    
    jQuery(".right-sidebar-wrapper").append("<div id='roles' class='custom-sidebar gdl-divider widget_nav_menu'>");
    jQuery(".right-sidebar-wrapper").append("<div id='committees' class='custom-sidebar gdl-divider widget_nav_menu'>");
    jQuery("#roles").append("<h3 class='custom-sidebar-title sidebar-title-color gdl-title'>Groups</h3>");
    jQuery("#roles").append("<div class='menu-members-container'>");
    jQuery("#roles > div.menu-members-container").append("<ul class='roleList' id='roles-members'>");
    addTab("#roles-members", "executive-leadership", "Executive Leadership");
    addTab("#roles-members", "network-investigators", "Network Investigators");
    addTab("#roles-members", "collaborators", "Collaborators");
    addTab("#roles-members", "administrative-centre", "Administrative Centre");
    jQuery("#committees").append("<h3 class='custom-sidebar-title sidebar-title-color gdl-title'>Committees</h3>");
    jQuery("#committees").append("<div class='menu-committees-container'>");
    jQuery("#committees > div.menu-committees-container").append("<ul class='roleList' id='committees-members'>");
    addTab("#committees-members", "bod",  "Board of Directors");
    addTab("#committees-members", "cc",   "Commercialization Committee");
    addTab("#committees-members", "exec", "Executive Committee");
    addTab("#committees-members", "fac",  "Finance and Audit Committee");
    addTab("#committees-members", "gta",  "GlycoNet Trainee Association");
    addTab("#committees-members", "nomc", "Nominating Committee");
    addTab("#committees-members", "sab",  "Scientific Advisory Board");
    addTab("#committees-members", "rmc",  "Research Management Committee");
    addTab("#committees-members", "etc",  "Training Committee");
                                   
    jQuery("ul.roleList li a").click(function(e){
        scroll(0,0);
        var id = jQuery(e.currentTarget).parent().attr('data-id');
        lastPage = id;
        jQuery(".outer_tab").hide();
        jQuery("#" + id).show();
        jQuery("#" + id + "_tab").show();
        jQuery(".back_button").hide();
        jQuery("iframe", section).each(function(i, el){
            this.contentWindow.location.replace('about:blank');
        });
        jQuery("iframe", section).hide();
        jQuery("img.throbber", section).hide();
    });
                                   
    jQuery.get("https://forum.glyconet.ca/index.php?action=api.university/current", function(response){
        jQuery(".right-sidebar-wrapper").append("<div id='unis' class='custom-sidebar gdl-divider widget_nav_menu'>");
        jQuery("#unis").append("<h3 class='custom-sidebar-title sidebar-title-color gdl-title'>Participating Institutions</h3>");
        jQuery("#unis").append("<div class='menu-members-container'>");
        jQuery("#unis > div.menu-members-container").append("<ul id='menu-members'>");
        for(id in response){
            var university = response[id];
            if(university.color != "#888888" && university.name != null){
                jQuery("#menu-members").append("<li class='menu-item menu-item-type-post_type menu-item-object-page' data-id='" + id + "'>" + 
                                               "<a style='cursor:pointer;'>" + university.name + "</a>" + 
                                               "</li>");
            }
        }
        jQuery(".right-sidebar-wrapper").height("auto");
        jQuery("#menu-members li a").click(function(e){
            // Click University
            scroll(0,0);
            jQuery(".outer_tab").hide();
            var id = jQuery(e.currentTarget).parent().attr('data-id');
            var university = response[id];
            lastPage = "universities";
            jQuery("#universities_header").show();
            jQuery("#universities_header").text(university.name);
            jQuery(".gdl-tabs").hide();
            jQuery(".gdl-tabs-content").hide();
            jQuery("#universities").show();
            jQuery("#universities_tab").show();
            initTab("NI,NFI,RMC,SAB,BOD,SD,ASD,Staff,Manager/" + university.name, "#universities", "", false, 4);
        });
        jQuery(".page-wrapper").css('min-height', jQuery(".right-sidebar-wrapper").height());
        if(isNumeric(firstTab)){
            jQuery("li[data-id=" + firstTab + "] a").click();
        }
    });
    jQuery(".back_button").click(function(){
        // Click Back Link
        jQuery(".back_button").hide();
        jQuery("#" + lastPage).show();
        jQuery("#" + lastPage + "_tab").show();
        jQuery("iframe", section).each(function(i, el){
            this.contentWindow.location.replace('about:blank');
        });
        jQuery("iframe", section).hide();
        jQuery("img.throbber", section).hide();
        
        //leftPart.animate({'width': "810px"}, function(){
            rightPart.show();
        //});
    });
}

function addTab(outerId, dataId, label){
    jQuery(outerId).append("<li class='menu-item menu-item-type-post_type menu-item-object-page' data-id='" + dataId + "'>" + 
                             "<a style='cursor:pointer;'>" + label + "</a>" + 
                           "</li>");
}

function initTab(role, selector, tabSelector, fields, cols){
    jQuery.get("https://forum.glyconet.ca/index.php?action=api.people/" + role.replace("&", "%26"), function(response){
        jQuery(selector + "_tab > div").empty();
        if(selector == "#administrative-centre"){
            // Special case for re-ordering
            var newPeople = [];
            for(id in response){
                var person = response[id];
                if(person.name == "Todd.Lowary"){
                    newPeople.splice(0, 0, person);
                }
                else if(person.name == "Elizabeth.Nanak"){
                    newPeople.splice(1, 0, person);
                }
                else {
                    newPeople.push(person);
                }
            }
            response = newPeople;
        }
        var j = 0;
        for(id in response){
            var person = response[id];
            if(person.name == 'Admin'){
                // Don't include Admin
                continue;
            }
            if(person.university == 'Unknown'){
                // Don't include incomplete people
                continue;
            }
            var html = "<div class='tshowcase-box ts-col_" + cols + "' id='" + id + "' data-id='" + person.id + "'>" + 
                       "<div class='tshowcase-inner-box'>" + 
                       "<div class='tshowcase-box-photo ts-rounded ts-white-border' style='height:105px;position:relative;cursor:pointer;'>" +
                       "<div class='overlay' style='width:80px;height:105px;background:#126480;opacity:0.2;display:none;position:absolute;top:0;left:0;'></div>" + 
                       "<img width='80' title='" + person.fullName + "' src='" + person.cachedPhoto + "' style='border-radius:0%;'>" +
                       "</div>" + 
                       "<div class='tshowcase-box-info ts-align-center'>" + 
                       "<div class='tshowcase-box-title'>" + person.fullName + "</div>";
            for(i in fields){
                var field = fields[i];
                var content = person[field];
                if(field == 'email' && content != ""){
                    content = "<i class='fa fa-envelope'></i>&nbsp;<a href='mailto:" + content + "'>" + content + "</a>";
                }
                else if(field == 'phone' && content != ""){
                    content = "<i class='fa fa-phone-square'></i>&nbsp;" + content;
                }
                html += "<div class='tshowcase-box-details'>" + content + "</div>";
            }
            html +=    "</div></div></div>";
            j++;
            if(j % cols == 0){
                html += "<br>";
            }
            jQuery(selector + "_tab > div").append(html);
        }
        
        jQuery(selector + "_tab .tshowcase-box-photo").hover(function(){
            // Mouseover
            jQuery(".overlay", jQuery(this)).stop().fadeIn();
        }, function(){
            // Mouseout
            jQuery(".overlay", jQuery(this)).stop().fadeOut();
        });
        
        jQuery(selector + "_tab .tshowcase-box-photo").click(function(e){
            // Click Profile
            scroll(0,0);
            var id = jQuery(e.currentTarget).parent().parent().attr('id');
            var person = response[id];
            if(firstPerson == "" || firstPerson == undefined){
                jQuery(selector + "_tab").fadeOut();
                jQuery(selector + "_tab > h1").fadeOut();
                
                rightPart.hide();
                //leftPart.animate({width: section.width() + "px"});
            }
            else{
                jQuery(selector + "_tab").hide();
                jQuery(selector + "_tab > h1").hide();
                
                rightPart.hide();
                //leftPart.animate({width: section.width() + "px"}, 0);
            }
            jQuery("iframe").closest(".elementor-column").show();
            //jQuery(".gdl-page-item > div").width("100%");
            jQuery("iframe").each(function(i, el){
                this.contentWindow.location.replace('about:blank');
            });
            jQuery("img.throbber").show();
            jQuery("iframe" + selector + "_frame")[0].contentWindow.location.replace(person.url + '?embed&font=Lato,Arial,Verdana');
            jQuery("iframe" + selector + "_frame").show();
            if(firstPerson == "" || firstPerson == undefined){
                jQuery(".back_button").show();
            }
        });
        
        jQuery("iframe" + selector + "_frame").load(function(){
            scroll(0,0);
        });
        
        if(firstPerson != "" && firstPerson != undefined){
            jQuery("#" + firstTab + " " + selector + "_tab div[data-id=" + firstPerson + "] .tshowcase-box-photo").click();
        }
    });
}

jQuery(".page-wrapper").css('min-height', 500);
jQuery(document).ready(initSideBar);
initTab("SD,BOD Chair,ADCP,ASD,Manager", "#executive-leadership", "tab-0", ['position','university'], 4);
initTab("BOD", "#bod", "tab-4", ['position','university'], 4);
initTab("SAB", "#sab", "tab-5", ['position','university'], 4);
initTab("RMC", "#rmc", "tab-6", ['position','university'], 4);
initTab("NI,NFI", "#network-investigators", "tab-1", ['university'], 4);
initTab("Collaborator", "#collaborators", "tab-2", ['university'], 4);
initTab("SD,Staff,Manager", "#administrative-centre", "tab-3", ['position','university', 'phone', 'email'], 4);
initTab("GTA", "#gta", "tab-7", ['position','university'], 4);
initTab("CC", "#cc", "tab-8", ['position','university'], 4);
initTab("EXEC", "#exec", "tab-9", ['position','university'], 4);
initTab("FAC", "#fac", "tab-10", ['position','university'], 4);
initTab("NOMC", "#nomc", "tab-11", ['position','university'], 4);
initTab("ETC", "#etc", "tab-12", ['position','university'], 4);
