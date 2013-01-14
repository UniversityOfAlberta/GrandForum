(function( $ ){

  $.fn.dropdown = function(options) {
    
    var title = (typeof options.title != 'undefined') ? options.title : 'DropDown';
    var width = (typeof options.width != 'undefined') ? options.width : '150px';
    
    var lis = $('li', $(this));
    $(lis).addClass('action');
    $(lis).css('display', 'block');
    
    $(this).append("<div class='actions' />");
    var divActions = $('div.actions' ,$(this));
    $(divActions).css('min-width', width);
    
    $(divActions).css('right', '1px');
    $(lis).appendTo($(divActions));
    $(this).append("<li class='actions'><a>" + title + "</a></li>");
    
    $(divActions).append("<img class='dropdowntop' src='../skins/dropdowntop.png' />");
    var dropdownTop = $('.dropdowntop', $(this));
    var that = this;
    $('li.actions', $(this)).click(function(e){
        $("div.actions").fadeOut(250); // Remove all other dropdowns
        e.stopPropagation();
        $(dropdownTop).css('position', 'absolute');
        $(dropdownTop).css('top', -5);
        $(dropdownTop).css('right', Math.ceil(($(this).width() + parseInt($(this).css('padding-left')) + parseInt($(this).css('padding-right')) - 7)/2));
        $(divActions).fadeToggle(250);
    });
    $(document).click(function(){
        $(divActions).fadeOut(250);
    });
  };
})( jQuery );
