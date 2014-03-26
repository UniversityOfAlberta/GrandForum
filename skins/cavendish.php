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


if( !defined( 'MEDIAWIKI' ) )
	die();

/** */
require_once('includes/SkinTemplate.php');

/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 * @todo document
 * @package MediaWiki
 * @subpackage Skins
 */
class Skincavendish extends SkinTemplate {
	/** Using cavendish. */
	function initPage( &$out ) {
		SkinTemplate::initPage( $out );
		$this->skinname  = 'cavendish';
		$this->stylename = 'cavendish';
		$this->template  = 'CavendishTemplate';
	}
}
	
class cavendishTemplate extends QuickTemplate {
	/**
	 * Template filter callback for cavendish skin.
	 * Takes an associative array of data set from a SkinTemplate-based
	 * class, and a wrapper for MediaWiki's localization database, and
	 * outputs a formatted page.
	 *
	 * @access private
	 */
	function execute() {
		global $wgRequest, $wgServer, $wgScriptPath, $wgLogo, $wgTitle, $wgUser, $wgMessage, $wgImpersonating, $wgTitle, $config;
		$this->skin = $skin = $this->data['skin'];
		$action = $wgRequest->getText( 'action' );

        if(FROZEN){
            $wgMessage->addInfo("The Forum is currently not available for edits during the RMC review-and-deliberation period.");
        }

        // if($wgUser->isLoggedIn() && $wgTitle != null && $wgTitle->getNsText() == "Special" && $wgTitle->getText() == "Report"){
        //     $wgMessage->addInfo("The 2012 Report pages are now closed for edits.");
        // }

		// Suppress warnings to prevent notices about missing indexes in $this->data
		wfSuppressWarnings();
		
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php $this->text('lang') ?>" lang="<?php $this->text('lang') ?>" dir="<?php $this->text('dir') ?>">
	<head>
		<meta http-equiv="Content-Type" content="<?php $this->text('mimetype') ?>; charset=<?php $this->text('charset') ?>" />
		<?php $this->html('headlinks') ?>
		<title><?php $this->text('pagetitle') ?></title>
		<link type="text/css" href="<?php $this->text('stylepath') ?>/smoothness/jquery-ui-1.8.21.custom.css" rel="Stylesheet" />
		<link type="text/css" href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/jquery.qtip.min.css" rel="Stylesheet" />
		
		<?php $this->html('csslinks') ?>
		
		
		<link type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/DataTables/css/jquery.dataTables.css" rel="Stylesheet" />
		<link type="text/css" rel="stylesheet" href="<?php echo "$wgServer$wgScriptPath"; ?>/skins/simplePagination/simplePagination.css" />
		<?php /*** browser-specific style sheets ***/ ?>
		<!--[if lt IE 5.5000]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE50Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
		<!--[if IE 5.5000]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE55Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
		<!--[if IE 6]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE60Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
		<!--[if IE 7]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE70Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
		<?php /*** general IE fixes ***/ ?>
		<!--[if lt IE 7]>
		<script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath') ?>/common/IEFixes.js?<?php echo $GLOBALS['wgStyleVersion'] ?>"></script>
		<meta http-equiv="imagetoolbar" content="no" />
		<![endif]-->
		<?php print Skin::makeGlobalVariablesScript( $this->data ); ?>
		
		<script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/date.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/inflection.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/to-title-case.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/excanvas.min.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/countries.en.js"></script>

        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery-ui.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.browser.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.cookie.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.resizeY.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.limit-1.2.source.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.multiLimit.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.combobox.js"></script>
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
        
            $.ajaxSetup({ cache: false });
        
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
		<script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath' ) ?>/common/wikibits.js?<?php echo $GLOBALS['wgStyleVersion'] ?>"><!-- wikibits js --></script>
		<!-- Head Scripts -->
		<?php $this->html('headscripts') ?>
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
	    
		<style type="text/css" media="screen,projection">/*<![CDATA[*/ @import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/main.css"; /*]]>*/</style>
		<style type="text/css" media="screen,projection">/*<![CDATA[*/ @import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/extensions.css"; /*]]>*/</style>
		<style <?php if(empty($this->data['printable']) ) { ?>media="print"<?php } ?> type="text/css">/*<![CDATA[*/ @import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/print.css"; /*]]>*/</style>
		
		<!--[if IE 8]>
		    <link type="text/css" href="<?php $this->text('stylepath') ?>/cavendish/ie8.css" rel="Stylesheet" />
		<![endif]-->
		<!--[if lt IE 8]>
		    <link type="text/css" href="<?php $this->text('stylepath') ?>/cavendish/oldie.css" rel="Stylesheet" />
		<![endif]-->
		<link rel="stylesheet" type="text/css" media="print" href="<?php $this->text('stylepath') ?>/common/commonPrint.css" />
		<link type="text/css" href="<?php $this->text('stylepath') ?>/switcheroo/switcheroo.css" rel="Stylesheet" />
		<link type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/tagIt/css/jquery.tagit.css" rel="Stylesheet" />
		<link type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/tagIt/css/tagit.ui-zendesk.css" rel="Stylesheet" />
		<link type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jqueryDropdown/jquery.dropdown.css" rel="Stylesheet" />
		<script type="text/javascript" src="<?php $this->text('stylepath' ) ?>/common/wikibits.js"></script>
		<link rel="stylesheet" type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/colorbox/colorbox.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/skins/cavendish/highlights.css.php" />
		<script type='text/javascript'>
		
		    // Configs
		    networkName = "<?php echo $config->getValue('networkName'); ?>";
		    extensions = <?php echo json_encode($config->getValue('extensions')); ?>;
		    iconPath = "<?php echo $config->getValue('iconPath'); ?>";
		    iconPathHighlighted = "<?php echo $config->getValue('iconPathHighlighted'); ?>";
		
		    function isExtensionEnabled(ext){
		        return (extensions.indexOf(ext) != -1);
		    }
		
		    me = new Person(
		    <?php
		        $me = Person::newFromWGUser();
		        echo $me->toJSON();
		    ?>
		    );
		    
		    function changeImg(el, img){
                $(el).attr('src', img);
            }
		    
		    function unaccentChars(str){
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
		                
		                $(this).attr('onmouseover', "changeImg($('img', $(this)), '" + light + "')");
		                $(this).attr('onmouseout', "changeImg($('img', $(this)), '" + dark + "')");
		            }
		        });
		        
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
                    case 'linkedin':
                        $img = "glyphicons_social_17_linked_in";
                        $text = "LinkedIn";
                        break;
                    case 'youtube':
                        $img = "glyphicons_social_22_youtube";
                        $text = "YouTube";
                        break;
                }
                echo "<a class='changeImg highlights-text-hover' name='$img' href='$link' target='_blank'>
	                        <img src='$wgServer$wgScriptPath/{$config->getValue('iconPath')}$img.png' />&nbsp;$text
	                  </a>";
	        }
	        echo "</div>";
            echo "<a name='question_mark_8x16' class='menuTooltip changeImg highlights-text-hover' title='Help/FAQ' href='$wgServer$wgScriptPath/index.php/Help:Contents'><img src='$wgServer$wgScriptPath/{$config->getValue('iconPath')}question_mark_8x16.png' /></a>";
            if(count($config->getValue("socialLinks")) > 0){
	            echo "<a id='share' style='cursor:pointer;' name='share_16x16' class='menuTooltipHTML changeImg highlights-text-hover'><img src='$wgServer$wgScriptPath/{$config->getValue('iconPath')}share_16x16.png' />&nbsp;▼</a>";
	        }
	        if($wgUser->isLoggedIn()){
		        $p = Person::newFromId($wgUser->getId());
		        
		        $smallNotificationText = "";
		        if(count($notifications) > 0){
		            $notificationText = " (".count($notifications).")";
		            $smallNotificationText = "*";
		        }
		        echo "<a name='mail_16x12' class='menuTooltip changeImg highlights-text-hover' title='Notifications$notificationText' href='$wgServer$wgScriptPath/index.php?action=viewNotifications' style='color:#EE0000;'><img src='$wgServer$wgScriptPath/{$config->getValue('iconPath')}mail_16x12.png' />$smallNotificationText</a>";
		        echo "<a class='menuTooltip highlights-text-hover' title='Profile' href='{$p->getUrl()}'>{$p->getNameForForms()}</a>";
		        echo "<a class='menuTooltip highlights-text-hover' title='Profile' href='{$p->getUrl()}'><img class='photo' src='{$p->getPhoto()}' /></a>";
		        if(!$wgImpersonating){
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
	                echo "<a name='arrow_right_16x16' class='menuTooltip changeImg highlights-text-hover' title='Logout' href='{$logout['href']}'><img src='$wgServer$wgScriptPath/{$config->getValue('iconPath')}arrow_right_16x16.png' /></a>";
	            }
	        }
	        
	        echo "</div>";
            if(!TESTING && $wgScriptPath != ""){
                exec("git rev-parse HEAD", $output);
                $revId = @substr($output[0], 0, 10);
                exec("git rev-parse --abbrev-ref HEAD", $output);
                $branch = @$output[1];
                $revIdFull = "<a class='highlights-text-hover' title='{$output[0]}' target='_blank' href='https://github.com/UniversityOfAlberta/GrandForum/commit/{$output[0]}'>$revId</a>";
                $branchFull = "<a class='highlights-text-hover' title='$branch' target='_blank' href='https://github.com/UniversityOfAlberta/GrandForum/tree/$branch'>$branch</a>";
                
                if(strstr($wgScriptPath, "staging") !== false){
                    echo "<div style='position:absolute;top:15px;left:525px;'>
                            STAGING ($branchFull, $revIdFull)</div>";
                }
                else{
                    echo "<div style='position:absolute;top:15px;left:525px;'>
                            DEVELOPMENT ($branchFull, $revIdFull)</div>";
                }
            }
            //$wgMessage->addPurpleInfo("Important: The GRAND Forum will be down for maintenance from 1-2AM (MST), Sun, Dec. 15.");
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
                    
                    $GLOBALS['tabs']['Main'] = TabUtils::createTab($config->getValue("networkName"), "$wgServer$wgScriptPath/index.php/Main_Page");
                    $GLOBALS['tabs']['Profile'] = TabUtils::createTab("My Profile");
                    $GLOBALS['tabs']['Manager'] = TabUtils::createTab("Manager");
                    
			        wfRunHooks('TopLevelTabs', array(&$GLOBALS['tabs']));
			        wfRunHooks('SubLevelTabs', array(&$GLOBALS['tabs']));
		      ?>
			    
			    <?php global $wgImpersonating;
			        foreach($this->data['personal_urls'] as $key => $item) {
			        //echo $key;
			        $selected = "";
			        $tabLeft = "";
			        if($key == "userpage"){
			            $user = Person::newFromName($wgUser->getName());
			            if(count($user->getRoles()) > 0){
			                if($wgTitle->getText() == $user->getName() && $user->isRole($wgTitle->getNSText())){
			                    $selected = "selected";
			                }
			                $item['href'] = "{$user->getUrl()}";
			                $item['text'] = "My Profile";
			            }
			        }
			        else if($key == "mytalk" || $key == "mycontris" || $key == "watchlist" || $key == "anonuserpage" || $key == "anontalk" || $key == "preferences"){
			            continue;
			        }
			        else if($key == "logout"){
			            continue;
			        }
			        else if($key == "anonlogin"){
			            continue;
			        }
			    ?>
			
			    <!--li class="top-nav-element <?php echo $selected.' '.$tabLeft; ?>">
				    <span class="top-nav-left">&nbsp;</span>
				    <a id="lnk-<?php echo $key; ?>" class="top-nav-mid highlights-tab" href="<?php
					    echo htmlspecialchars($item['href']) ?>"<?php
					    if(!empty($item['class'])) { ?> class="<?php
						       echo htmlspecialchars($item['class']) ?>"<?php } ?>><?php
						       echo $item['text'] ?></a>
				    <span class="top-nav-right">&nbsp;</span>
				    <?php
				    } ?>
			    </li-->
			    
			    <?php 
				    global $wgUser, $wgScriptPath, $tabs;
				    $selectedFound = false;
				    foreach($tabs as $key => $tab){
				        if($tabs[$key]['href'] == "" && isset($tabs[$key]['subtabs'][0])){
				            $tabs[$key]['href'] = $tab['subtabs'][0]['href'];
				        }
		           	    foreach($tab['subtabs'] as $subtab){
		           	        if(strstr($subtab['selected'], "selected") !== false){
		           	            $tabs[$key]['selected'] = "selected";
		           	            $selectedFound = true;
		           	        }
		           	    }
		           	}
		           	if(!$selectedFound){
		           	    // If a selected tab wasn't found, just default to the Main Tab
		           	    $tabs['Main']['selected'] = "selected";
		           	}
				    foreach($tabs as $key => $tab){
				        if($tab['href'] != ""){
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
		       	<?php
		       	 foreach($tabs as $tab){
		       	    if($tab['selected'] == "selected"){
		       	        foreach($tab['subtabs'] as $subtab){
		           	        echo "<li class='{$subtab['selected']}'><a class='highlights-tab' href='".htmlspecialchars($subtab['href'])."'>".htmlspecialchars($subtab['text'])."</a></li>";
		           	    }
		           	    break;
		       	    }
		       	 }
		       	 foreach($this->data['content_actions'] as $key => $action) {
		           ?><li
		           <?php if($action['class']) { ?>class="<?php echo htmlspecialchars($action['class']) ?>"<?php } ?>
		           ><a class='highlights-tab' href="<?php echo htmlspecialchars($action['href']) ?>"><?php
		           echo htmlspecialchars($action['text']) ?></a></li><?php
		         } ?>
		    </ul>
        </div>
	</div>
    
    <?php global $dropdownScript; echo $dropdownScript; ?>
    <div id="side" class=' <?php if(isset($_COOKIE['sideToggled']) && $_COOKIE['sideToggled'] == 'in') echo "menu-in";?>'>
		    <ul id="nav">
		    <?php
			    global $wgUser;
	    $sidebar = $this->data['sidebar'];
	    if ( !isset( $sidebar['TOOLBOX'] ) ) $sidebar['TOOLBOX'] = true;
	    if ( !isset( $sidebar['LANGUAGES'] ) ) $sidebar['LANGUAGES'] = true;
	    foreach ($sidebar as $boxName => $cont) {
		    if ( $boxName == 'TOOLBOX' ) {
		        $this->toolbox();
		    } elseif ( $boxName == 'LANGUAGES' ) {
			    $this->languageBox();
		    } else {
			    $this->customBox( $boxName, $cont );
		    }
	    }
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
			    'privacy', 'about', 'disclaimer', 'tagline',
		    );
		    $validFooterLinks = array();
		    foreach( $footerlinks as $aLink ) {
			    if( isset( $this->data[$aLink] ) && $this->data[$aLink] ) {
				    $validFooterLinks[] = $aLink;
			    }
		    }
		    if ( count( $validFooterLinks ) > 0 ) {
    ?>			<ul id="f-list">
    <?php
			    foreach( $validFooterLinks as $aLink ) {
				    if( isset( $this->data[$aLink] ) && $this->data[$aLink] ) {
    ?>					<li id="f-<?php echo$aLink?>"><?php $this->html($aLink) ?></li>
    <?php 			}
			    }
		    }
	    echo "<li id='f-disclaimer'><a href='mailto:support@forum.grand-nce.ca'>Support</a></li>\n";
    ?>
    </ul>Icons by <a href="http://somerandomdude.com/work/iconic/" target='_blank'>Iconic</a> & <a href="http://glyphicons.com/" target='_blank'>Glyphicons</a>.</td><td align="right" width="1%" nowrap="nowrap"><?php if($this->data['poweredbyico']) { ?><div id="f-poweredbyico"><?php $this->html('poweredbyico') ?></div><?php } ?></td></tr></table><img style='display:none;' src='<?php echo "$wgServer$wgScriptPath"; ?>/skins/Throbber.gif' alt='Throbber' />
	    </div><!-- end of the FOOTER div -->
		</div><!-- end of MAINCONTENT div -->	
	</div><!-- end of MBODY div -->
	<div id="recordDiv"></div>
</div><!-- end of the CONTAINER div -->
<?php $this->html('bottomscripts'); /* JS call to runBodyOnloadHook */ ?>
<?php $this->html('reporttime') ?>

</body>
</html>

<?php
	}
/*************************************************************************************************/
	function toolbox() {
?>
	<li class="portlet" id="p-tb">
<?php
	global $wgScriptPath, $wgUser, $wgRequest;
		if($wgUser->isLoggedIn()){
		    $me = Person::newFromId($wgUser->getId());
		    if($me->isRoleAtLeast(CNI)){
		        echo "<span class='highlights-text'>People</span>
			    <ul class='pBody'>";
		        //echo "<li id='userRequest'><a href='{$wgScriptPath}/index.php/Special:UserSearch'>Find Member</a></li>";
		        echo "<li id='userRequest'><a class='highlights-background-hover' href='{$wgScriptPath}/index.php/Special:AddMember'>Add Member</a></li>";
		        echo "<li id='userEditRequest'><a class='highlights-background-hover' href='{$wgScriptPath}/index.php/Special:EditMember'>Edit Member</a></li>";
		        echo "<li id='userEditRelation'><a class='highlights-background-hover' href='{$wgScriptPath}/index.php/Special:EditRelations'>Edit Relations</a></li>";
		        echo "</ul>";
		    }
		    echo "<span class='highlights-text'><hr />Products</span>
				<ul class='pBody'>";
		    echo "<li id='addPublication'><a class='highlights-background-hover' href='{$wgScriptPath}/index.php/Special:AddPublicationPage'>Add/Edit Publication</a></li>";
		    echo "<li id='addArtifact'><a class='highlights-background-hover' href='{$wgScriptPath}/index.php/Special:AddArtifactPage'>Add/Edit Artifact</a></li>";
		    echo "<li id='addPresentation'><a class='highlights-background-hover' href='{$wgScriptPath}/index.php/Special:AddPresentationPage'>Add/Edit Presentation</a></li>";
			echo "<li id='addActivity'><a class='highlights-background-hover' href='{$wgScriptPath}/index.php/Special:AddActivityPage'>Add/Edit Activity</a></li>";
			echo "<li id='addPress'><a class='highlights-background-hover' href='{$wgScriptPath}/index.php/Special:AddPressPage'>Add/Edit Press</a></li>";
			echo "<li id='addAward'><a class='highlights-background-hover' href='{$wgScriptPath}/index.php/Special:AddAwardPage'>Add/Edit Award</a></li>";
			if($me->isRoleAtLeast(CNI)){
			    echo "<li id='addContribution'><a class='highlights-background-hover' href='{$wgScriptPath}/index.php/Special:AddContributionPage'>Add/Edit Contribution</a></li>";
			}
			echo "<li id='addMultimedia'><a class='highlights-background-hover' href='{$wgScriptPath}/index.php/Special:AddMultimediaStoryPage'>Add/Edit Multimedia Story</a></li>";
			echo "</ul>";
			echo "<ul class='pBody'>";
			echo "<li id='addMultimedia'><a class='highlights-background-hover' href='{$wgScriptPath}/index.php/Special:MyDuplicateProducts'>Duplicate Management</a></li>";
			//echo "<li id='sanityChecks'><a class='highlights-background-hover' href='{$wgScriptPath}/index.php/Special:SanityChecks'>Data Quality Issues</a></li>";
			echo "</ul>";
		    echo "<span class='highlights-text'><hr />Other</span>
				<ul class='pBody'>";
			echo "<li id='grandinstructions'><a class='highlights-background-hover' href='{$wgScriptPath}/index.php/GRAND:Instructions'>Instructions</a></li>";
			//echo "<li id='recentNews'><a class='highlights-background-hover' href='{$wgScriptPath}/index.php?action=getNews'>Recent News</a></li>";
			//echo "<li id='recentNews'><a class='highlights-background-hover' href='{$wgScriptPath}/index.php/Special:Solr'>Full Text Search</a></li>";
			echo "<li id='academiamap'><a class='highlights-background-hover' href='{$wgScriptPath}/index.php/Special:AcademiaMap'>Academia Map</a></li>";
			echo "<li id='othertools'><a class='highlights-background-hover' href='{$wgScriptPath}/index.php/Special:SpecialPages'>Other Tools</a></li>";
			if($me->isRoleAtLeast(HQP)){
				echo "<li id='recentNews'><a class='highlights-background-hover' href='{$wgScriptPath}/index.php/Special:LoiProposals'>LOI Proposals</a></li>";
			}
			echo "<li id='sanityChecks'><a class='highlights-background-hover' href='{$wgScriptPath}/index.php/Special:AdvancedSearch'>Search for Experts</a></li>";
		}
		else {
		    global $wgSiteName, $wgTitle;
		    setcookie('sideToggled', 'out', time()-3600);
		    $loginFailed = (isset($_POST['wpLoginattempt']) || isset($_POST['wpMailmypassword']));
		    if($loginFailed){
		        $person = Person::newFromName($_POST['wpName']);
		        if($person == null || $person->getName() == ""){
		            $failMessage = "<p class='inlineError'>There is no user by the name of <b>{$_POST['wpName']}</b>.  If you are an HQP and do not have an account, please ask your supervisor to create one for you.<br />";
		            if(isset($_POST['wpMailmypassword'])){
		                $failMessage .= "<b>Password request failed</b>";
		            }
		            $failMessage .= "</p>";
		        }
		        else if(isset($_POST['wpMailmypassword'])){
		            $user = User::newFromName($_POST['wpName']);
		            $user->load();
		            $failMessage = "<p>A new password has been sent to the e-mail address registered for \"{$_POST['wpName']}\".  Please wait a few minutes for the email to appear.  If you do not recieve an email, then contact <a style='padding: 0;background:none;display:inline;border-width: 0;' href='mailto:support@forum.grand-nce.ca'>support@forum.grand-nce.ca</a>.<br /><b>NOTE: Only one password reset can be requested every half hour.</b></p>";
		        }
		        else{
		            $failMessage = "<p>Incorrect password entered. Please try again.</p>";
		        }
		        $message = "<tr><td colspan='2'>$failMessage
<p>
You must have cookies enabled to log in to $wgSiteName.<br />
</p>
<p>
Your login ID is a concatenation of your first and last names: <b>First.Last</b> (case sensitive)
If you have forgotten your password please enter your login and ID and request a new random password to be sent to the email address associated with your Forum account.</p></td></tr>";
		        $emailPassword = "<br /><br /><input class='dark' type='submit' name='wpMailmypassword' id='wpMailmypassword' tabindex='6' value='E-mail new password' />";
		    }
		    if($_SESSION == null || 
		       $wgRequest->getSessionData('wsLoginToken') == "" ||
		       $wgRequest->getSessionData('wsLoginToken') == null){
		        wfSetupSession();
		        LoginForm::setLoginToken();
		    }
		    $wgUser->setCookies();
		    $token = LoginForm::getLoginToken();
		    $name = $wgRequest->getText('wpName');
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
		    $returnTo = "";
		    if(isset($_GET['returnto'])){
		        $returnTo = $_GET['returnto'];
		    }
		    else if($wgTitle->getNsText() != ""){
		        $returnTo .= str_replace(" ", "_", $wgTitle->getNsText()).':';
		    }
		    
		    if(!isset($_GET['returnto'])){
		        $returnTo .= str_replace(" ", "_", $wgTitle->getText());
		    }
		    $returnTo .= $getStr;
		    $returnTo = urlencode($returnTo);
		    echo "<span>Login</span>
			<ul class='pBody'>";
		    echo <<< EOF
		    <li style='padding:5px;'>
<form style='position:relative;left:5px;' name="userlogin" method="post" action="$wgServer$wgScriptPath/index.php?title=Special:UserLogin&amp;action=submitlogin&amp;type=login&amp;returnto={$returnTo}">
	<table style='width:185px;'>
	    $message
		<tr class='tooltip' title="Your username is in the form of 'First.Last' (case-sensitive)">
			<td valign='middle' align='right'>Username:</td>
			<td class="mw-input">
				<input type='text' class='loginText dark' style='width:97%;' name="wpName" value="$name" id="wpName1"
					tabindex="1" size='20' />
			</td>
		</tr>
		<tr>
			<td valign='middle' align='right'><label for='wpPassword1'>Password:</label></td>
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
			<!--td></td-->
			<td colspan="2" class="mw-submit">
				<input type='submit' class='dark' name="wpLoginattempt" id="wpLoginattempt" tabindex="5" value="Log in" />$emailPassword
							</td>
		</tr>
	</table>
<input type="hidden" name="wpLoginToken" value="$token" /></form></li>
EOF;
        }
		wfRunHooks( 'MonoBookTemplateToolboxEnd', array( &$this ) );
		wfRunHooks( 'SkinTemplateToolboxEnd', array( &$this ) );
?>
			</ul>
	</li>
<?php
	}

	/*************************************************************************************************/
	function languageBox() {
		if( $this->data['language_urls'] ) {
?>
	<li id="p-lang" class="portlet">
		<span><?php $this->msg('otherlanguages') ?></span>
			<ul class="pBody">
<?php		foreach($this->data['language_urls'] as $langlink) { ?>
				<li class="<?php echo htmlspecialchars($langlink['class'])?>"><?php
				?><a href="<?php echo htmlspecialchars($langlink['href']) ?>"><?php echo $langlink['text'] ?></a></li>
<?php		} ?>
			</ul>
	</li>
<?php
		}
	}

	/*************************************************************************************************/
	function customBox( $bar, $cont ) {
?>
	<li class='generated-sidebar portlet' id='<?php echo Sanitizer::escapeId( "p-$bar" ) ?>'<?php echo $this->skin->tooltip('p-'.$bar) ?>>
		<span><?php $out = wfMsg( $bar ); if (wfEmptyMsg($bar, $out)) echo $bar; else echo $out; ?></span>
<?php   if ( is_array( $cont ) ) { ?>
			<ul class='pBody'>
<?php
	global $wgUser;
	 			foreach($cont as $key => $val) {
					if(($val['id'] == "n-recentchanges" && $wgUser->isLoggedIn()) || $val['id'] != "n-recentchanges"){ ?>
					<li id="<?php echo Sanitizer::escapeId($val['id']) ?>"<?php
						if ( $val['active'] ) { ?> class="active" <?php }
					?>><a href="<?php echo htmlspecialchars($val['href']) ?>"<?php echo $this->skin->tooltipAndAccesskey($val['id']) ?>><?php echo htmlspecialchars($val['text']) ?></a></li>
<?php				}
			} ?>
			</ul>
<?php   } else {
			# allow raw HTML block to be defined by extensions
			print $cont;
		}
?>
	</li>
<?php
	}

} // end of class
