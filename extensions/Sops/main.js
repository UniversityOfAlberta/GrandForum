//main program flow starts here
$("head").append('<script type="text/javascript" src="'+wgServer+wgScriptPath+'/scripts/atd-jquery/csshttprequest.js"></script>');
$("head").append('<script type="text/javascript" src="'+wgServer+wgScriptPath+'/scripts/atd-jquery/jquery.atd.js"></script>');
$("head").append('<script type="text/javascript" src="'+wgServer+wgScriptPath+'/scripts/atd-jquery/jquery.atd.textarea.js"></script>');

$("head").append('<script type="text/javascript" src="'+wgServer+wgScriptPath+'/scripts/annotator-full.min.js"></script>');

$("head").append('<script type="text/javascript" src="'+wgServer+wgScriptPath+'/scripts/jquery.deserialize.js></script>');
$("head").append('<script type="text/javascript" src="'+wgServer+wgScriptPath+'/scripts/select2/js/select2.js"></script>');

//$("head").append('<script type="text/javascript" src="/scripts/jquery.facets.js></script>');


// Adapted from a category plugin for annotatorjs written in coffee-script by Michael Widner
// https://github.com/PoeticMediaLab/Annotator-Categories

var uniqueFilter = function(item, i, ar){ return ar.indexOf(item) === i; }; // remove duplicates in array filter function
var bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; },
  extend = function(child, parent) { for (var key in parent) { if (hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; },
  hasProp = {}.hasOwnProperty,
  indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

Annotator.Plugin.MyTags = (function(_super) {
  extend(MyTags, _super);

  function MyTags() {
    this.setAnnotationMyTags = bind(this.setAnnotationMyTags, this);
    this.updateField = bind(this.updateField, this);
    return MyTags.__super__.constructor.apply(this, arguments);
  }
  MyTags.prototype.options = {
    availableTags: [],
  };
  MyTags.prototype.events = {
    'annotationEditorShown': "updateField",
    'annotationEditorSubmit': "saveTags",
  };
  MyTags.prototype.field = null;
  MyTags.prototype.input = null;
  MyTags.prototype.pluginInit = function() {
    if (!Annotator.supported()) {
      return;
    }
    this.field = this.annotator.editor.addField({
      submit: this.setAnnotationMyTags
    });
    this.annotator.viewer.addField({
      load: this.updateViewer
    });
    return this.input = $(this.field).find(':input');
  };
  MyTags.prototype.updateField = function(field, annotation) {
    annotation.tags = annotation.tags || [];
    var allTags = annotation.tags.concat(this.options.availableTags).filter(uniqueFilter);
    var tagHTML = "<select id='annotator-tags-select' multiple>";
    for (var j = 0, len = allTags.length; j < len; j++) {
      var tag = allTags[j];
      tagHTML += '<option val="' + tag + '" ';
      if (annotation.tags.indexOf(tag) !== -1) {
        tagHTML += ' selected="selected" '
      }
      tagHTML += ' >';
      tagHTML += tag;
      tagHTML += '</option>';
    }
    tagHTML += '</select>';
    $(this.field).html(tagHTML);
    $(this.field).find("#annotator-tags-select").select2({
      multiple: true,
      tags: true,
      placeholder: 'choose multiple tags'
    });
    return this.input;
  };
  MyTags.prototype.setAnnotationMyTags = function(field, annotation) {
    var tags = [];
    $(this.field).find('select :selected').each(function(i, selected) {
      tags[i] = $(selected).text();
    });
    return annotation.tags = tags.filter(uniqueFilter);
  };
  MyTags.prototype.updateViewer = function(field, annotation) {
    field = $(field);
    if (annotation.tags && $.isArray(annotation.tags) && annotation.tags.length) {
      return field.addClass('annotator-tags').html(function() {
        var string;
        return string = $.map(annotation.tags, function(tag) {
          return '<span class="annotator-tag">' + Annotator.Util.escape(tag) + '</span>';
        }).join(' ');
      });
    } else {
      return field.remove();
    }
  };

  MyTags.prototype.saveTags = function(event, annotation){
    // debugger;
    //var tags = [];
    //console.log(annotation);
    //$(this.field).find(".select2-selection__choice").each(function(i){
    //  tags[i] = $(this).attr("title");
    //})
    //console.log(tags);
  }
    return MyTags;
})(Annotator.Plugin);//main program flow starts here
