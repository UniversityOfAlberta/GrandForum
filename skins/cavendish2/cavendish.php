<?php
/**
 * Mozilla cavendish theme
 * Modified by DaSch for MW 1.15 and WeCoWi
 * Skin Version 0.9
 *
 * Loosely based on the cavendish style by Gabriel Wicke
 *
 * @todo document
 * @package MediaWiki
 * @subpackage Skins
 */
$wgValidSkinNames['cavendish2'] = 'cavendish2';
$wgAutoloadClasses['SkinCavendish2'] = __DIR__ . '/cavendish.php';

if( !defined( 'MEDIAWIKI' ) )
    die();

/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 * @todo document
 * @package MediaWiki
 * @subpackage Skins
 */
class SkinCavendish2 extends SkinTemplate {
    /** Using cavendish. */
    function initPage( $out ) {
        SkinTemplate::initPage($out);
        $this->skinname  = 'cavendish2';
        $this->stylename = 'cavendish2';
        $this->template  = 'CavendishTemplate2';
    }
}
    
class CavendishTemplate2 extends QuickTemplate {
    /**
     * Template filter callback for cavendish skin.
     * Takes an associative array of data set from a SkinTemplate-based
     * class, and a wrapper for MediaWiki's localization database, and
     * outputs a formatted page.
     *
     * @access private
     */
    function execute() {
        global $wgRequest, $wgServer, $wgScriptPath, $wgOut, $wgLogo, $wgTitle, $wgUser, $wgMessage, $wgImpersonating, $wgDelegating, $wgTitle, $config, $wgLang;
        $this->skin = $skin = $this->data['skin'];
        $action = $wgRequest->getText( 'action' );

        // Suppress warnings to prevent notices about missing indexes in $this->data
        wfSuppressWarnings();
        
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php $this->text('lang') ?>" lang="<?php $this->text('lang') ?>" dir="<?php $this->text('dir') ?>">
    <head>
        <meta http-equiv="Content-Type" content="<?php $this->text('mimetype') ?>; charset=<?php $this->text('charset') ?>" />

        <title><?php $this->text('pagetitle') ?></title>
        <link type="image/x-icon" href="<?php echo $wgServer.$wgScriptPath.'/favicon.png'; ?>" rel="shortcut icon" />
        <link type="text/css" href="<?php $this->text('stylepath') ?>/smoothness/jquery-ui-1.8.21.custom.css" rel="Stylesheet" />
        <link type="text/css" href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/jquery.qtip.min.css" rel="Stylesheet" />
        <link type="text/css" href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/chosen/chosen.css.php" rel="Stylesheet" />
        <?php $this->html('csslinks') ?>

        <link rel="stylesheet" href="<?php $this->text('stylepath') ?>/common/shared.css" type="text/css" media="screen" />
        <link rel="stylesheet" href="<?php $this->text('stylepath') ?>/common/commonPrint.css" type="text/css" media="print" />
        <link rel="stylesheet" href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/print.css" type="text/css" media="print" />
        
        <link type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/DataTables/css/cavendish2/jquery.dataTables.css" rel="Stylesheet" />
        <link type="text/css" rel="stylesheet" href="<?php echo "$wgServer$wgScriptPath"; ?>/skins/simplePagination/simplePagination.css" />
        
        <style type="text/css" media="screen,projection">/*<![CDATA[*/ @import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/main.css"; /*]]>*/</style>
        <style type="text/css" media="screen,projection">/*<![CDATA[*/ @import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/extensions.css"; /*]]>*/</style>
        <style <?php if(empty($this->data['printable']) ) { ?>media="print"<?php } ?> type="text/css">/*<![CDATA[*/ @import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/print.css"; /*]]>*/</style>
        
        <link rel="stylesheet" type="text/css" media="print" href="<?php $this->text('stylepath') ?>/common/commonPrint.css" />
        <link type="text/css" href="<?php $this->text('stylepath') ?>/switcheroo/switcheroo.css" rel="Stylesheet" />
        <link type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/tagIt/css/jquery.tagit.css?<?php echo filemtime(dirname(__FILE__)."/../../scripts/tagIt/css/jquery.tagit.css"); ?>" rel="Stylesheet" />
        <link type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/tagIt/css/tagit.ui-zendesk.css?<?php echo filemtime(dirname(__FILE__)."/../../scripts/tagIt/css/tagit.ui-zendesk.css"); ?>" rel="Stylesheet" />
        <link type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jqueryDropdown/cavendish2/jquery.dropdown.css" rel="Stylesheet" />
        <link rel="stylesheet" type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/colorbox/colorbox.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/skins/cavendish2/highlights.css.php" />
        <link rel="stylesheet" type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/markitup/skins/markitup/style.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/markitup/sets/wiki/style.css" />
        <link type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/annotator.min.css" rel="Stylesheet" />
        <link type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/select2/css/select2.min.css" rel="Stylesheet" />
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/DataTables/css/cavendish2/fixedColumns.dataTables.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/DataTables/css/cavendish2/scroller.dataTables.css" />
        
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/date.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/inflection.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/to-title-case.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/countries.en.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/detectIE.js"></script>

        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.min.js?version=3.4.1"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.backwards.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery-ui.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.browser.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.cookie.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.resizeY.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.limit-1.2.source.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.multiLimit.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.combobox.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.chosen.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.caret.1.02.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery-ui.triggeredAutocomplete.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.filterByText.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.scrollTo-min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.md5.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jqueryDropdown/jquery.dropdown.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.reallyvisible.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.jsPlumb-min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/colorbox/jquery.colorbox-min.js"></script>   
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/DataTables/js/jquery.dataTables.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/DataTables/js/jquery.dataTables.scroller.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.flash.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.colVis.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/3.2.4/js/dataTables.fixedColumns.min.js"></script>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.qtip.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.forceNumeric.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.form.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/tabs.js"></script>
        
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/tinymce/tinymce.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/tinymce/combobox.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/tinymce/jquery.tinymce.min.js"></script>
        
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/tagIt/js/tag-it.min.js?<?php echo filemtime(dirname(__FILE__)."/../../scripts/tagIt/js/tag-it.min.js"); ?>"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/switcheroo.js"></script>
        <script language="javascript" type="text/javascript" src="https://maps.google.com/maps/api/js?&libraries=places"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/raphael.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/spinner.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/filter.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/autosave.js?<?php echo filemtime(dirname(__FILE__)."/../../scripts/autosave.js"); ?>"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/extensions/Messages/messages.js"></script>
        
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/d3.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/html2canvas.min.js"></script>
    
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/underscore-min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/backbone-min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/backbone-subviews.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/backbone-trackit.js"></script>
        <!--script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/backbone-relational-min.js"></script>-->
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.simplePagination.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/markitup/jquery.markitup.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/markitup/sets/wiki/set.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/extensions/Visualizations/RadarChart/radarchart/radarchart.js"></script>
 
        <script type='text/javascript'>
        
            $.ajaxSetup({ cache: false, 
                          data: {embed: <?php if(isset($_GET['embed']) && $_GET['embed'] != "false"){ echo "true"; } else { echo "false"; } ?>},
                          headers : { "cache-control": "no-cache" } 
                        });
        
            Backbone.emulateHTTP = true;
            Backbone.emulateJSON = true;
            
            Backbone.View.prototype.beforeRender = function(){};
            Backbone.View.prototype.afterRender = function(){
                $.each(this.$el.find('input[type=datepicker]'), function(index, val){
                    $(val).datepicker({
                        'dateFormat': $(val).attr('format'),
                        'changeMonth': true,
                        'changeYear': true,
                        'showOn': "both",
                        'buttonImage': "<?php echo $wgServer.$wgScriptPath; ?>/skins/calendar.gif",
                        'buttonImageOnly': true
                    });
                });
                this.$el.find('.tooltip').qtip({
                    position: {
                        adjust: {
                            x: -(this.$el.find('.tooltip').width()/25),
                            y: -(this.$el.find('.tooltip').height()/2)
                        }
                    },
                    show: {
                        delay: 500
                    }
                });
            };
            
            Backbone.View = (function(View) {
              // Define the new constructor
              Backbone.View = function(attributes, options) {
                // Your constructor logic here
                // ...
                _.bindAll(this, 'beforeRender', 'render', 'afterRender'); 
                var _this = this;
                this.render = _.wrap(this.render, function(render) {
                  _this.beforeRender();
                  var ret = render();
                  _this.afterRender(); 
                  return ret;
                }); 
                // Call the default constructor if you wish
                View.apply(this, arguments);
                // Add some callbacks
                
              };
              // Clone static properties
              _.extend(Backbone.View, View);
              // Clone prototype
              Backbone.View.prototype = (function(Prototype) {
                Prototype.prototype = View.prototype;
                return new Prototype;
              })(function() {});
              // Update constructor in prototype
              Backbone.View.prototype.constructor = Backbone.View;
              return Backbone.View;
            })(Backbone.View);
        </script>
        <?php echo $config->getValue("analyticsCode"); ?>

        <!-- Head Scripts -->
        <script type="text/javascript">
            var wgServer = "<?php echo $wgServer; ?>";
            var wgScriptPath = "<?php echo $wgScriptPath; ?>";
            var wgBreakFrames = "<?php echo $wgBreakFrames; ?>";
            var wgUserName = "<?php echo $wgUser->getName(); ?>";
            var wgLang = "<?php echo $wgLang->getCode(); ?>";
            var studyEnabled = "<?php echo $config->getValue("studyEnabled"); ?>";
            var hiddenEnabled = "<?php echo $config->getValue("hiddenEnabled"); ?>";

        </script>
        <?php echo $wgOut->getScript(); ?>
        <!-- site js -->
        <?php   if($this->data['jsvarurl']) { ?>
        <script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('jsvarurl') ?>"><!-- site js --></script>
        <?php   } ?>
        <!-- should appear here -->
        <?php   if($this->data['pagecss']) { ?>
                <style type="text/css"><?php $this->html('pagecss') ?></style>
        <?php   }
                if($this->data['usercss']) { ?>
                <style type="text/css"><?php $this->html('usercss') ?></style>
        <?php   }
                if($this->data['userjs']) { ?>
                <script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('userjs' ) ?>"></script>
        <?php   }
                if($this->data['userjsprev']) { ?>
                <script type="<?php $this->text('jsmimetype') ?>"><?php $this->html('userjsprev') ?></script>
        <?php   }
                if($this->data['trackbackhtml']) print $this->data['trackbackhtml']; ?>

        <?php createModels(); ?>
        <script type='text/javascript'>
        
            // Configs
            allowedRoles = <?php $me = Person::newFromWGUser(); echo json_encode($me->getAllowedRoles()); ?>;
            allowedProjects = <?php $me = Person::newFromWGUser(); echo json_encode($me->getAllowedProjects()); ?>;
            wgRoles = <?php global $wgAllRoles; echo json_encode($wgAllRoles); ?>;
            roleDefs = <?php echo json_encode($config->getValue('roleDefs')); ?>;
            
            <?php
                foreach($config->constants as $key => $value){
                    echo "{$key} = '{$value}';\n";
                }
            ?>
            
            skin = "<?php echo $config->getValue('skin'); ?>";
            projectPhase = <?php echo PROJECT_PHASE; ?>;
            projectsEnabled = <?php var_export($config->getValue('projectsEnabled')); ?>;
            networkName = "<?php echo $config->getValue('networkName'); ?>";
            extensions = <?php echo json_encode($config->getValue('extensions')); ?>;
            iconPath = "<?php echo $config->getValue('iconPath'); ?>";
            iconPathHighlighted = "<?php echo $config->getValue('iconPathHighlighted'); ?>";
            highlightColor = "<?php echo $config->getValue('highlightColor'); ?>";
            productsTerm = "<?php echo $config->getValue('productsTerm'); ?>";
            relationTypes = <?php echo json_encode($config->getValue('relationTypes')); ?>;
            boardMods = <?php echo json_encode($config->getValue('boardMods')); ?>;
        
            function isExtensionEnabled(ext){
                return (extensions.indexOf(ext) != -1);
            }
        
            me = new Person(
            <?php
                $me = Person::newFromWGUser();
                echo $me->toJSON();
            ?>
            );
            
            productStructure = <?php
                $structure = Product::structure();
                echo json_encode($structure);
            ?>;
            
            function changeImg(el, img){
                $(el).attr('src', img);
            }
            
            jQuery.fn.htmlClean = function() {
                this.contents().filter(function() {
                    if (this.nodeType != 3) {
                        $(this).htmlClean();
                        return false;
                    }
                    else {
                        this.textContent = $.trim(this.textContent);
                        return !/\S/.test(this.nodeValue);
                    }
                }).remove();
                return this;
            }
            
            function unaccentChars(str){
                if(str == undefined){
                    str = "";
                }
                var dict = {'Š':'S', 'š':'s', 'Ð':'Dj','Ž':'Z', 'ž':'z', 'À':'A', 'Á':'A', 'Â':'A', 'Ã':'A', 'Ä':'A',
                            'Å':'A', 'Æ':'A', 'Ç':'C', 'È':'E', 'É':'E', 'Ê':'E', 'Ë':'E', 'Ì':'I', 'Í':'I', 'Î':'I',
                            'Ï':'I', 'Ñ':'N', 'Ò':'O', 'Ó':'O', 'Ô':'O', 'Õ':'O', 'Ö':'O', 'Ø':'O', 'Ù':'U', 'Ú':'U',
                            'Û':'U', 'Ü':'U', 'Ý':'Y', 'Þ':'B', 'ß':'Ss','à':'a', 'á':'a', 'â':'a', 'ã':'a', 'ä':'a',
                            'å':'a', 'æ':'a', 'ç':'c', 'è':'e', 'é':'e', 'ê':'e', 'ë':'e', 'ì':'i', 'í':'i', 'î':'i',
                            'ï':'i', 'ð':'o', 'ñ':'n', 'ò':'o', 'ó':'o', 'ô':'o', 'õ':'o', 'ö':'o', 'ø':'o', 'ù':'u',
                            'ú':'u', 'û':'u', 'ý':'y', 'ý':'y', 'þ':'b', 'ÿ':'y', 'ƒ':'f', 'ü':'u'};
                return str.replace(/[^\w ]/g, function(char) {
                    return dict[char] || char;
                }).toLowerCase();
            }

            function setMinWidth(){
                $("body").css('min-width', '0');
                minWidth = parseInt($("#header ul").css('left')) +
                           parseInt($("#header ul").css('right')) +
                           parseInt($("body").css('margin-left')) +
                           parseInt($("body").css('margin-right'));
                $.each($("#header li"), function(index, val){
                    minWidth += $(this).width() + 
                                parseInt($(this).css('padding-left')) + 
                                parseInt($(this).css('padding-right')) +
                                parseInt($(this).css('margin-left')) + 
                                parseInt($(this).css('margin-right'));
                });
                $("html").css('min-width', minWidth);
            }
            
            function addAPIMessages(response){
                clearError();
                clearSuccess();
                errors = response.errors;
                messages = response.messages;
                for(i in errors){
                    addError(errors[i]);
                }
                for(i in messages){
                    addSuccess(messages[i]);
                }
            }
            
            function createDropDown(name, title, width){
                $('li.' + name).wrapAll('<ul class=\'' + name + '\'>');
                $('ul.' + name).wrapAll('<li class=\'invisible\'>');
                var selected = false;
                if($('li.' + name).filter('.selected').length >= 1){
                    selected = true;
                }
                $('div#submenu ul.' + name).dropdown({title: title,
                                                       width: width + 'px' 
                                                      });
                if(selected){
                    $('ul.' + name + ' > li').addClass('selected');
                    $('ul.' + name).imgDown();
                }
            }
            
            var sideToggled = $.cookie('sideToggled');
            if(sideToggled == undefined){
                sideToggled = 'out';
            }

            $(document).ready(function(){
                /*
                var ajax = null;
                $(document).ajaxComplete(function(e, xhr, settings) {
                    if(settings.url.indexOf("action=getUserMode") == -1){
                        if(ajax != null){
                            ajax.abort();
                        }
                        ajax = $.get("<?php echo $wgServer.$wgScriptPath; ?>/index.php?action=getUserMode&user=" + wgUserName, function(response){
                            if(response.mode == 'loggedOut'){
                                if($('#wgMessages .info').text() != response.message){
                                    clearInfo();
                                }
                                addInfo(response.message);
                            }
                            else if(response.mode == 'frozen'){
                                if($('#wgMessages .info').text() != response.message){
                                    clearInfo();
                                }
                                addInfo(response.message);
                            }
                            else if(response.mode == 'impersonating'){
                                if($('#wgMessages .info').text() != response.message){
                                    clearInfo();
                                }
                                addInfo(response.message);
                            }
                            else if(response.mode == 'differentUser'){
                                if($('#wgMessages .warning').text() != response.message){
                                    clearWarning();
                                }
                                addWarning(response.message);
                            }
                            else{
                                clearInfo();
                                clearWarning();
                            }
                        });
                    }
                });*/
                
                $('a.disabledButton').click(function(e){
                    e.preventDefault();
                });

                setMinWidth();
                $('.tooltip').qtip({
                    position: {
                        adjust: {
                            x: -($('.tooltip').width()/25),
                            y: -($('.tooltip').height()/2)
                        }
                    },
                    show: {
                        delay: 500
                    }
                });
                $('.menuTooltip').qtip({
                    position: {
                        my: 'top center',  // Position my top left...
                        at: 'bottom center', // at the bottom right of...
                    },
                    show: {
                        delay: 0
                    },
                    hide: {
                        delay: 100
                    },
                    style: {
                        classes: 'qtip-light'
                    }
                });
                $('.menuTooltipHTML').qtip({
                    content: {
                        text: function(){
                            return $("#" + $(this).attr('id') + "_template");
                        }
                    },
                    position: {
                        my: 'top center',  // Position my top left...
                        at: 'bottom center', // at the bottom right of...
                    },
                    show: {
                        delay: 0
                    },
                    style: {
                        classes: 'qtip-light'
                    },
                    hide: {
                          fixed: true,
                          delay: 100
                    }
                });
                
                $("textarea[name=wpTextbox1]").markItUp(myWikiSettings);
                
                $.each($('a.changeImg'), function(index, el){
                    if($(this).attr("name") != undefined){
                        var dark = '<?php echo "$wgServer$wgScriptPath"; ?>/' + iconPath + $(this).attr("name") + '.png';
                        var light = '<?php echo "$wgServer$wgScriptPath"; ?>/' + iconPathHighlighted + $(this).attr("name") + '.png';
                        
                        $(this).attr('onmouseover', "changeImg($('img:not(.overlay)', $(this)), '" + light + "')");
                        $(this).attr('onmouseout', "changeImg($('img:not(.overlay)', $(this)), '" + dark + "')");
                    }
                });
                
                if($("img.overlay")[0] != undefined){
                    var notificationOverlay = $("img.overlay")[0];
                    var delta = 0.05;
                    var opacity = 1;
                    setInterval(function(){
                        if(opacity <= 0 || opacity >= 1){
                            delta = -delta;
                        }
                        opacity += delta;
                        notificationOverlay.style.opacity = opacity;
                    }, 100);
                }
                
                $("#sideToggle").click(function(e, force){
                    $("#sideToggle").stop();
                    if((sideToggled == 'out' && force == null) || force == 'in'){
                        $("#sideToggle").html("&#12297;");
                        $("#side").animate({
                            'left': '-200px'
                        }, 200, 'swing');
                        $("#outerHeader").animate({
                            'left': '4'
                        }, 200, 'swing');
                        $("#bodyContent").animate({
                            'padding-left': '34px'
                        }, 200, 'swing', function(){
                            jsPlumb.repaintEverything();
                        });
                        sideToggled = 'in';
                        $.cookie('sideToggled', 'in', {expires: 30});
                    }
                    else{
                        $("#sideToggle").html("&#12296;");
                        $("#side").animate({
                            'left': '0px'
                        }, 200, 'swing');
                        $("#outerHeader").animate({
                            'left': '200px'
                        }, 200, 'swing');
                        $("#bodyContent").animate({
                            'padding-left': '230px'
                        }, 200, 'swing', function(){
                            jsPlumb.repaintEverything();
                        });
                        sideToggled = 'out';
                        $.cookie('sideToggled', 'out', {expires: 30});
                    }
                });
            });
        </script>
        <?php if(isExtensionEnabled('Shibboleth') && isset($_SERVER['uid'])){ ?>
            <script type="text/javascript">
                var logoutFn = function(redirect){
                    $.get(wgServer + wgScriptPath + '/index.php?clearSession', function(){
                        $("#logoutFrame").attr('src', "<?php echo $config->getValue('shibLogoutUrl'); ?>");
                        $("#logoutFrame").on('load', function(){
                            $.get(wgServer + wgScriptPath + '/index.php?clearSession', function(){
                                if(redirect){
                                    document.location = '<?php echo $wgServer.$wgScriptPath; ?>';
                                }
                            });
                        });
                    });
                }
                $(document).ready(function(){
                    $('#status_logout').removeAttr('href');
                    $('#status_logout').click(function(){
                        logoutFn(true);
                    });
                });
            </script>
        <?php } ?>
        <?php if(isset($_GET['embed'])){ ?>
            <style>
            
                html {
                    overflow: auto;
                    background: #FFFFFF;
                }
            
                body {
                    overflow: auto;
                    background:#FFFFFF;
                }
                
                #side {
                    display: none;
                }
                
                #topheader {
                    display: none;
                }
                
