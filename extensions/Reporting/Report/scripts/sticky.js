var stickyInterval = null;
var focusTextareas = true;

function sticky(response, postId, stickies){
    // Add Document
    response = response.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
    $('#pdfHTML').html(response.replace(".pdfnodisplay", ".pdfnodisplay_removed"));
    $('#pdfHTML .pdfnodisplay').remove();
    // Add Word Spans
    (function (count) {
      'use strict';
      (function wrap(el) {
        $(el).filter(':not(script)').contents().each(function () {
          // Node.* won't work in IE < 9, use `1`
          if (this.nodeType === Node.ELEMENT_NODE) {
            wrap(this);
          // and `3` respectively
          } else if (this.nodeType === Node.TEXT_NODE && !this.nodeValue.match(/^\s+$/)) {
            if($(this).closest('a').length == 0 && $(this).closest('h1,h2,h3,h4,h5,h6,h7,.header').length > 0){
                $(this).replaceWith($.map(this.nodeValue.split(/(\S+)/), function (w) {
                  return w.match(/^\s*$/) ? document.createTextNode(w) : $('<span>', {id: "sticky" + (count = count + 1), text: w, class: 'word'}).get();
                }));
            }
          }
        });
      }('#pdfHTML #pdfBody'));
    }(0));
    
    $('#pdfHTML #pdfBody .word').each(function(i, el){
        var id = $(el).attr('id').replace("sticky", "");
        if(stickies[id] != undefined && 
           stickies[id] != ''){
            // Create the Sticky
            var head = $(this).text();
            var text = stickies[id].replace(/</g, '&lt;').replace(/\>/g, '&gt;');
            $(el).append('<div class="sticky" style="display:none;"><b>' + head + '</b><a class="X">X</a><br /><textarea name="' + postId + '[' + id + ']" placeholder="Enter Annotation...">' + text + '</textarea></div>');
            $(el).addClass('highlighted');
        }
    });
    
    $('#pdfHTML #pdfBody .word').click(function(e){
        if(e.target == this && $(this).closest('a').length == 0){
            if($('div', this).length == 0){
                // Create the Sticky
                var head = $(this).text();
                var id = $(this).attr('id').replace("sticky", "");
                $(this).append('<div class="sticky" style="display:none;"><b>' + head + '</b><a class="X">X</a><br /><textarea name="' + postId + '[' + id + ']" placeholder="Enter Annotation..."></textarea></div>');
            }
            // Show the Sticky
            $('div', this).slideDown(200);
            if(focusTextareas){
                $('textarea', this).focus();
            }
            $(this).addClass('highlighted');
        }
    });
    
    $('#showAllStickies').click(function(){
        focusTextareas = false;
        $($('#pdfHTML #pdfBody .highlighted').get().reverse()).click();
        focusTextareas = true;
    });
    
    $('#hideAllStickies').click(function(){
        $($('#pdfHTML #pdfBody .sticky .X').get().reverse()).click();
    });
    
    $(document).on('click', '.sticky .X', function(){
        $(this).closest('.sticky').slideUp(200, function(){
            // Hide the Sticky
            if($('textarea', this).val() == ''){
                // Delete the Sticky (if contents are empty)
                $(this).closest('.word').removeClass('highlighted');
            }
        });
    });
    
    $(document).on('click', '.sticky', function(){
        // Make sure the one that is clicked shows up on top
        $('.sticky').css('z-index', 1000);
        $(this).css('z-index', 1001);
    });
    
    if(stickyInterval != null){
        clearInterval(stickyInterval);
    }
    
    stickyInterval = setInterval(function(){
        var docWidth = $(document).width() - 91;
        $('.sticky:visible').each(function(i, el){
            var left = parseInt($(el).css('left'));
            var offsetLeft = $(el).offset().left;
            var outerWidth = $(el).outerWidth();
            if(((offsetLeft - left) + outerWidth) > docWidth){
                var difference = ((offsetLeft - left) + outerWidth) - docWidth;
                $(el).css('left', -Math.round(difference));
            }
            else if(((offsetLeft - left) + outerWidth) < docWidth){
                $(el).css('left', 0);
            }
        });
    }, 33);
}
