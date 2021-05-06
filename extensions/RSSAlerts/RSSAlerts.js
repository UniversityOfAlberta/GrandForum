$('#new_feed').click(function(){
    $('#new_feed').parent().hide();
    $('#new_feed_div').show();
});

$("#feeds").DataTable({
    'aLengthMenu': [[-1], ['All']]
});

$("#articles").DataTable({
    'aLengthMenu': [[-1], ['All']]
});