                #outerHeader {
                    display: none;
                }
                
                #footer {
                    display: none;
                }
                
                body, td, th, input, h1, h2, h3, h4, div {
                    font-family: <?php echo @$_GET['font']; ?> !important;
                }
                
                #mBody {
                    padding: 0;
                }
        
                #bodyContent {
                    left: 0;
                    right: 0;
                    top:0;
                    bottom: 0;
                    padding: 0;
                    border: none;
                    box-shadow: none;
                    margin: 0;
                    position: relative; 
                }
                
                #person .ui-tabs-nav {
                    display: none;
                }
                
                #bodyContent .ui-tabs-panel {
                    padding: 0;
                }
            </style>
            <script type="text/javascript">
                parent.postMessage(-1, "*");
                
                var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
                var eventer = window[eventMethod];   
                var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";   

                // Listen to message from parent window
                eventer(messageEvent,function(e) {
                    if(e.data.projectUrl != undefined){
                        $("a.projectUrl").attr('href', function(el){ return e.data.projectUrl + jQuery(this).attr('data-projectId')});
                        $("a.projectUrl").attr('target', '_parent');
                    }
                }, false);
                
                $(document).ready(function(){
                    $("a").attr("target", "");
                    var height = $("#bodyContent").height();
                    // Inform the parent about what iframe height should be
                    setInterval(function(){
                        height = $("#bodyContent").height();
                        parent.postMessage(height+5, "*");
                    }, 100);
                });
            </script>
        <?php
            header_remove("X-Frame-Options");
         } ?>
    </head>
