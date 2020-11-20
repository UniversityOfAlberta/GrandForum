(function( $ ) {
    $.widget( "custom.combobox", {
      _create: function() {
        var interval = null;
        var createFn = function(){
            if(this.element.is(":visible") || this.element.css('width') != '0px'){
                var next = this.element.next();
                if(next.hasClass('custom-combobox')){
                    next.remove();
                }
                this.wrapper = $( "<span>" )
                  .addClass( "custom-combobox" )
                  .insertAfter( this.element );
                this.element.addClass('combobox');
                this.element.hide();
                this._createAutocomplete();
                this._createShowAllButton();
                if($(this.element).attr('disabled')){
                    this.input.prop('disabled', 'disabled');
                    $("a", $(this.wrapper)).addClass('disabledButton').unbind().click(function(e){
                        e.preventDefault();
                    });
                }
                clearInterval(interval);
                interval = null;
            }
        }.bind(this);
        if(this.element.is(":visible") || this.element.css('width') != '0px'){
            createFn();
        }
        else{
            var interval = setInterval(createFn, 10);
        }
      },
 
      _createAutocomplete: function() {
        var selected = this.element.children( ":selected" ),
          value = selected.val() ? selected.text() : "";
        var width = $(this.element).width() + 10;
        this.input = $( "<input>" )
          .appendTo( this.wrapper )
          .val( value )
          .attr( "title", "" )
          .attr("id", "combo_" + this.element.attr('name'))
          .width(width)
          .addClass( "custom-combobox-input " +this.uni)
          .autocomplete({
            delay: 0,
            minLength: 0,
            source: $.proxy( this, "_source" )
          })
          .click(function(){
            $(this).select();
          });
        this.invis = $("<input>")
            .appendTo(this.wrapper)
            .attr('value', value)
            .hide()
            .attr('name', this.element.attr('name'));
 
        var invis = this.invis;
        var element = this.element;
 
        $(this.input).on('autocompleteselect', function(event, ui){
            ui.item.option.selected = true;
            $(this).trigger( "select", event, {
              item: ui.item.option
            });
            $(invis).attr('value', ui.item.option.value);
            $("option", element).prop('selected', false);
            $("option", element).filter(function() {
                //may want to use $.trim in here
                return $(this).text().trim() == ui.item.option.value.trim(); 
            }).prop('selected', true);
            element.trigger('change');
        });
        
        $(this.input).on('keyup', function(event, ui){
            var found = false;
            _.each($(element).children("option"), function(o){
                if(o.innerHTML == $(event.target).val()){
                    found = o.value;
                }
            });
            if(!found){
                $(invis).attr('value', $(event.target).val());
                var option = $("<option>");
                option.addClass('extra');
                option.html($(event.target).val());
                option.prop('selected', true);
                $("option.extra", element).remove();
                element.append(option);
            }
            else{
                $(invis).attr('value', found);
                $("option", element).prop('selected', false);
                $("option", element).filter(function() {
                    //may want to use $.trim in here
                    return $(this).text().trim() == found.trim(); 
                }).prop('selected', true);
            }
            element.trigger('change');
        });
        
        $(this.input).on('change', function(event, ui){
            var found = false;
            _.each($(element).children("option"), function(o){
                if(o.innerHTML == $(event.target).val()){
                    found = o.value;
                }
            });
            if(!found){
                $(invis).attr('value', $(event.target).val());
                var option = $("<option>");
                option.addClass('extra');
                option.html($(event.target).val());
                option.prop('selected', true);
                $("option.extra", element).remove();
                element.append(option);
            }
            else{
                $(invis).attr('value', found);
                $("option", element).prop('selected', false);
                $("option", element).filter(function() {
                    //may want to use $.trim in here
                    return $(this).text().trim() == found.trim(); 
                }).prop('selected', true);
            }
            element.trigger('change');
        });
        $(this.input).trigger('change');
      },
 
      _createShowAllButton: function() {
        var input = this.input,
          wasOpen = false;
 
        $("<a>")
          .attr( "tabIndex", -1 )
          .attr( "title", "Show All Items" )
          .appendTo( this.wrapper )
          .removeClass( "ui-corner-all" )
          .html("&#9662;")
          .addClass( "custom-combobox-toggle button" )
          .mousedown(function() {
            wasOpen = input.autocomplete( "widget" ).is( ":visible" );
          })
          .click(function() {
            input.focus();
 
            // Close if already visible
            if ( wasOpen ) {
              return;
            }
 
            // Pass empty string as value to search for, displaying all results
            input.autocomplete( "search", "" );
          });
      },
 
      _source: function( request, response ) {
        var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
        response( this.element.children( "option" ).map(function() {
          var text = $( this ).text();
          if ( this.value && ( !request.term || matcher.test(text) ) )
            return {
              label: text,
              value: text,
              option: this
            };
        }) );
      },
 
      _destroy: function() {
        this.wrapper.remove();
        this.element.show();
      }
    });
  })( jQuery );
