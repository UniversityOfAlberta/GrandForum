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
	function initPage( $out ) {
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
		global $wgRequest, $wgServer, $wgScriptPath, $wgOut, $wgLogo, $wgTitle, $wgUser, $wgMessage, $wgImpersonating, $wgDelegating, $wgTitle, $config, $wgLang;
		$this->skin = $skin = $this->data['skin'];
		$action = $wgRequest->getText( 'action' );

		// Suppress warnings to prevent notices about missing indexes in $this->data
		//Wikimedia\AtEase\AtEase::suppressWarnings();
		
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php $this->text('lang') ?>" lang="<?php $this->text('lang') ?>" dir="<?php $this->text('dir') ?>">
	<head>
		<meta http-equiv="Content-Type" content="<?php $this->text('mimetype') ?>; charset=<?php $this->text('charset') ?>" />

		<title><?php $this->text('pagetitle') ?></title>
		<link type="image/x-icon" href="<?php echo $wgServer.$wgScriptPath.'/favicon.png'; ?>" rel="shortcut icon" />
		<link type="text/css" href="<?php $this->text('stylepath') ?>/smoothness/jquery-ui-1.8.21.custom.css" rel="Stylesheet" />
		<link type="text/css" href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/jquery.qtip.min.css" rel="Stylesheet" />
		<link type="text/css" href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/chosen/chosen.css.php" rel="Stylesheet" />
		<?php //$this->html('csslinks') ?>

		<link rel="stylesheet" href="<?php $this->text('stylepath') ?>/common/shared.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="<?php $this->text('stylepath') ?>/common/commonPrint.css" type="text/css" media="print" />
		<link rel="stylesheet" href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/print.css" type="text/css" media="print" />
		
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
                <link rel="stylesheet" type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/markitup/skins/markitup/style.css" />
                <link rel="stylesheet" type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/markitup/sets/wiki/style.css" />
		
		<style>
		    <?php
		        
		        if($wgLang->getCode() == "en"){
		            echo ".fr { display: none !important; }";
		        }
		        else if($wgLang->getCode() == "fr"){
		            echo ".en { display: none !important; }";
		        }
		        
		    ?>
		</style>
		
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
        <script language="javascript" type="text/javascript" src="https://maps.google.com/maps/api/js?&libraries=places&key=<?php echo $config->getValue('googleAPI'); ?>"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/raphael.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/scale.raphael.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/spinner.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/filter.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/autosave.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/extensions/Messages/messages.js"></script>
        
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/d3.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/html2canvas.js"></script>
    
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/underscore-min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/backbone-min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/backbone-subviews.js"></script>
        <!--script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/backbone-relational-min.js"></script>-->
        <script type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.simplePagination.js"></script>
        <script type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/markitup/jquery.markitup.js"></script>
        <script type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/markitup/sets/wiki/set.js"></script>

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
	<style>
	<?php
	if($wgLang->getCode() == 'en'){
            echo ".fr{display:none;}";
         }
	elseif($wgLang->getCode() == 'fr'){
   	    echo ".en{display:none;}";
	}
	?>
	</style>	
		<!-- Head Scripts -->
		<script type="text/javascript">
		    var wgServer = "<?php echo $wgServer; ?>";
		    var wgScriptPath = "<?php echo $wgScriptPath; ?>";
		    var wgUserName = "<?php echo $wgUser->getName(); ?>";
		    var wgLang = "<?php echo $wgLang->getCode(); ?>";
		</script>
		<?php echo $wgOut->getBottomScripts(); ?>
		

		<?php createModels(); ?>
		<script type='text/javascript'>
		
		    // Configs
		    allowedRoles = <?php $me = Person::newFromWGUser(); echo json_encode($me->getAllowedRoles()); ?>;
		    allowedProjects = <?php $me = Person::newFromWGUser(); echo json_encode($me->getAllowedProjects()); ?>;
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
	        
if(wgLang == 'en'){
	    $.extend( true, $.fn.dataTable.defaults, {
		    "language":{
        "decimal":        "",
        "emptyTable":     "No data available in table",
        "info":           "Showing _START_ to _END_ of _TOTAL_ entries",
        "infoEmpty":      "Showing 0 to 0 of 0 entries",
        "infoFiltered":   "(filtered from _MAX_ total entries)",
        "infoPostFix":    "",
        "thousands":      ",",
        "lengthMenu":     "Show _MENU_ entries",
        "loadingRecords": "Loading...",
        "processing":     "Processing...",
        "search":         "Search:",
        "zeroRecords":    "No matching records found",
        "paginate": {
            "first":      "First",
            "last":       "Last",
            "next":       "Next",
            "previous":   "Previous"
        },
        "aria": {
            "sortAscending":  ": activate to sort column ascending",
            "sortDescending": ": activate to sort column descending"
        }
    }
});
}
	if(wgLang == 'fr'){
            $.extend( true, $.fn.dataTable.defaults, {
                            "language":{
                "decimal":        "",
                "emptyTable":     "Aucune Donnée Disponible",
                "info":           "Affichage _START_ à _END_ des entrées de _TOTAL_",
                "infoEmpty":      "Affichage 0 à 0 de 0 entrées",
                "infoFiltered":   "(Filtré _Max_ Entrées Totales)",
                "infoPostFix":    "",
                "thousands":      ",",
                "lengthMenu":     "Afficher les entrées de _MENU_",
                "loadingRecords": "Chargement...",
                "processing":     "En traitement...",
                "search":         "Chercher:",
                "zeroRecords":    "Aucun enregistrements correspondants trouvés",
                "paginate": {
                    "first":      "Premier",
                    "last":       "Dernier",
                    "next":       "Prochain",
                    "previous":   "Précédent"
                },              
                "aria": {           
                    "sortAscending":  ": activer pour trier la colonne ascendante",
                    "sortDescending": ": activer pour trier la colonne descendante"
                }           
            }   
	    } );
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

            setInterval(function(){
                    if(wgLang == 'en'){
                        $('#en_button').css('font-weight','bold');
                        $('.en').show();
                        $('.fr').remove();
                    }
                    else{
                        $('#fr_button').css('font-weight','bold');
                        $('.fr').show();
                        $('.en').remove();
                    }

                    if(me.isLoggedIn()){
                        $('.welcome').show();
                        $('.intro').remove();
                    }
                    if(!me.isLoggedIn()){
                        $('.welcome').remove();
                        $('.intro').show();
                    }
                }, 100);
                
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
		        $('textarea[name=wpTextbox1]').markItUp(myWikiSettings); 
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
<body class="mediawiki ltr ns-0 ns-subject skin-cavendish">

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
        echo "<script language='javascript'>

$(function(){
    $('en_button').click(function(){
        $.get('/skins/cavendish/cavendish.php', function(){
        });
    });
    $('fr_button').click(function(){
        $.get('/skins/cavendish/cavendish.php', {linkText: $(this).text()}, function(resp){
           // handle response here
        }, 'json');
    });

});

</script>";
            echo "<div class='smallLogo'><a href='{$this->data['nav_urls']['mainpage']['href']}' title='$wgSitename'><img src='$wgServer$wgScriptPath/{$config->getValue('logo')}' /></a></div>";
            echo "<div class='search'><div id='globalSearch'></div></div>";
            if($wgUser->isLoggedIn()){
                echo "<div class='settings'>";
            }
            else{
                echo "<div class='settings' style='right:4px;border-bottom-right-radius:10px'>";
            }
            echo "<a href='?lang=en' id='en_button'>English</a> &nbsp<a href='?lang=fr' id='fr_button'>Français</a> &nbsp";
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
            echo "<a id='status_help_faq' name='question_mark_8x16' class='menuTooltip changeImg' title='Help' href='$wgServer$wgScriptPath/index.php/Help:Contents'><img src='$wgServer$wgScriptPath/{$config->getValue('iconPath')}question_mark_8x16.png' /></a>";
            if(count($config->getValue("socialLinks")) > 0){
                echo "<a id='share' style='cursor:pointer;' name='share_16x16' class='menuTooltipHTML changeImg'><img src='$wgServer$wgScriptPath/{$config->getValue('iconPath')}share_16x16.png' />&nbsp;▼</a>";
            }
            if($wgUser->isLoggedIn()){
                $p = Person::newFromId($wgUser->getId());
                
                $smallNotificationText = "";
                $notificationText = "";
                if(count($notifications) > 0){
                    $notificationText = " (".count($notifications).")";
                    $smallNotificationText = "<img class='overlay' style='margin-left:-16px;' src='$wgServer$wgScriptPath/{$config->getValue('iconPath')}mail_16x12_red.png' />";
                }
                echo "<a id='status_notifications' name='mail_16x12' class='menuTooltip changeImg' title='Notifications$notificationText' href='$wgServer$wgScriptPath/index.php?action=viewNotifications' style='color:#EE0000;'><img src='$wgServer$wgScriptPath/{$config->getValue('iconPath')}mail_16x12.png' />$smallNotificationText</a>";
            }
            echo "</div>";
            if($wgUser->isLoggedIn()){
                echo "<div class='login'>";
                echo "<a id='status_profile_photo' class='menuTooltip' style='padding-left:0;margin-left:10px; width:26px;' title='Profile' href='{$p->getUrl()}'><img class='photo' src='{$p->getPhoto()}' /></a>";
                if(!$wgImpersonating && !$wgDelegating){
                    $logoutUrl = urlencode("{$wgServer}{$_SERVER['REQUEST_URI']}");
                    echo "<a id='status_logout' name='arrow_right_16x16' class='changeImg' style='font-size: 13px;line-height:12px;display:inline-block;width:74px;' title='Logout' href='{$wgServer}{$wgScriptPath}/index.php?action=logout&returnto={$logoutUrl}'>Logout&nbsp;&nbsp;&nbsp;<span style='color:white;font-size:28px;vertical-align: middle;display: inline-block; width:18px;text-decoration:none;'>&#12297;</span></a>";
                }
                echo "</div>";
            }
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
                            STAGING ($branchFull, $revIdFull), $docs&nbsp;&nbsp;<a target='_blank' href='http://grand.cs.ualberta.ca/~dwt/caps_test/symfony/output/output.html'><img src='http://grand.cs.ualberta.ca/~dwt/caps_test/testSuiteStatus.php' /></a></div>";
                }
                else{
                    echo "<div style='position:absolute;top:15px;left:525px;'>
                            DEVELOPMENT ($branchFull, $revIdFull), $docs&nbsp;&nbsp;<a target='_blank' href='http://grand.cs.ualberta.ca/~dwt/caps_test/symfony/output/output.html'><img src='http://grand.cs.ualberta.ca/~dwt/caps_test/testSuiteStatus.php' /></a></div>";
                }
            }
            if($config->getValue('globalMessage') != ""){
                $wgMessage->addInfo($config->getValue('globalMessage'));
            }
        ?>
    </div>
    <div id="outerHeader" class=' <?php if(isset($_COOKIE['sideToggled']) && $_COOKIE['sideToggled'] == 'in') echo "menu-in";?>'>
        <!--div id="sideToggle">
            <?php if(isset($_COOKIE['sideToggled']) && $_COOKIE['sideToggled'] == 'in') { echo "&#12297;"; } else { echo "&#12296;";}?>
        </div>
	    <div id="header">
		    <a name="top" id="contentTop"></a>
            <ul class="top-nav">
            <?php 
		        global $notifications, $notificationFunctions, $wgUser, $wgScriptPath, $wgMessage, $config, $wgLang;
                $GLOBALS['tabs'] = array();
                
                $GLOBALS['tabs']['Other'] = TabUtils::createTab("", "");
                $title = "CAPS";
                if($wgLang->getCode() == "fr"){
                    $title ="CPCA";
                }
                $GLOBALS['tabs']['Main'] = TabUtils::createTab($title, "$wgServer$wgScriptPath/index.php/Main_Page");
		        $title = "My Profile";
		        if($wgLang->getCode() == "fr"){
		            $title ="Mon Profil";
		        }
                $GLOBALS['tabs']['Profile'] = TabUtils::createTab($title);
                $GLOBALS['tabs']['Manager'] = TabUtils::createTab("Manager");
                
	            Hooks::run('TopLevelTabs', array(&$GLOBALS['tabs']));
	            Hooks::run('SubLevelTabs', array(&$GLOBALS['tabs']));
	            
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
        </div-->
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
        $title = "People";
	if($wgLang->getCode() == "fr"){
	    $title = "Gens";
	} 
        $GLOBALS['toolbox']['People'] = TabUtils::createToolboxHeader($title);
        $GLOBALS['toolbox']['Products'] = TabUtils::createToolboxHeader(Inflect::pluralize($config->getValue('productsTerm')));
        $title = "For All Members";
        if($wgLang->getCode() == "fr"){
            $title = "Pour tous les membres";
        } 
        $GLOBALS['toolbox']['Other'] = TabUtils::createToolboxHeader($title);
        
        $title = "For Health Care Professionals";
        if($wgLang->getCode() == "fr"){
            $title = "Pour les professionnels de la santé";
        }
        $GLOBALS['toolbox']['Other2'] = TabUtils::createToolboxHeader($title);
        $message = "";
        $emailPassword = "";
		if($wgUser->isLoggedIn()){
		    echo "
			<ul class='pBodyLogin'>";
		    
		    if(isset($_GET['returnto'])){
		        redirect("$wgServer$wgScriptPath/index.php/{$_GET['returnto']}");
		    }
		    $me = Person::newFromWgUser();
		    Hooks::run('ToolboxHeaders', array(&$GLOBALS['toolbox']));
	        Hooks::run('ToolboxLinks', array(&$GLOBALS['toolbox']));
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
            $title = "Frequently Asked Questions";
            if($wgLang->getCode() == "fr"){
                $title = "Questions Fréquemment Posées";
            }
            $GLOBALS['toolbox']['Other']['links'][9998] = TabUtils::createToolboxLink($title, "$wgServer$wgScriptPath/index.php/FAQ");
            if($me->isRoleAtLeast(MANAGER)){
                $GLOBALS['toolbox']['People']['links'][9999] = TabUtils::createToolboxLink("Reset Password", "$wgServer$wgScriptPath/index.php/Special:PasswordReset");
            }
		    $person = Person::newFromId($wgUser->getId());
		    /*if($wgUser->isLoggedIn() && $person->isRoleAtLeast(MANAGER)){
                $title = "Other Tools";
                if($wgLang->getCode() == "fr"){
                    $title = "Autres Outils";
                }
                $GLOBALS['toolbox']['Other']['links'][9999] = TabUtils::createToolboxLink($title, "$wgServer$wgScriptPath/index.php/Special:SpecialPages");
            }*/
		    global $toolbox;
	            $i = 0;
            $title = "Take a Poll";
            if($wgLang->getCode() == "fr"){
                $title = "Prendre un Sondage";
            }

            $poll_tab = array(TabUtils::createToolboxLink($title, "$wgServer$wgScriptPath/index.php?action=viewPoll&id=random"));
            $title = "Helpful Resources";
            if($wgLang->getCode() == "fr"){
                $title = "Ressources utiles";
            }

            $resources_tab = array(TabUtils::createToolboxLink($title, "$wgServer$wgScriptPath/index.php/Special:HelpfulResources"));
            
            $title = "What's happening in your province?";
            if($wgLang->getCode() == "fr"){
                $title = "Qui fournit/paie Mifegymiso?";
            }

            $whosupplies_tab = array(TabUtils::createToolboxLink($title, "$wgServer$wgScriptPath/index.php/Special:WhoSupplies"));
            
            $title = "Latest News";
            if($wgLang->getCode() == "fr"){
                $title = "Dernières Nouvelles";
            }

            $latestnews_tab = array(TabUtils::createToolboxLink($title, "$wgServer$wgScriptPath/index.php/Special:LatestNews"));

            $title = "Academic Resources";
            if($wgLang->getCode() == "fr"){
                $title = "Ressources académiques";
            }

            $academic_resources = array(TabUtils::createToolboxLink($title, "$wgServer$wgScriptPath/index.php/Special:AcademicResources"));

            array_splice($GLOBALS['toolbox']['Other']['links'],2,0,$latestnews_tab);
            array_splice($GLOBALS['toolbox']['Other']['links'],2,0,$whosupplies_tab);
            array_splice($GLOBALS['toolbox']['Other']['links'],0,0,$poll_tab);
            array_splice($GLOBALS['toolbox']['Other']['links'],0,0,$resources_tab);
            if($me->isRoleAtLeast(MANAGER) || $me->isSubRole('Academic Faculty')){
                array_splice($GLOBALS['toolbox']['Other2']['links'],0,0,$academic_resources);
            }
           
	        foreach($toolbox as $key => $header){
	            if(count($header['links']) > 0){
	                $hr = ($i > 0) ? "" : "";
	                $i = min(1, $i);
	                echo "<span class='pBodyTitle$i'>{$hr}{$header['text']}</span><ul class='pBody$i'>";
	                ksort($header['links']);
	                foreach($header['links'] as $lKey => $link){
	                    echo "<li><a class='highlights-background-hover' href='{$link['href']}'>{$link['text']}</a></li>";
	                }
	                echo "</ul>";
	                $i++;
	            }
	        }
	        
	        if($me->isCandidate()){
	            echo <<< EOF
        <a class='underlined highlights-text' style='display:inline;padding:0;' href='$wgServer$wgScriptPath/index.php/Special:CAPSCompleteRegister'><span class='pBodyTitle1 en'>Become a Full Member</span></a>
        <div class='pBody1 en' style='padding: 10px;margin-bottom:4px;'>Complete your <a class='underlined highlights-text' style='display:inline;padding:0;' href='$wgServer$wgScriptPath/index.php/Special:CAPSCompleteRegister'>registration</a> to become a full member.</div>
        <a class='underlined highlights-text' style='display:inline;padding:0;' href='$wgServer$wgScriptPath/index.php/Special:CAPSCompleteRegister'><span class='pBodyTitle1 fr'>Devenir membre à part entière</span></a>
        <div class='pBody1 fr' style='padding: 10px;margin-bottom:4px;'>Complétez votre <a class='underlined highlights-text' style='display:inline;padding:0;' href='$wgServer$wgScriptPath/index.php/Special:CAPSCompleteRegister'>inscription</a> pour devenir membre à part entière.</div>
EOF;
            }
		}
		else {
		    global $wgSiteName, $wgOut, $wgPasswordAttemptThrottle;
		    setcookie('sideToggled', 'out', time()-3600);
		    $userLogin = new SpecialSideUserLogin();
		    $userLogin->render();
            echo <<< EOF
        <br />
        <a class='underlined highlights-text' style='display:inline;padding:0;' href='$wgServer$wgScriptPath/index.php/Special:CAPSRegister'><span class='pBodyTitle0 en'>Member Registration</span></a>
        <div class='pBody0 en' style='padding: 10px;margin-bottom: 4px;'>If you would like to apply to become a member in CAPS then please fill out the <a class='underlined highlights-text' style='display:inline;padding:0;' href='$wgServer$wgScriptPath/index.php/Special:CAPSRegister'>registration form</a>.</div>
        <a class='underlined highlights-text' style='display:inline;padding:0;' href='$wgServer$wgScriptPath/index.php/Special:CAPSRegister'><span class='pBodyTitle0 fr'>Inscription Membre</span></a>
        <div class='pBody0 fr' style='padding: 10px;margin-bottom: 4px;'>Si vous souhaitez postuler pour devenir membre en CPCA alors s'il vous plaît remplir le <a class='underlined highlights-text' style='display:inline;padding:0;' href='$wgServer$wgScriptPath/index.php/Special:CAPSRegister'>formulaire d'inscription</a>.</div>
        
EOF;
        }
        echo "<a class='underlined highlights-text' style='display:inline;padding:0;' href='$wgServer$wgScriptPath/index.php/About'><span class='pBodyTitle1 en'>About</span></a>
        <div class='pBody1 en' style='padding: 10px;margin-bottom: 4px;margin-left:4px;margin-right:4px;'>Learn more about the website <a class='underlined highlights-text' style='display:inline;padding:0;' href='$wgServer$wgScriptPath/index.php/About'>here</a>.</div>
        <a class='underlined highlights-text' style='display:inline;padding:0;' href='$wgServer$wgScriptPath/index.php/About'><span class='pBodyTitle1 fr'>Propos</span></a>
        <div class='pBody1 fr' style='padding: 10px;margin-bottom: 4px;margin-left:4px;margin-right:4px;'>En savoir plus sur le site <a class='underlined highlights-text' style='display:inline;padding:0;' href='$wgServer$wgScriptPath/index.php/About'>ici</a>.</div>";
        
		Hooks::run( 'MonoBookTemplateToolboxEnd', array( &$this ) );
		Hooks::run( 'SkinTemplateToolboxEnd', array( &$this ) );
?>
	</li>
<?php
	}

} // end of class