<body 
<?php if($this->data['body_ondblclick']) { ?> ondblclick="<?php $this->text('body_ondblclick') ?>"<?php } ?>
<?php if($this->data['body_onload']) { ?> onload="<?php $this->text('body_onload') ?>"<?php } ?>
 class="mediawiki <?php $this->text('dir') ?> <?php $this->text('pageclass') ?> <?php $this->text('skinnameclass') ?>">
<?php if(isExtensionEnabled('Shibboleth') && isset($_SERVER['uid'])){ ?>
    <iframe id="logoutFrame" style="display:none;" src=""></iframe>
<?php } ?>
<div id="internal"></div>
<div id="container">
    <div id="topheader">
        <?php
            global $wgSitename, $notifications, $notificationFunctions, $config, $wgUser, $wgLang;
            if(count($notifications) == 0){
                foreach($notificationFunctions as $function){
                    call_user_func($function);
                }
            }
            echo "<div class='smallLogo'><a href='{$this->data['nav_urls']['mainpage']['href']}' title='$wgSitename'><img src='$wgServer$wgScriptPath/{$config->getValue('logo')}' /></a></div>";
            echo "<div class='search'><div id='globalSearch'></div></div>";
            if($wgUser->isLoggedIn()){
                echo "<div class='settings highlightsBackground2'>";
            }
            else{
                echo "<div class='settings highlightsBackground2' style='right:4px;border-bottom-right-radius:10px'>";
            }

            echo "<div style='display:none;' id='share_template'>";
            foreach($config->getValue("socialLinks") as $social => $link){
                $img = "";
                $text = "";
                switch($social){
                    case 'flickr':
                        $img = "glyphicons_social_35_flickr";
                        $text = "Flickr";
                        break;
                    case 'twitter':
                        $img = "glyphicons_social_31_twitter";
                        $text = "Twitter";
                        break;
                    case 'facebook':
                        $img = "glyphicons_social_30_facebook";
                        $text = "Facebook";
                        break;
                    case 'vimeo':
                        $img = "glyphicons_social_34_vimeo";
                        $text = "Vimeo";
                        break;
                    case 'linkedin':
                        $img = "glyphicons_social_17_linked_in";
                        $text = "LinkedIn";
                        break;
                    case 'youtube':
                        $img = "glyphicons_social_22_youtube";
                        $text = "YouTube";
                        break;
                }
                echo "<a class='changeImg highlights-text-hover' style='white-space:nowrap;' name='$img' href='$link' target='_blank'>
                            <img src='$wgServer$wgScriptPath/{$config->getValue('iconPath')}$img.png' />&nbsp;$text
                      </a>";
            }
            echo "</div>";
            /*
            echo "<a id='status_help_faq' name='question_mark_8x16' class='menuTooltip changeImg' title='Help/FAQ' href='$wgServer$wgScriptPath/index.php/Help:Contents'><img src='$wgServer$wgScriptPath/{$config->getValue('iconPath')}question_mark_8x16.png' /></a>";
            */
            if(count($config->getValue("socialLinks")) > 0){
                echo "<a id='share' style='cursor:pointer;' name='share_16x16' class='menuTooltipHTML changeImg'><img src='$wgServer$wgScriptPath/{$config->getValue('iconPath')}share_16x16.png' />&nbsp;▼</a>";
            }
            if($wgUser->isLoggedIn()){
                $p = Person::newFromId($wgUser->getId());
                
                $smallNotificationText = "";
                if(count($notifications) > 0){
                    $notificationText = " (".count($notifications).")";
                    $smallNotificationText = "<img class='overlay' style='margin-left:-16px;' src='$wgServer$wgScriptPath/{$config->getValue('iconPath')}mail_16x12_red.png' />";
                }
                echo "<a id='status_notifications' name='mail_16x12' class='menuTooltip changeImg' title='Notifications$notificationText' href='$wgServer$wgScriptPath/index.php?action=viewNotifications' style='color:#EE0000;'><img src='$wgServer$wgScriptPath/{$config->getValue('iconPath')}mail_16x12.png' />$smallNotificationText</a>";
            }
            if($wgUser->isLoggedIn()){
        $me = Person::newFromWgUser();
        if($me->isRole(CI) || $me->isRole(HQP)){
                echo "<a id='status_profile_photo' class='menuTooltip' style='padding-left:0;margin-left:10px; font-size:13px;' title='Profile' href='{$p->getUrl()}'><img class='photo' src='{$p->getPhoto()}' />{$p->getNameForForms()}</a> <span style='font-size:20px;font-weight: lighter;color: rgba(255, 255, 255, 0.17);'>|</span>";
        }
        else{
                echo "<a id='status_profile_photo' class='menuTooltip' style='padding-left:0;margin-left:10px; font-size:13px;' title='Main' href='$wgServer$wgScriptPath/index.php/Special:Sops'><img class='photo' src='{$p->getPhoto()}' />{$p->getNameForForms()}</a> <span style='font-size:20px;font-weight: lighter;color: rgba(255, 255, 255, 0.17);'>|</span>";
        }
                if(!$wgImpersonating && !$wgDelegating){
                    $logout = $this->data['personal_urls']['logout'];
                    $getStr = "";
                    foreach($_GET as $key => $get){
                        if($key == "title" || $key == "returnto"){
                            continue;
                        }
                        if(strlen($getStr) == 0){
                            $getStr .= "?$key=$get";
                        }
                        else{
                            $getStr .= "&$key=$get";
                        }
                    }
                    $logout['href'] .= urlencode($getStr);
                    echo "<a id='status_logout' name='arrow_right_16x16' class='changeImg' style='font-size: 13px;line-height:32px;display:inline-block;width:74px;cursor: pointer;' title='Logout' href='{$logout['href']}'><span>Logout</span>&nbsp;&nbsp;&nbsp;<span style='color:white;font-size:28px;vertical-align: middle;display: inline-block; width:18px;text-decoration:none;'>&#12297;</span></a>";
                }
            }
            if(!TESTING && $wgScriptPath != "" && !DEMO){
                exec("git rev-parse HEAD", $output);
                $revId = @substr($output[0], 0, 10);
                exec("git rev-parse --abbrev-ref HEAD", $output);
                $branch = @$output[1];
                $revIdFull = "<a title='{$output[0]}' target='_blank' href='https://github.com/UniversityOfAlberta/GrandForum/commit/{$output[0]}'>$revId</a>";
                $branchFull = "<a title='$branch' target='_blank' href='https://github.com/UniversityOfAlberta/GrandForum/tree/$branch'>$branch</a>";
                $docs = "<a title='docs' target='_blank' href='http://ssrg5.cs.ualberta.ca/rtd/docs/grand-forum/en/latest/'>Docs</a>";
                
                if(strstr($wgScriptPath, "staging") !== false){
                    echo "<div style='position:absolute;top:15px;left:525px;' class='highlightsBackground2'>
                            STAGING ($branchFull, $revIdFull), $docs&nbsp;&nbsp;<a target='_blank' href='http://grand.cs.ualberta.ca/~dwt/behat_test/symfony/output/output.html'><img src='http://grand.cs.ualberta.ca/~dwt/behat_test/testSuiteStatus.php' /></a></div>";
                }
                else{
                    echo "<div style='position:absolute;top:15px;left:525px;' class='highlightsBackground2'>
                            DEVELOPMENT ($branchFull, $revIdFull), $docs&nbsp;&nbsp;<a target='_blank' href='http://grand.cs.ualberta.ca/~dwt/behat_test/symfony/output/output.html'><img src='http://grand.cs.ualberta.ca/~dwt/behat_test/testSuiteStatus.php' /></a></div>";
                }
            }
            if($config->getValue('globalMessage') != ""){
                $wgMessage->addInfo($config->getValue('globalMessage'));
            }
        ?>
        </div>
    </div>
    <div id="outerHeader" class=' <?php if(isset($_COOKIE['sideToggled']) && $_COOKIE['sideToggled'] == 'in') echo "menu-in";?>'>
        <div id="sideToggle" class="highlightsBackground0">
            <?php if(isset($_COOKIE['sideToggled']) && $_COOKIE['sideToggled'] == 'in') { echo "&#12297;"; } else { echo "&#12296;";}?>
        </div>
        <div id="header">
            <a id="allTabs"><img src="<?php echo $wgServer.$wgScriptPath; ?>/skins/icons/white_mix/hamburger.png" /></a>
            <a name="top" id="contentTop"></a>
            <ul class="top-nav highlightsBackground0">
            <?php 
                global $notifications, $notificationFunctions, $wgUser, $wgScriptPath, $wgMessage, $config;
                $GLOBALS['tabs'] = array();
                
                $GLOBALS['tabs']['Other'] = TabUtils::createTab("", "");
                //$GLOBALS['tabs']['Main'] = TabUtils::createTab($config->getValue("networkName"), "$wgServer$wgScriptPath/index.php/Main_Page");

                // $GLOBALS['tabs']['Profile'] = TabUtils::createTab("My Profile");
                // $GLOBALS['tabs']['Manager'] = TabUtils::createTab("Manager");
                /*if($me->isRoleAtLeast(Manager)){
                    $GLOBALS['tabs']['Review'] = TabUtils::createTab("Overview","$wgServer$wgScriptPath/index.php/Special:Sops");
                }
            if($me->isRoleAtLeast(Admin)){
                    $GLOBALS['tabs']['AdminTabs'] = TabUtils::createTab("Admin Tabs","$wgServer$wgScriptPath/index.php/Special:AdminTabs");
            }
                    $GLOBALS['tabs']['Manage Products'] = TabUtils::createTab("Outputs","$wgServer$wgScriptPath/index.php/Special:ManageProducts"); 
                    $GLOBALS['tabs']['Manage Courses'] = TabUtil::createTab("Courses","$wgServer$wgScriptPath/index.php/Special:Courses");*/

                // Making tabs to eventually phase out sidebar:
                /*
                if($wgUser->isLoggedIn() && $config->getValue('networkName') == "CSGARS"){
                    $GLOBALS['tabs']['Main'] = TabUtils::createTab("GARS", "$wgServer$wgScriptPath/index.php/Main_Page");
                }
                */
                wfRunHooks('TopLevelTabs', array(&$GLOBALS['tabs']));
                wfRunHooks('SubLevelTabs', array(&$GLOBALS['tabs']));
            ?>
            <?php 
                global $wgUser, $wgScriptPath, $tabs;
                $selectedFound = false;
               /* foreach($tabs as $key => $tab){
                    ksort($tab['subtabs']);
                    if($tabs[$key]['href'] == "" && isset($tabs[$key]['subtabs'][0])){
                        $tabs[$key]['href'] = $tab['subtabs'][0]['href'];
                    }
                    if(strstr($tab['selected'], "selected") !== false){
                        $selectedFound = true;
                    }
                    foreach($tab['subtabs'] as $subtab){
                        if(strstr($subtab['selected'], "selected") !== false){
                            $tabs[$key]['selected'] = "selected";
                            $selectedFound = true;
                            if($tabs[$key]['text'] == ""){
                                $tabs['Main']['selected'] = "selected";
                            }
                        }
                        if(count($subtab['dropdown']) > 0){
                            foreach($subtab['dropdown'] as $dropdown){
                                if(strstr($dropdown['selected'], "selected") !== false){
                                    $tabs[$key]['selected'] = "selected";
                                    $selectedFound = true;
                                    if($tabs[$key]['text'] == ""){
                                        $tabs['Main']['selected'] = "selected";
                                    }
                                }
                            }
                        }
                    }
                }*/
                if(!$selectedFound){
                    // If a selected tab wasn't found, just default to the Main Tab
                    $tabs['Main']['selected'] = "selected";
                }
                foreach($tabs as $key => $tab){
                    if($tab['href'] != "" && $tab['text'] != ""){
                        echo "<li class='top-nav-element {$tab['selected']}'>\n";
                        echo "    <span class='top-nav-left'>&nbsp;</span>\n";
                        echo "    <a id='{$tab['id']}' class='top-nav-mid highlights-tab' href='{$tab['href']}'>{$tab['text']}</a>\n";
                        echo "    <span class='top-nav-right'>&nbsp;</span>\n";
                        echo "</li>";
                    }
                }
            ?>
            </ul>
        </div>
        <div id="submenu" class="highlightsBackground0">
            <ul>
                <?php global $dropdownScript;
                 $i = 0;
                 foreach($tabs as $tab){
                    $i++;
                    if($tab['selected'] == "selected"){
                        $j = 0;
                        foreach($tab['subtabs'] as $subtab){
                            $j++;
                            $class = "subtab_{$i}_{$j}";
                            if(count($subtab['dropdown']) > 0){
                                foreach($subtab['dropdown'] as $dropdown){
                                    echo "<li class='$class hidden {$dropdown['selected']}'><a class='highlights-tab' href='".htmlspecialchars($dropdown['href'])."'>".htmlspecialchars($dropdown['text'])."</a></li>";
                                }
                                $dropdownScript .= "createDropDown('$class', '{$subtab['text']}', 125);";
                            }
                            else if($subtab['href'] != ""){
                                echo "<li class='$class {$subtab['selected']}'><a class='highlights-tab' href='".htmlspecialchars($subtab['href'])."'>".$subtab['text']."</a></li>";
                            }
                        }
                        break;
                    }
                 }
                 foreach($this->data['content_actions'] as $key => $action) {
                    if($key == "nstab-special" || 
                       $key == "varlang-watch"){
                        continue;
                    }
                   ?><li
                   <?php if($action['class']) { ?>class="<?php echo htmlspecialchars($action['class']) ?>"<?php } ?>
                   ><a class='highlights-tab' href="<?php echo htmlspecialchars($action['href']) ?>"><?php
                   echo htmlspecialchars($action['text']) ?></a></li><?php
                 } ?>
            </ul>
        </div>
    </div>
    
    <?php global $dropdownScript; echo "<script type='text/javascript'>$dropdownScript</script>"; ?>
    <div id="side" class=' <?php if(isset($_COOKIE['sideToggled']) && $_COOKIE['sideToggled'] == 'in') echo "menu-in";?>'>
            <ul id="nav">
            <?php
                $this->toolbox();
            ?>
            </ul>
        </div><!-- end of SIDE div -->
    <div id="allTabsDropdown" style="display:none;"></div>
    <div id="mBody">
        <div id="bodyContent" class=' <?php if(isset($_COOKIE['sideToggled']) && $_COOKIE['sideToggled'] == 'in') echo "menu-in";?>'>
            <?php if($this->data['sitenotice']) { ?><div id="siteNotice"><?php $this->html('sitenotice') ?></div><?php } ?>
            <?php if($this->data['thispage'] != 'Main_Page'){ ?><h1><?php $this->text('title') ?></h1><?php } ?>
            <div id='wgMessages'><?php $wgMessage->showMessages(); ?></div>
            <h3 id="siteSub"><?php $this->msg('tagline') ?></h3>
            <div id="contentSub"><?php $this->html('subtitle') ?></div>
            <?php if($this->data['undelete']) { ?><div id="contentSub"><?php     $this->html('undelete') ?></div><?php } ?>
            <?php if($this->data['newtalk'] ) { ?><div class="usermessage"><?php $this->html('newtalk')  ?></div><?php } ?>
            <!-- start content -->
            <?php $this->html('bodytext') ?>
            <?php if($this->data['catlinks']) { ?><div id="catlinks"><?php       $this->html('catlinks') ?></div><?php } ?>
            <!-- end content -->
            <?php if($this->data['dataAfterContent']) { $this->html ('dataAfterContent'); } ?>
                <div id="footer"><table><tr><td align="left" width="1%" nowrap="nowrap">
                <img src="<?php echo $wgServer.$wgScriptPath.'/'.$config->getValue('iconPathHighlighted'); ?>border.png" style="display:none;" />
                <img src="<?php echo $wgServer.$wgScriptPath.'/'.$config->getValue('iconPathHighlighted'); ?>border_focus.png" style="display:none;" />
            <?php if($this->data['copyrightico']) { ?><div id="f-copyrightico"><?php $this->html('copyrightico') ?></div><?php } ?></td><td align="center">
    <?php   // Generate additional footer links
            $footerlinks = array(
                'lastmod', 'viewcount', 'numberofwatchingusers', 'credits', 'copyright',
                'tagline',
            );
            $validFooterLinks = array();
            foreach( $footerlinks as $aLink ) {
                if( isset( $this->data[$aLink] ) && $this->data[$aLink] ) {
                    $validFooterLinks[] = $aLink;
                }
            }
            echo '<ul id="f-list">';
            if ( count( $validFooterLinks ) > 0 ) {
    ?>          
    <?php
                foreach( $validFooterLinks as $aLink ) {
                    if( isset( $this->data[$aLink] ) && $this->data[$aLink] ) {
    ?>                  <li id="f-<?php echo$aLink?>"><?php $this->html($aLink) ?></li>
    <?php           }
                }
            }
    ?></ul></td></tr></table>
        </div><!-- end of the FOOTER div -->
        </div><!-- end of MAINCONTENT div -->   
    </div><!-- end of MBODY div -->
    <div id="recordDiv"></div>
