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
$wgValidSkinNames['cavendish'] = 'cavendish';
$wgAutoloadClasses['SkinCavendish'] = __DIR__ . '/cavendish.php';

if( !defined( 'MEDIAWIKI' ) )
	die();

/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 * @todo document
 * @package MediaWiki
 * @subpackage Skins
 */
class SkinCavendish extends SkinTemplate {
	/** Using cavendish. */
	function initPage( &$out ) {
		SkinTemplate::initPage($out);
		$this->skinname  = 'cavendish';
		$this->stylename = 'cavendish';
		$this->template  = 'CavendishTemplate';
	}
}
	
class CavendishTemplate extends QuickTemplate {
	/**
	 * Template filter callback for cavendish skin.
	 * Takes an associative array of data set from a SkinTemplate-based
	 * class, and a wrapper for MediaWiki's localization database, and
	 * outputs a formatted page.
	 *
	 * @access private
	 */
	function execute() {
		global $wgRequest, $wgServer, $wgScriptPath, $wgOut, $wgLogo, $wgTitle, $wgUser, $wgMessage, $wgImpersonating, $wgDelegating, $wgTitle, $config;
		$this->skin = $skin = $this->data['skin'];
		$action = $wgRequest->getText( 'action' );

        if(FROZEN){
            $wgMessage->addInfo("The Forum is currently not available for edits during the RMC review-and-deliberation period.");
        }

		// Suppress warnings to prevent notices about missing indexes in $this->data
		wfSuppressWarnings();
		
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php $this->text('lang') ?>" lang="<?php $this->text('lang') ?>" dir="<?php $this->text('dir') ?>">
	<head>
		<meta http-equiv="Content-Type" content="<?php $this->text('mimetype') ?>; charset=<?php $this->text('charset') ?>" />
		<?php $this->html('headlinks') ?>
		<title><?php $this->text('pagetitle') ?></title>
		<link type="image/x-icon" href="<?php echo $wgServer.$wgScriptPath.'/favicon.png'; ?>" rel="shortcut icon" />
		<link type="text/css" href="<?php $this->text('stylepath') ?>/smoothness/jquery-ui-1.8.21.custom.css" rel="Stylesheet" />
		<link type="text/css" href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/jquery.qtip.min.css" rel="Stylesheet" />
		<link type="text/css" href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/chosen/chosen.css.php" rel="Stylesheet" />
		<?php $this->html('csslinks') ?>

		<link rel="stylesheet" href="<?php $this->text('stylepath') ?>/common/shared.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="<?php $this->text('stylepath') ?>/common/commonPrint.css" type="text/css" media="print" />
		<link rel="stylesheet" href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/print.css" type="text/css" media="print" />
		<link rel="stylesheet" href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/cavendish.css" type="text/css" />
		
		<link type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/DataTables/css/jquery.dataTables.css" rel="Stylesheet" />
		<link type="text/css" rel="stylesheet" href="<?php echo "$wgServer$wgScriptPath"; ?>/skins/simplePagination/simplePagination.css" />
		
		<style type="text/css" media="screen,projection">/*<![CDATA[*/ @import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/main.css"; /*]]>*/</style>
		<style type="text/css" media="screen,projection">/*<![CDATA[*/ @import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/extensions.css"; /*]]>*/</style>
		<style <?php if(empty($this->data['printable']) ) { ?>media="print"<?php } ?> type="text/css">/*<![CDATA[*/ @import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/print.css"; /*]]>*/</style>
		
		<link rel="stylesheet" type="text/css" media="print" href="<?php $this->text('stylepath') ?>/common/commonPrint.css" />
		<link type="text/css" href="<?php $this->text('stylepath') ?>/switcheroo/switcheroo.css" rel="Stylesheet" />
		<link type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/tagIt/css/jquery.tagit.css" rel="Stylesheet" />
		<link type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/tagIt/css/tagit.ui-zendesk.css" rel="Stylesheet" />
		<link type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jqueryDropdown/jquery.dropdown.css" rel="Stylesheet" />
		<link rel="stylesheet" type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/colorbox/colorbox.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/skins/cavendish/highlights.css.php" />
		
		<script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/date.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/inflection.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/to-title-case.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/countries.en.js"></script>

        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery-ui.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.browser.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.cookie.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.resizeY.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.limit-1.2.source.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.multiLimit.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.combobox.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.chosen.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery-ui.triggeredAutocomplete.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.filterByText.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.scrollTo-min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.md5.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jqueryDropdown/jquery.dropdown.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.reallyvisible.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.dom-outline.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.jsPlumb-min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/colorbox/jquery.colorbox-min.js"></script>   
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/DataTables/js/jquery.dataTables.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.qtip.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.forceNumeric.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.form.min.js"></script>
        
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/tinymce/tinymce.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/tinymce/combobox.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/tinymce/jquery.tinymce.min.js"></script>
        
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/tagIt/js/tag-it.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/switcheroo.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/raphael.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/spinner.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/filter.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/autosave.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/extensions/Messages/messages.js"></script>
        
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/d3.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/html2canvas.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/ScreenRecord/record.js"></script>
    
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/underscore-min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/backbone-min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/backbone-subviews.js"></script>
        <!--script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/backbone-relational-min.js"></script>-->
        <script type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.simplePagination.js"></script>
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
		</script>
		<?php echo $wgOut->getScript(); ?>
		<!-- site js -->
		<?php	if($this->data['jsvarurl']) { ?>
		<script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('jsvarurl') ?>"><!-- site js --></script>
		<?php	} ?>
		<!-- should appear here -->
		<?php	if($this->data['pagecss']) { ?>
				<style type="text/css"><?php $this->html('pagecss') ?></style>
		<?php	}
				if($this->data['usercss']) { ?>
				<style type="text/css"><?php $this->html('usercss') ?></style>
		<?php	}
				if($this->data['userjs']) { ?>
				<script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('userjs' ) ?>"></script>
		<?php	}
				if($this->data['userjsprev']) { ?>
				<script type="<?php $this->text('jsmimetype') ?>"><?php $this->html('userjsprev') ?></script>
		<?php	}
				if($this->data['trackbackhtml']) print $this->data['trackbackhtml']; ?>

		<?php createModels(); ?>
		<script type='text/javascript'>
		
		    // Configs
		    wgRoles = <?php global $wgAllRoles; echo json_encode($wgAllRoles); ?>;
		    
		    <?php
		        foreach($config->constants as $key => $value){
		            echo "{$key} = '{$value}';\n";
		        }
		    ?>
		    
		    projectPhase = <?php echo PROJECT_PHASE; ?>;
		    projectsEnabled = <?php var_export($config->getValue('projectsEnabled')); ?>;
		    networkName = "<?php echo $config->getValue('networkName'); ?>";
		    extensions = <?php echo json_encode($config->getValue('extensions')); ?>;
		    iconPath = "<?php echo $config->getValue('iconPath'); ?>";
		    iconPathHighlighted = "<?php echo $config->getValue('iconPathHighlighted'); ?>";
		    highlightColor = "<?php echo $config->getValue('highlightColor'); ?>";
		    productsTerm = "<?php echo $config->getValue('productsTerm'); ?>";
		
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
		                $("#sideToggle").html("&gt;");
		                $("#side").animate({
		                    'left': '-200px'
		                }, 200, 'swing');
		                $("#outerHeader").animate({
		                    'left': '0'
		                }, 200, 'swing');
		                $("#bodyContent").animate({
		                    'left': '30px'
		                }, 200, 'swing', function(){
		                    jsPlumb.repaintEverything();
		                });
                        sideToggled = 'in';
                        $.cookie('sideToggled', 'in', {expires: 30});
                    }
                    else{
                        $("#sideToggle").html("&lt;");
                        $("#side").animate({
		                    'left': '0px'
		                }, 200, 'swing');
		                $("#outerHeader").animate({
		                    'left': '200px'
		                }, 200, 'swing');
		                $("#bodyContent").animate({
		                    'left': '230px'
		                }, 200, 'swing', function(){
		                    jsPlumb.repaintEverything();
		                });
                        sideToggled = 'out';
                        $.cookie('sideToggled', 'out', {expires: 30});
                    }
		        });
		    });
		</script>
		<?php if(isset($_GET['embed'])){ ?>
		    <style>
		    
		        html {
		            overflow: hidden;
		            background: #FFFFFF;
		        }
			
			body {
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
<body <?php if($this->data['body_ondblclick']) { ?> ondblclick="<?php $this->text('body_ondblclick') ?>"<?php } ?>
<?php if($this->data['body_onload']) { ?> onload="<?php $this->text('body_onload') ?>"<?php } ?>
 class="mediawiki <?php $this->text('dir') ?> <?php $this->text('pageclass') ?> <?php $this->text('skinnameclass') ?>">

<div id="internal"></div>
<div id="container">
	<div id="topheader">
        <?php
            global $wgSitename, $notifications, $notificationFunctions, $config;
            if(count($notifications) == 0){
                foreach($notificationFunctions as $function){
                    call_user_func($function);
                }
            }
            echo "<div class='smallLogo'><a href='{$this->data['nav_urls']['mainpage']['href']}' title='$wgSitename'><img src='$wgServer$wgScriptPath/{$config->getValue('logo')}' /></a></div>";
            echo "<div class='search'><div id='globalSearch'></div></div>";
            echo "<div class='login'>";
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
            echo "<a id='status_help_faq' name='question_mark_8x16' class='menuTooltip changeImg highlights-text-hover' title='Help/FAQ' href='$wgServer$wgScriptPath/index.php/Help:Contents'><img src='$wgServer$wgScriptPath/{$config->getValue('iconPath')}question_mark_8x16.png' /></a>";
            if(count($config->getValue("socialLinks")) > 0){
	            echo "<a id='share' style='cursor:pointer;' name='share_16x16' class='menuTooltipHTML changeImg highlights-text-hover'><img src='$wgServer$wgScriptPath/{$config->getValue('iconPath')}share_16x16.png' />&nbsp;▼</a>";
	        }
	        if($wgUser->isLoggedIn()){
		        $p = Person::newFromId($wgUser->getId());
		        
		        $smallNotificationText = "";
		        if(count($notifications) > 0){
		            $notificationText = " (".count($notifications).")";
		            $smallNotificationText = "<img class='overlay' style='margin-left:-16px;' src='$wgServer$wgScriptPath/{$config->getValue('iconPath')}mail_16x12_red.png' />*";
		        }
		        echo "<a id='status_notifications' name='mail_16x12' class='menuTooltip changeImg highlights-text-hover' title='Notifications$notificationText' href='$wgServer$wgScriptPath/index.php?action=viewNotifications' style='color:#EE0000;'><img src='$wgServer$wgScriptPath/{$config->getValue('iconPath')}mail_16x12.png' />$smallNotificationText</a>";
		        echo "<a id='status_profile' class='menuTooltip highlights-text-hover' title='Profile' href='{$p->getUrl()}'>{$p->getNameForForms()}</a>";
		        echo "<a id='status_profile_photo' class='menuTooltip highlights-text-hover' title='Profile' href='{$p->getUrl()}'><img class='photo' src='{$p->getPhoto()}' /></a>";
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
	                echo "<a id='status_logout' name='arrow_right_16x16' class='menuTooltip changeImg highlights-text-hover' title='Logout' href='{$logout['href']}'><img src='$wgServer$wgScriptPath/{$config->getValue('iconPath')}arrow_right_16x16.png' /></a>";
	            }
	        }
	        echo "</div>";
            if(!TESTING && $wgScriptPath != "" && !DEMO){
                exec("git rev-parse HEAD", $output);
                $revId = @substr($output[0], 0, 10);
                exec("git rev-parse --abbrev-ref HEAD", $output);
                $branch = @$output[1];
                $revIdFull = "<a class='highlights-text-hover' title='{$output[0]}' target='_blank' href='https://github.com/UniversityOfAlberta/GrandForum/commit/{$output[0]}'>$revId</a>";
                $branchFull = "<a class='highlights-text-hover' title='$branch' target='_blank' href='https://github.com/UniversityOfAlberta/GrandForum/tree/$branch'>$branch</a>";
                $docs = "<a class='highlights-text-hover' title='docs' target='_blank' href='http://ssrg5.cs.ualberta.ca/rtd/docs/grand-forum/en/latest/'>Docs</a>";
                
                if(strstr($wgScriptPath, "staging") !== false){
                    echo "<div style='position:absolute;top:15px;left:525px;'>
                            STAGING ($branchFull, $revIdFull), $docs&nbsp;&nbsp;<a target='_blank' href='http://grand.cs.ualberta.ca/~dwt/behat_test/symfony/output/output.html'><img src='http://grand.cs.ualberta.ca/~dwt/behat_test/testSuiteStatus.php' /></a></div>";
                }
                else{
                    echo "<div style='position:absolute;top:15px;left:525px;'>
                            DEVELOPMENT ($branchFull, $revIdFull), $docs&nbsp;&nbsp;<a target='_blank' href='http://grand.cs.ualberta.ca/~dwt/behat_test/symfony/output/output.html'><img src='http://grand.cs.ualberta.ca/~dwt/behat_test/testSuiteStatus.php' /></a></div>";
                }
            }
            if($config->getValue('globalMessage') != ""){
                $wgMessage->addInfo($config->getValue('globalMessage'));
            }
        ?>
    </div>
    <div id="outerHeader" class=' <?php if(isset($_COOKIE['sideToggled']) && $_COOKIE['sideToggled'] == 'in') echo "menu-in";?>'>
        <div id="sideToggle">
            <?php if(isset($_COOKIE['sideToggled']) && $_COOKIE['sideToggled'] == 'in') { echo "&gt;"; } else { echo "&lt;";}?>
        </div>
	    <div id="header">
		    <a name="top" id="contentTop"></a>
            <ul class="top-nav">
            <?php 
		        global $notifications, $notificationFunctions, $wgUser, $wgScriptPath, $wgMessage, $config;
                $GLOBALS['tabs'] = array();
                
                $GLOBALS['tabs']['Other'] = TabUtils::createTab("", "");
                $GLOBALS['tabs']['Main'] = TabUtils::createTab($config->getValue("networkName"), "$wgServer$wgScriptPath/index.php/Main_Page");
                $GLOBALS['tabs']['Profile'] = TabUtils::createTab("My Profile");
                $GLOBALS['tabs']['Manager'] = TabUtils::createTab("Manager");
                
	            wfRunHooks('TopLevelTabs', array(&$GLOBALS['tabs']));
	            wfRunHooks('SubLevelTabs', array(&$GLOBALS['tabs']));
            ?>
		    <?php 
			    global $wgUser, $wgScriptPath, $tabs;
			    $selectedFound = false;
			    foreach($tabs as $key => $tab){
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
	           	}
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
	    <div id='submenu'>
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
		           	        else{
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
	<div id="mBody">
		<div id="bodyContent" class=' <?php if(isset($_COOKIE['sideToggled']) && $_COOKIE['sideToggled'] == 'in') echo "menu-in";?>'>
			<?php if($this->data['sitenotice']) { ?><div id="siteNotice"><?php $this->html('sitenotice') ?></div><?php } ?>
			<h1><?php $this->text('title') ?></h1>
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
		    <?php if($this->data['copyrightico']) { ?><div id="f-copyrightico"><?php $this->html('copyrightico') ?></div><?php } ?></td><td align="center">
    <?php	// Generate additional footer links
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
    ?>					<li id="f-<?php echo$aLink?>"><?php $this->html($aLink) ?></li>
    <?php 			}
			    }
		    }
		echo "<li id='f-disclaimer'><a target='_blank' href='{$config->getValue('networkSite')}'>{$config->getValue('networkName')} Website</a></li>\n";
	    echo "<li id='f-disclaimer'><a href='mailto:{$config->getValue('supportEmail')}'>Support</a></li>\n";
    ?>
    </ul>Icons by <a href="http://somerandomdude.com/work/iconic/" target='_blank'>Iconic</a> & <a href="http://glyphicons.com/" target='_blank'>Glyphicons</a>.</td><td align="right" width="1%" nowrap="nowrap"><?php if($this->data['poweredbyico']) { ?><div id="f-poweredbyico"><?php $this->html('poweredbyico') ?></div><?php } ?></td></tr></table><img style='display:none;' src='<?php echo "$wgServer$wgScriptPath"; ?>/skins/Throbber.gif' alt='Throbber' />
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
	global $wgServer, $wgScriptPath, $wgUser, $wgRequest, $wgAuth, $wgTitle, $config;
	    $GLOBALS['toolbox'] = array();
        
        $GLOBALS['toolbox']['People'] = TabUtils::createToolboxHeader("People");
        $GLOBALS['toolbox']['Products'] = TabUtils::createToolboxHeader(Inflect::pluralize($config->getValue('productsTerm')));
        $GLOBALS['toolbox']['Other'] = TabUtils::createToolboxHeader("Other");
        
		if($wgUser->isLoggedIn()){
		    if(isset($_GET['returnto'])){
		        redirect("$wgServer$wgScriptPath/index.php/{$_GET['returnto']}");
		    }
		    $me = Person::newFromWgUser();
		    wfRunHooks('ToolboxHeaders', array(&$GLOBALS['toolbox']));
	        wfRunHooks('ToolboxLinks', array(&$GLOBALS['toolbox']));
	        $GLOBALS['toolbox']['Other']['links'][1000] = TabUtils::createToolboxLink("Upload File", "$wgServer$wgScriptPath/index.php/Special:Upload");
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
	        $GLOBALS['toolbox']['Other']['links'][9999] = TabUtils::createToolboxLink("Other Tools", "$wgServer$wgScriptPath/index.php/Special:SpecialPages");
	        global $toolbox;
	        $i = 0;
	        foreach($toolbox as $key => $header){
	            if(count($header['links']) > 0){
	                $hr = ($i > 0) ? "<hr />" : "";
	                echo "<span class='highlights-text'>{$hr}{$header['text']}</span><ul class='pBody'>";
	                ksort($header['links']);
	                foreach($header['links'] as $lKey => $link){
	                    echo "<li><a class='highlights-background-hover' href='{$link['href']}'>{$link['text']}</a></li>";
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
		        $person = Person::newFromName($_POST['wpName']);
		        $user = User::newFromName($_POST['wpName']);
		        if($user == null || $user->getId() == 0 || $user->getName() != $_POST['wpName']){
		            $failMessage = "<p class='inlineError'>There is no user by the name of <b>{$_POST['wpName']}</b>.  If you are an HQP and do not have an account, please ask your supervisor to create one for you.<br />";
		            if(isset($_POST['wpMailmypassword'])){
		                $failMessage .= "<b>Password request failed</b>";
		            }
		            $failMessage .= "</p>";
		        }
		        else if(isset($_POST['wpMailmypassword'])){
		            $user = User::newFromName($_POST['wpUsername']);
		            $user->load();
		            $failMessage = "<p>A new password has been sent to the e-mail address registered for &quot;{$_POST['wpName']}&quot;.  Please wait a few minutes for the email to appear.  If you do not recieve an email, then contact <a class='highlights-text-hover' style='padding: 0;background:none;display:inline;border-width: 0;' href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a>.<br /><b>NOTE: Only one password reset can be requested every 10 minutes.</b></p>";
		        }
		        else{
		            $failMessage = "<p>Incorrect password entered. Please try again.</p>";
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
		        $wgOut->clearHTML();
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
Your login ID is a concatenation of your first and last names: <b>First.Last</b> (case sensitive)
If you have forgotten your password please enter your login and ID and request a new random password to be sent to the email address associated with your Forum account.</p></td></tr>";
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
		                $('#failMessage').html(message);
		            }
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
		    
		    echo "<span class='highlights-text'>Login</span>
			<ul class='pBody'>";
		    echo <<< EOF
<form style='position:relative;left:5px;' name="userlogin" method="post" action="$wgServer$wgScriptPath/index.php?title=Special:UserLogin&amp;action=submitlogin&amp;type=login&amp;returnto={$returnTo}">
	<table style='width:185px;'>
	    $message
		<tr class='tooltip' title="Your username is in the form of 'First.Last' (case-sensitive)">
			<td valign='middle' align='right' style='width:1%;'>Username:</td>
			<td class="mw-input">
				<input type='text' class='loginText dark' style='width:97%;' name="wpName" value="$name" id="wpName1"
					tabindex="1" size='20' />
			</td>
		</tr>
		<tr>
			<td valign='middle' align='right' style='width:1%;'><label for='wpPassword1'>Password:</label></td>
			<td class="mw-input">
				<input type='password' class='loginPassword dark' style='width:97%' name="wpPassword" id="wpPassword1"
					tabindex="2" size='20' />
			</td>
		</tr>
		    <tr><td colspan="2"><br /></td></tr>
		<tr>
			<!--td></td-->
			<td colspan="2" class="mw-input">
				<input type='checkbox' name="wpRemember"
					tabindex="4"
					value="1" id="wpRemember"
										/> <label for="wpRemember">Remember my login on this computer</label>
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
?>
	</li>
<?php
	}

} // end of class
