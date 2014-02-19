$(document).ready(function(){
    $.each($("a.reportTab"), function(index, val){
        var href = $(this).attr('href');
        $(this).removeAttr('href');
        $(this).click(function(){
            submitForm(href);
        });
    });
});

function submitForm(newUrl){
    saveAll(function(){
        window.location = newUrl;
    });
}