</div><!-- end of the CONTAINER div -->
<?php echo wfReportTimeOld(); ?>

</body>
</html>

<?php
    }
    function toolbox() {
?>
    <li class="portlet" id="p-tb">
<?php
    global $wgServer, $wgScriptPath, $wgUser, $wgRequest, $wgAuth, $wgTitle, $config, $wgLang;
        $GLOBALS['toolbox'] = array();
        //$GLOBALS['toolbox']['People'] = TabUtils::createToolboxHeader("People");
        //$GLOBALS['toolbox']['Products'] = TabUtils::createToolboxHeader(Inflect::pluralize($config->getValue('productsTerm')));
    $GLOBALS['toolbox']['People'] = TabUtils::createToolboxHeader("Menu Items");
        $GLOBALS['toolbox']['Products'] = TabUtils::createToolboxHeader("Reviewer");

        $GLOBALS['toolbox']['Other'] = TabUtils::createToolboxHeader("Admin");
 
        if($wgUser->isLoggedIn()){
            echo "
            <ul class='pBodyLogin'>";
            /*if(isset($_GET['returnto'])){
                redirect("$wgServer$wgScriptPath/index.php/{$_GET['returnto']}");
            }*/
            $me = Person::newFromWgUser();
            if(isset($_GET['hash'])){
                $url = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                $url = str_replace("?hash={$_GET['hash']}", "", $url);
                redirect("$url#" . $_GET['hash']);
            }
            if(($wgTitle->getText() == "Main Page" || $wgTitle->getText() == "UserLogin") && !$me->isRole(CI) && !$me->isRole(HQP) && $_GET['action'] != "viewNotifications"){
                redirect("$wgServer$wgScriptPath/index.php/Special:Sops");  
            }
            wfRunHooks('ToolboxHeaders', array(&$GLOBALS['toolbox']));
            wfRunHooks('ToolboxLinks', array(&$GLOBALS['toolbox']));
            //$GLOBALS['toolbox']['Other']['links'][1000] = TabUtils::createToolboxLink("Upload File", "$wgServer$wgScriptPath/index.php/Special:Upload");
            if($wgUser->isLoggedIn() && $config->getValue('networkName') == "AGE-WELL"){ 
                $resources = TabUtils::createToolboxHeader("Resources");
                $resources['links'][1001] = TabUtils::createToolboxLink("Network Management", "$wgServer$wgScriptPath/index.php/Network_Resources/Network_Management_Office");
                $resources['links'][1002] = TabUtils::createToolboxLink("HQP Resources", "$wgServer$wgScriptPath/index.php/HQP_Wiki:HQP Resources");
                $resources['links'][1003] = TabUtils::createToolboxLink("Technical Resources", "$wgServer$wgScriptPath/index.php/Network_Resources/SFU_Core_Facility");
                for($year=date('Y'); $year >= 2014; $year--){
                    $title = "Conference:{$config->getValue('networkName')}_Annual_Conference_{$year}";
                    if(Wiki::newFromTitle("{$title}")->exists()){
                        $resources['links'][1004] = TabUtils::createToolboxLink("{$year} Conference", "$wgServer$wgScriptPath/index.php/{$title}");
                        break;
                    }
                }
                $resources['links'][1005] = TabUtils::createToolboxLink("AGE-WELL Seminars", "$wgServer$wgScriptPath/index.php/AGE-WELL_Seminars");
                if($me->isRole(TL) || $me->isRole(TC) || $me->isRoleAtLeast(STAFF)){
                    $resources['links'][1006] = TabUtils::createToolboxLink("WP Coordinators", "$wgServer$wgScriptPath/index.php/".TL.":Workpackage Coordinator");
                }
                $resources['links'][1007] = TabUtils::createToolboxLink("Funding", "$wgServer$wgScriptPath/index.php/Network_Resources/Funding");
                $resources['links'][1007] = TabUtils::createToolboxLink("Weekly Digest", "$wgServer$wgScriptPath/index.php/Network_Resources/Weekly_Digest");
                array_splice($GLOBALS['toolbox'], 2, 0, array($resources));
            }
            if($wgUser->isLoggedIn() && $config->getValue('networkName') == "GlycoNet"){
                $GLOBALS['toolbox']['Other']['links'][] = TabUtils::createToolboxLink("Logos/Templates", "$wgServer$wgScriptPath/index.php/Logos_Templates");
                $GLOBALS['toolbox']['Other']['links'][] = TabUtils::createToolboxLink("Forum Help and FAQs", "$wgServer$wgScriptPath/index.php/FAQ");
            }
            if($wgUser->isLoggedIn() && $config->getValue('networkName') == "CSGARS"){
                $GLOBALS['toolbox']['Help'] = TabUtils::createToolboxHeader("Other");
                $GLOBALS['toolbox']['Help']['links'][] = TabUtils::createToolboxLink("Forum Help and FAQs", "$wgServer$wgScriptPath/index.php/FAQs");
                if($me->isRole(EVALUATOR) || $me->isRole(MANAGER) || $me->isRole(ADMIN)){
                    $GLOBALS['toolbox']['Help']['links'][] = TabUtils::createToolboxLink("January 2021 Instructions", "$wgServer$wgScriptPath/data/GradAdm2021.pdf", "_blank");
                    $GLOBALS['toolbox']['Help']['links'][] = TabUtils::createToolboxLink("Reviewer Manual", "$wgServer$wgScriptPath/data/GARS_Manual.pdf", "_blank");
                    $GLOBALS['toolbox']['Help']['links'][] = TabUtils::createToolboxLink("Admission Policy", "https://skatgame.net/appli", "_blank");
                }
            }
            if($wgUser->isLoggedIn() && $config->getValue('networkName') == "GARS"){
                $GLOBALS['toolbox']['Help'] = TabUtils::createToolboxHeader("Other");
                $GLOBALS['toolbox']['Help']['links'][] = TabUtils::createToolboxLink("Forum Help and FAQs", "$wgServer$wgScriptPath/index.php/FAQs");
            }
            //$GLOBALS['toolbox']['Other']['links'][9998] = TabUtils::createToolboxLink("Frequently Asked Questions", "$wgServer$wgScriptPath/index.php/Help:Contents");
            $person = Person::newFromId($wgUser->getId());
            //$GLOBALS['toolbox']['Other']['links'][9999] = TabUtils::createToolboxLink("Other Tools", "$wgServer$wgScriptPath/index.php/Special:SpecialPages");
            global $toolbox;
            $i = 0;
            array_splice($GLOBALS['toolbox']['Other']['links'],1,0,$poll_tab);
            array_splice($GLOBALS['toolbox']['Other']['links'],4,0,$resources_tab);
            foreach($toolbox as $key => $header){
                if(count($header['links']) > 0){
                    $hr = ($i > 0) ? "" : "";
                    echo "<span class='pBodyTitle highlightsBackground$i'>{$hr}{$header['text']}</span><ul class='pBody highlightsBackground$i'>";
                    ksort($header['links']);
                    foreach($header['links'] as $lKey => $link){
                        echo "<li><a href='{$link['href']}' target='{$link['target']}'>{$link['text']}</a></li>";
                    }
                    echo "</ul>";
                    $i++;
                }
            }
        }
        else {
            global $wgSiteName, $wgOut;
            setcookie('sideToggled', 'out', time()-3600);
            $loginFailed = (isset($_POST['wpLoginattempt']) || isset($_POST['wpMailmypassword']));
            if($loginFailed){
                if(isset($_POST['wpName'])){
                    $_POST['wpUsername'] = $_POST['wpName'];
                }
                else{
                    $_POST['wpName'] = $_POST['wpUsername'];
                }
                $_POST['wpName'] = str_replace("_", " ", sanitizeInput($_POST['wpName']));
                $_POST['wpUsername'] = $_POST['wpName'];
                $_POST['wpPassword'] = sanitizeInput($_POST['wpPassword']);
                $person = Person::newFromName($_POST['wpName']);
                $user = User::newFromName($_POST['wpName']);
                if($user == null || $user->getId() == 0 || $user->getName() != $_POST['wpName']){
                    $failMessage = "<p class='inlineError'>There is no user by the name of <b>{$_POST['wpName']}</b>.<br />";
                    if(isset($_POST['wpMailmypassword'])){
                        $failMessage .= "<b>Password request failed</b>";
                    }
                    $failMessage .= "</p>";
                }
                else if(isset($_POST['wpMailmypassword'])){
                    $user = User::newFromName($_POST['wpUsername']);
                    $user->load();
                    $failMessage = "<p><div class='inlineSuccess'>A new password has been sent to the e-mail address registered for &quot;{$_POST['wpName']}&quot;.</div>  Please wait a few minutes for the email to appear.  If you do not recieve an email, then contact <a class='highlights-text-hover' style='padding: 0;background:none;display:inline;border-width: 0;' href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a>.<br /><b>NOTE: Only one password reset can be requested every 10 minutes.</b></p>";
                }
                else{
                    $failMessage = "<p class='inlineError'>Incorrect password entered. Please try again.</p>";
                }
                if($user != null && $user->checkTemporaryPassword($_POST['wpPassword'])){
                    $failMessage = "";
                    return;
                }
                if(isset($_POST['wpMailmypassword'])){
                    echo "<script type='text/javascript'>
                        parent.showResetMessage(\"$failMessage\");
                    </script>";
                    exit;
                }
                $wgOut->addHTML("
                <p>Typical problems with login:</p>
                <ol>
                    <li>You have no account setup for you yet
                        <ul>
                            <li>Ask your supervisor or {$config->getValue('projectThemes')} coordinator to setup one for you</li>
                        </ul>
                    </li>
                    <li>There is an account but you do not remember your ID
                        <ul>
                            <li>Look for the name through “search” textbox above (note that ".HQP." will typically not show up in the search)</li>
                            <li>When you see your name in the drop-down list, click on it and go to your profile page</li>
                            <li>The URL indicates the actual login ID (case sensitive, period between first and last name required, accents required)</li>
                        </ul>
                    </li>
                    <li>You know your ID but not your password
                        <ul>
                            <li>Click on the “E-mail new password” link to receive a temporary one in your mailbox (look in your spam folder; if you do not receive one within 30 minutes contact your {$config->getValue('projectThemes')} coordinator to check whether your email address is setup correctly)</li>
                        </ul>
                    </li>
                </ol>");
                $message = "<tr><td colspan='2'><div style='display:inline-block;' id='failMessage'>$failMessage</span>
<p>
You must have cookies enabled to log in to {$config->getValue('siteName')}.<br />
</p>
<p>
Your login ID is your email address (case sensitive)
If you have forgotten your password please enter your login ID and request a new random password to be sent to the email address associated with your Forum account.</p></td></tr>";
                $emailPassword = "
                
                <form target='resetFrame' method='post' action='$wgServer$wgScriptPath/index.php/Special:PasswordReset' style='position:relative;left:5px;'>
                <table>
                    <tr>
                        <td>
                            <input id='wpUsername1' type='hidden' name='wpUsername' value='' />
                            <input type='hidden' name='wpEmail' value='' />
                            <input class='dark' type='submit' name='wpMailmypassword' id='wpMailmypassword' tabindex='6' value='E-mail new password' />
                        </td>
                    </tr>
                </table>
                </form>
                <iframe name='resetFrame' id='resetFrame' src='' style='width:0;height:0;border:0;' frameborder='0' width='0' height='0'></iframe>
                <script type='text/javascript'>
                    function showResetMessage(message){
                        $('#failMessage').html(message);\n";
                        if(isExtensionEnabled("Shibboleth")){
                            $emailPassword .= "updateLoginPopup();\n";
                        }
                    $emailPassword .= "}
                    $('#wpUsername1').attr('value', $('#wpName1').val());
                    $('#wpName1').change(function(){
                        $('#wpUsername1').attr('value', $('#wpName1').val());
                    }).keyup(function(){
                        $('#wpUsername1').attr('value', $('#wpName1').val());
                    });
                </script>";
            }
            if($_SESSION == null || 
               $wgRequest->getSessionData('wsLoginToken') == "" ||
               $wgRequest->getSessionData('wsLoginToken') == null){
                wfSetupSession();
                LoginForm::setLoginToken();
            }
            $getStr = "";
            foreach($_GET as $key => $get){
                if($key == "title" || 
                   $key == "returnto" || 
                   $key == "returntoquery" ||
                   ($key == "action" && $get == "submitlogin") ||
                   ($key == "type" && $get == "login")){
                    continue;
                }
                if(strlen($getStr) == 0){
                    $getStr .= "?$key=$get";
                }
                else{
                    $getStr .= "&$key=$get";
                }
            }
            $returnTo = "";
            if(isset($_GET['returnto'])){
                $returnTo = $_GET['returnto'];
                if(isset($_GET['returntoquery'])){
                    $returnTo .= "?".$_GET['returntoquery'];
                }
            }
            else if (isset($_GET['title'])){
                $returnTo .= str_replace(" ", "_", $_GET['title']);
            }
            else {
                $url = str_replace("$wgScriptPath/", "", 
                       str_replace("index.php/", "", $wgRequest->getRequestURL()));
                $returnTo .= str_replace(" ", "_", $url);
            }
            $returnTo .= $getStr;
            $returnTo = urlencode($returnTo);
            if(isset($_POST['returnto'])){
                $returnTo = $_POST['returnto'];
            }
            
            $wgUser->setCookies();
            
            if(isExtensionEnabled("Shibboleth") && $config->getValue('shibLoginUrl') != ""){
                SetupShibPopup();
            }
            
            if(isset($_POST['wpPassword']) &&
               isset($_POST['wpNewPassword']) &&
               isset($_POST['wpRetype']) &&
               isset($_POST['wpName']) &&
               $_POST['wpNewPassword'] == $_POST['wpRetype']){
                $user = User::newFromName($_POST['wpName']);
                $user->load();
                if($user->checkPassword($_POST['wpNewPassword'])){
                    redirect("$wgServer$wgScriptPath/index.php/$returnTo");
                }
            }
            
            $token = LoginForm::getLoginToken();
            $name = $wgRequest->getText('wpName');
            $name = sanitizeInput($name);
            echo "
            <ul class='pBodyLogin'>";
            echo <<< EOF
<form style='position:relative;left:5px;' name="userlogin" method="post" action="$wgServer$wgScriptPath/index.php?title=Special:UserLogin&amp;action=submitlogin&amp;type=login&amp;returnto={$returnTo}">
    <table style='width:185px;'>
        $message
        <tr class='tooltip' title="Your username is your email address (case-sensitive)">
            <td class="mw-input mw-input-string">
                <input type='text' class='loginText highlightsBackground0' style='width:97%;' name="wpName" value="$name" id="wpName1" placeholder="Username"
                    tabindex="1" size='20' />
            </td>
        </tr>
        <tr>
            <td class="mw-input mw-input-string">
                <input type='password' class='loginPassword highlightsBackground1' style='width:97%' name="wpPassword" id="wpPassword1" placeholder="Password"
                    tabindex="2" size='20' autocomplete='off' />
            </td>
        </tr>
        <tr>
            <!--td></td-->
            <td colspan="2" class="mw-input mw-input-string">
                <input type='checkbox' name="wpRemember"
                    tabindex="4"
                    value="1" id="wpRemember"
                                        /> <label for="wpRemember">Remember my Login</label>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="mw-submit">
                <input type='submit' class='dark' name="wpLoginattempt" id="wpLoginattempt" tabindex="5" value="Log in" />
            </td>
        </tr>
    </table>
<input type="hidden" name="wpLoginToken" value="$token" /></form>
$emailPassword
</li>
EOF;
            
        }
        wfRunHooks( 'MonoBookTemplateToolboxEnd', array( &$this ) );
        wfRunHooks( 'SkinTemplateToolboxEnd', array( &$this ) );
        echo "</ul></li>";
?>
<?php
    }

} // end of class
