$(document).ready(function(){
    var html = $("ul.top-nav a").clone();
    $("#allTabsDropdown").html(html);

    $("#allTabs").click(function(){
        $("#allTabsDropdown").slideToggle(300);
    });
    
    $(document).click(function(){
        if(!$("#allTabsDropdown").is(":animated")){
            $("#allTabsDropdown").slideUp(300);
        }
    });
});
