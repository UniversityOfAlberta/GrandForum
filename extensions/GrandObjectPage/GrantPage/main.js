// The main program flow starts here
main.bind('change:title', function(){
    $("#pageTitle").append("&nbsp;<span class='clicktooltip' title=''>&#9432;</span>");
    $("#pageTitle .clicktooltip").attr('title', $('#instructions').html());
    $("#pageTitle .clicktooltip").qtip({
        position: {
            adjust: {
                x: -($("#pageTitle .clicktooltip").width()/25),
                y: -($("#pageTitle .clicktooltip").height()/2)
            }
        },
        style: {
            classes: "instructions-qtip"
        },
        show: 'click',
        hide: 'click unfocus'
    });
});
