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
		global $wgRequest, $wgServer, $wgScriptPath, $wgOut, $wgLogo, $wgTitle, $wgUser, $wgMessage, $wgImpersonating, $wgDelegating, $wgTitle, $config, $wgLang;
		$this->skin = $skin = $this->data['skin'];
		$action = $wgRequest->getText( 'action' );

		// Suppress warnings to prevent notices about missing indexes in $this->data
		wfSuppressWarnings();
		
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php $this->text('lang') ?>" lang="<?php $this->text('lang') ?>" dir="<?php $this->text('dir') ?>">
	<head>
		<meta http-equiv="Content-Type" content="<?php $this->text('mimetype') ?>; charset=<?php $this->text('charset') ?>" />
        <?php if(!TESTING && $wgScriptPath != "" && !DEMO){ ?>
            <meta name="robots" content="noindex, nofollow" />
            <meta name="googlebot" content="noindex, nofollow" />
        <?php } ?>
		<title><?php $this->text('pagetitle') ?></title>
		<link type="image/x-icon" href="<?php echo $wgServer.$wgScriptPath.'/favicon.png'; ?>" rel="shortcut icon" />
		<link rel='stylesheet' id='roboto-css'  href='//fonts.googleapis.com/css?family=Roboto%3A400%2C400i%2C500%2C500i%2C700%2C700i&#038;ver=4.9.13' type='text/css' media='all' />
		<link type="text/css" href="<?php $this->text('stylepath') ?>/smoothness/jquery-ui-1.8.21.custom.css" rel="Stylesheet" />

		<link rel="stylesheet" href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/autocomplete.css" type="text/css" />
		<link rel="stylesheet" href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/rte-content.css" type="text/css" />

		<!-- Multiple Select-->
		<link rel="stylesheet" href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/multiple-select.css" type="text/css" />
		
		<link type="text/css" href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/jquery.qtip.min.css" rel="Stylesheet" />
		<link type="text/css" href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/chosen/chosen.css.php" rel="Stylesheet" />
		<?php $this->html('csslinks') ?>

		<link rel="stylesheet" href="<?php $this->text('stylepath') ?>/common/shared.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="<?php $this->text('stylepath') ?>/common/commonPrint.css" type="text/css" media="print" />
		<link rel="stylesheet" href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/print.css" type="text/css" media="print" />
		<link type="text/css" href="<?php $this->text('stylepath') ?>/common/carousel.css" rel="Stylesheet" />
		
		<link type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/DataTables/css/cavendish/jquery.dataTables.css" rel="Stylesheet" />
		<link type="text/css" rel="stylesheet" href="<?php echo "$wgServer$wgScriptPath"; ?>/skins/simplePagination/simplePagination.css" />
		
		<link type="text/css" rel="stylesheet" href="<?php echo "$wgServer$wgScriptPath"; ?>/skins/<?php $this->text('stylename') ?>/content.css?<?php echo filemtime('skins/cavendish/content.css'); ?>" />
		<link type="text/css" rel="stylesheet" href="<?php echo "$wgServer$wgScriptPath"; ?>/skins/<?php $this->text('stylename') ?>/template.css?<?php echo filemtime('skins/cavendish/template.css'); ?>" />
		<link type="text/css" rel="stylesheet" href="<?php echo "$wgServer$wgScriptPath"; ?>/skins/<?php $this->text('stylename') ?>/basetemplate.css?<?php echo filemtime('skins/cavendish/basetemplate.css'); ?>" />
		<link type="text/css" rel="stylesheet" href="<?php echo "$wgServer$wgScriptPath"; ?>/skins/<?php $this->text('stylename') ?>/cavendish.css?<?php echo filemtime('skins/cavendish/cavendish.css'); ?>" />
		<link type="text/css" rel="stylesheet" href="<?php echo "$wgServer$wgScriptPath"; ?>/skins/<?php $this->text('stylename') ?>/main.css?<?php echo filemtime('skins/cavendish/main.css'); ?>" />
		<style type="text/css" media="screen,projection">/*<![CDATA[*/ @import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/extensions.css"; /*]]>*/</style>
		<style <?php if(empty($this->data['printable']) ) { ?>media="print"<?php } ?> type="text/css">/*<![CDATA[*/ @import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/print.css"; /*]]>*/</style>
		
		<link rel="stylesheet" type="text/css" media="print" href="<?php $this->text('stylepath') ?>/common/commonPrint.css" />
		<link type="text/css" href="<?php $this->text('stylepath') ?>/switcheroo/switcheroo.css" rel="Stylesheet" />
		<link type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/tagIt/css/jquery.tagit.css" rel="Stylesheet" />
		<link type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/tagIt/css/tagit.ui-zendesk.css" rel="Stylesheet" />
		<link type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jqueryDropdown/cavendish/jquery.dropdown.css" rel="Stylesheet" />
		<link rel="stylesheet" type="text/css" href="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/lightbox/css/lightbox.min.css" />
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
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.dataTables.min.css" />
        <link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/3.3.2/css/fixedColumns.dataTables.min.css" />
        <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.1.7/css/fixedHeader.dataTables.min.css" />
		
		<script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/date.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/inflection.js?version=2021.02.09"></script>
		<script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/to-title-case.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/countries.en.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/detectIE.js"></script>

        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.min.js?version=3.4.1"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.backwards.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery-ui.min.js?version=1.8.24"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.browser.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.cookie.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.resizeY.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.limit-1.2.source.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.multiLimit.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.combobox.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.chosen.js?version=1.8.3"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.caret.1.02.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery-ui.triggeredAutocomplete.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.filterByText.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.scrollTo-min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.md5.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jqueryDropdown/jquery.dropdown.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.reallyvisible.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.jsPlumb-min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/lightbox/js/lightbox.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/DataTables/js/jquery.dataTables.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/DataTables/plugins/natural.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/DataTables/plugins/rowsGroup.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.flash.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.colVis.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/3.3.2/js/dataTables.fixedColumns.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/fixedheader/3.1.7/js/dataTables.fixedHeader.min.js"></script>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.qtip.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.forceNumeric.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.form.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/tabs.js"></script>
        
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/multiple-select/js/multiple-select.js"></script>

        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/tinymce/tinymce.min.js?version=4.6.7"></script>
        <!--script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/tinymce/combobox.js"></script-->
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/tinymce/jquery.tinymce.min.js?version=4.6.7"></script>
        
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/tagIt/js/tag-it.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/sortable.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/switcheroo.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/raphael.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/spinner.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/filter.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/extensions/Messages/messages.js?<?php echo filemtime('extensions/Messages/messages.js'); ?>"></script>
        
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/d3.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/d3plus.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/html2canvas.min.js"></script>
    
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/underscore-min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/backbone-min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/backbone-subviews.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/backbone-trackit.js"></script>
        <!--script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/backbone-relational-min.js"></script>-->
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.simplePagination.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/carousel.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/markitup/jquery.markitup.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/markitup/sets/wiki/set.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/autosave.js?<?php echo filemtime(dirname(__FILE__)."/../../scripts/autosave.js"); ?>"></script>

        <script type='text/javascript'>
        
            $.ajaxSetup({ cache: false, 
                          data: {embed: <?php if(isset($_GET['embed']) && $_GET['embed'] != "false"){ echo "true"; } else { echo "false"; } ?>},
                          headers : { "cache-control": "no-cache" } 
                        });
        
            $(document).on('click', function(e){
                Backbone.trigger('document-click-event', e);
            });
        
            Backbone.emulateHTTP = true;
            Backbone.emulateJSON = true;
            
            Backbone.View.prototype.beforeRender = function(){};
            Backbone.View.prototype.afterRender = function(){
                $.each(this.$el.find('input[type=datepicker]'), function(index, val){
                    $(val).datepicker({
                        'dateFormat': $(val).attr('format'),
                        'defaultDate': $(val).attr('value').substr(0, 10),
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
		</script>
		<?php createModels(); ?>
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

		<script type='text/javascript'>
		
		    // Configs
		    allowedRoles = <?php $me = Person::newFromWGUser(); echo json_encode($me->getAllowedRoles()); ?>;
		    allowedProjects = <?php $me = Person::newFromWGUser(); echo json_encode($me->getAllowedProjects()); ?>;
		    allowedThemes = <?php echo json_encode(Theme::getAllowedThemes()); ?>;
		    allowedVisibility = <?php if($me->isRoleAtLeast(STAFF)){ 
		                                  echo json_encode(array('Public','Forum','Manager')); 
		                              } 
		                              else { 
		                                  echo json_encode(array('Public','Forum')); 
		                              } ?>;
		    isAllowedToCreateNewsPostings = <?php echo json_encode(NewsPosting::isAllowedToCreate()); ?>;
		    isAllowedToCreateEventPostings = <?php echo json_encode(EventPosting::isAllowedToCreate()); ?>;
		    isAllowedToCreateBSIPostings = <?php echo json_encode(BSIPosting::isAllowedToCreate()); ?>;
		    isAllowedToCreateCRMContacts = <?php echo json_encode(CRMContact::isAllowedToCreate()); ?>;
		    isAllowedToCreateLIMSContacts = <?php echo json_encode(LIMSContact::isAllowedToCreate()); ?>;
		    wgRoles = <?php global $wgAllRoles; echo json_encode($wgAllRoles); ?>;
		    roleDefs = <?php echo json_encode($config->getValue('roleDefs')); ?>;
		    subRoles = <?php $subRoles = $config->getValue('subRoles'); asort($subRoles); echo json_encode($subRoles); ?>;
		    
		    <?php
		        foreach($config->constants as $key => $value){
		            echo "{$key} = '{$value}';\n";
		        }
		    ?>
		    
		    skin = "<?php echo $config->getValue('skin'); ?>";
		    orcidId = "<?php echo $config->getValue('orcidId'); ?>";
		    currentTimestamp = "<?php echo currentTimestamp(); ?>";
		    projectPhase = <?php echo PROJECT_PHASE; ?>;
		    projectsEnabled = <?php var_export($config->getValue('projectsEnabled')); ?>;
		    showNonNetwork = <?php var_export($config->getValue("showNonNetwork")) ?>;
		    alumniEnabled = <?php var_export($config->getValue('alumniEnabled')); ?>;
		    networkName = "<?php echo $config->getValue('networkName'); ?>";
		    extensions = <?php echo json_encode($config->getValue('extensions')); ?>;
		    iconPath = "<?php echo $config->getValue('iconPath'); ?>";
		    iconPathHighlighted = "<?php echo $config->getValue('iconPathHighlighted'); ?>";
		    highlightColor = "<?php echo $config->getValue('highlightColor'); ?>";
		    highlightFontColor = "<?php echo $config->getValue('highlightFontColor'); ?>";
		    headerColor = "<?php echo $config->getValue('headerColor'); ?>";
		    topHeaderColor = "<?php echo $config->getValue('topHeaderColor'); ?>";
		    sideColor = "<?php echo $config->getValue('sideColor'); ?>";
		    hyperlinkColor = "<?php echo $config->getValue('hyperlinkColor'); ?>";
		    mainBorderColor = "<?php echo $config->getValue('mainBorderColor'); ?>";
		    productsTerm = "<?php echo $config->getValue('productsTerm'); ?>";
		    productVisibility = "<?php echo $config->getValue('productVisibility'); ?>";
		    subRolesTerm = "<?php echo $config->getValue('subRoleTerm'); ?>";
		    deptsTerm = "<?php echo $config->getValue('deptsTerm'); ?>";
		    splitDept = "<?php echo $config->getValue('splitDept'); ?>";
		    relationTypes = <?php echo json_encode($config->getValue('relationTypes')); ?>;
		    boardMods = <?php echo json_encode($config->getValue('boardMods')); ?>;
		    
		    var today = new Date().toISOString().split('T')[0];
		
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
		    
		    function base64Conversion(s){
		        return window.btoa(unescape(encodeURIComponent(s)));
		    }
		    
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
		    
		    function setNavHeight(){
		        var negHeight = 46;
		        $("#side > div").each(function(i, el){
                    negHeight += $(el).outerHeight(true);
                })
		        $("#nav").css("max-height", "calc(100% - " + negHeight + "px)");
		    }
		    
		    function setBodyContentTop(){
		        if(!$("#header").is(":visible")){
		            $("#submenu").css("margin-top", "-45px");
		            $("#sideToggle").css("line-height", ($("#submenu").height() - 6) + "px");
		        }
		        $("#bodyContent").css('top', $("#submenu").offset().top + $("#submenu").height());
		        $("#sideToggle").height($("ul.top-nav").innerHeight() + $("div#submenu").height() - 3);
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
            
            function renderProductLinks(container){
                if(!(container instanceof jQuery)){
                    container = $("body");
                }
                $('.productTitle', $(container)).each(function(i, el){
                    if(_.isEmpty($(el).attr('data-title'))){
                        $(el).attr('data-title', $(el).html());
                    }
                    var id = $(el).attr('data-id');
                    var url = $(el).attr('data-href');
                    var title = $(el).attr('data-title');
                    var model = new Product({id: id, url: url, title: title});
                    pLinkView = new ProductLinkView({model: model.getLink(), el: el});
                    pLinkView.render();
                });
            }
	        
	        var sideToggled = $.cookie('sideToggled');
	        if(sideToggled == undefined){
	            sideToggled = 'out';
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
                });
            }
	        
		    $(document).ready(function(){
		        renderProductLinks();
		        setBodyContentTop();
		        setNavHeight();
                $(window).resize(setBodyContentTop);
                $(window).resize(setNavHeight);
		        setMinWidth();
		        
		        $('a.disabledButton').click(function(e){
                    e.preventDefault();
                });
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
		        $('.clicktooltip').qtip({
		            position: {
		                adjust: {
			                x: -($('.clicktooltip').width()/25),
			                y: -($('.clicktooltip').height()/2)
		                }
		            },
                    show: 'click',
                    hide: 'click unfocus'
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
		        
		        $("#sideToggle").click(function(e, force){
		            $("#sideToggle").stop();
		            if((sideToggled == 'out' && force == null) || force == 'in'){
		                $("#sideToggle").html("&gt;");
		                $("#side").animate({
		                    'left': '-200px'
		                }, 200, 'swing');
		                $("#outerHeader").animate({
		                    'left': '-3px'
		                }, 200, 'swing');
		                $("#bodyContent").animate({
		                    'left': '-3px'
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
		                    'left': '200px'
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
	        <iframe id="logoutFrame" style="display:none;" src=""></iframe>
		<?php } ?>
		<?php if(!(!TESTING && $wgScriptPath != "" && !DEMO)){ ?>
		    <style>
		        .mce-path-item {
		            opacity: 0;
		        }
		    </style>
		<?php } ?>
		<?php if(isset($_GET['embed'])){ ?>
		    <style>
		        <?php if(isset($_GET['scroll'])){ ?>
		            html {
                        overflow: visible !important;
                    }
		        <?php } ?>
		        <?php if(isset($_GET['noTitle'])){ ?>
		            h1 {
                        display: none !important;
                    }
		        <?php } ?>
		    
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
		                parent.postMessage(height+10, "*");
		            }, 100);
		            
		            <?php if(isset($_GET['newTab'])){ ?>
		                $("a").attr("target", "_blank");
		                $("a").each(function(i, a){
		                    if($(a).attr("href") != undefined){
		                        $(a).attr("href", $(a).attr("href").replace("embed", ""));
		                    }
		                });
		                setInterval(function(){
		                    $("a").attr("target", "_blank");
		                    $("a").each(function(i, a){
		                        if($(a).attr("href") != undefined){
		                            $(a).attr("href", $(a).attr("href").replace("embed", ""));
		                        }
		                    });
		                }, 100);
		            <?php } ?>
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
            global $wgSitename, $notifications, $notificationFunctions, $config, $wgLang;
            if(count($notifications) == 0){
                foreach($notificationFunctions as $function){
                    call_user_func($function);
                }
            }
            echo "<div class='smallLogo'><a href='{$this->data['nav_urls']['mainpage']['href']}' title='$wgSitename'><img src='$wgServer$wgScriptPath/{$config->getValue('logo')}' /></a></div>";
            echo "<div class='search'><div id='globalSearch'></div></div>";
            echo "<div class='login'>";
            if($config->getValue('bilingual')){
                echo "<select name='lang' style='vertical-align:middle;'>";
                echo ($wgLang->getCode() == "en") ? "<option value='en' selected>English</option>" : "<option value='en'>English</option>";
                echo ($wgLang->getCode() == "fr") ? "<option value='fr' selected>Français</option>" : "<option value='fr'>Français</option>";
                echo "</select>
                <script type='text/javascript'>
                    $('select[name=lang]').change(function(){
                        var search = (document.location.search != '') ? 
                            '?lang=' + $('select[name=lang]').val() + document.location.search.replace('?', '&').replace(/&lang=(en|fr)/, ''): 
                            '?lang=' + $('select[name=lang]').val();
                        document.location = search + document.location.hash;
                    });
                </script>";
            }
            echo "<div style='display:none;' id='share_template'>";
            foreach($config->getValue("socialLinks") as $social => $link){
                $img = "";
                $text = "";
                if(is_array($link)){
                    $social = $link['social'];
                    $text = $link['text'];
                    $link = $link['url'];
                }
                switch($social){
                    case 'flickr':
                        $img = "glyphicons_social_35_flickr";
                        $text = ($text == "") ? "Flickr" : $text;
                        break;
                    case 'twitter':
                        $img = "glyphicons_social_31_twitter";
                        $text = ($text == "") ? "Twitter" : $text;
                        break;
                    case 'facebook':
                        $img = "glyphicons_social_30_facebook";
                        $text = ($text == "") ? "Facebook" : $text;
                        break;
                    case 'vimeo':
                        $img = "glyphicons_social_34_vimeo";
                        $text = ($text == "") ? "Vimeo" : $text;
                        break;
                    case 'linkedin':
                        $img = "glyphicons_social_17_linked_in";
                        $text = ($text == "") ? "LinkedIn" : $text;
                        break;
                    case 'youtube':
                        $img = "glyphicons_social_22_youtube";
                        $text = ($text == "") ? "YouTube" : $text;
                        break;
                    case 'newsletter':
                        $img = "glyphicons_social_39_e-mail";
                        $text = ($text == "") ? "Newsletter" : $text;
                        break;
                    case 'intranet':
                        $img = "share_24x24";
                        $text = ($text == "") ? "Intranet" : $text;
                        break;
                }
                echo "<a class='changeImg' style='white-space:nowrap;' name='$img' href='$link' target='_blank'>
	                        <img src='$wgServer$wgScriptPath/{$config->getValue('iconPath')}$img.png' />&nbsp;$text
	                  </a>";
	        }
	        echo "</div>";
            echo "<a id='status_help_faq' name='question_mark_8x16' class='menuTooltip' title='Help/FAQ' href='$wgServer$wgScriptPath/index.php/Help:Contents'><img src='$wgServer$wgScriptPath/skins/icons/white/question_mark_8x16.png' />&nbsp;&nbsp;<span class='en'>Help/FAQ</span><span class='fr'>Aide/FAQ</span></a>";
            if(count($config->getValue("socialLinks")) > 0){
	            echo "<a id='share' style='cursor:pointer;' name='share_16x16' class='menuTooltipHTML'><img src='$wgServer$wgScriptPath/skins/icons/white/share_16x16.png' />&nbsp;▼</a>";
	        }
	        if($wgUser->isLoggedIn()){
		        $p = Person::newFromId($wgUser->getId());
		        
		        $notificationAnimation = "";
		        if(count($notifications) > 0){
		            $notificationAnimation = "animation: shake 2s; animation-iteration-count: infinite;";
		            $notificationText = " (".count($notifications).")";
		        }
		        echo "<a id='status_notifications' name='mail_16x12' class='menuTooltip' title='Notifications$notificationText' href='$wgServer$wgScriptPath/index.php?action=viewNotifications'><img src='$wgServer$wgScriptPath/skins/icons/white/mail_16x12.png' style='$notificationAnimation' /></a>";
		        echo "<a id='status_profile' class='menuTooltip' title='Profile' href='{$p->getUrl()}'>{$p->getNameForForms()}</a>";
		        echo "<a id='status_profile_photo' class='menuTooltip' title='Profile' href='{$p->getUrl()}'><img class='photo' src='{$p->getPhoto()}' /></a>";
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
	                echo "<a id='status_logout' name='arrow_right_16x16' class='menuTooltip' style='cursor: pointer;' title='Logout' href='{$logout['href']}'><img src='$wgServer$wgScriptPath/skins/icons/white/arrow_right_16x16.png' /></a>";
	            }
	        }
	        echo "</div>";
            if(!TESTING && $wgScriptPath != "" && !DEMO){
                exec("git rev-parse HEAD", $output);
                $revId = @substr($output[0], 0, 10);
                exec("git rev-parse --abbrev-ref HEAD", $output);
                $branch = @$output[1];
                $revIdFull = "<a title='{$output[0]}' target='_blank' href='https://github.com/UniversityOfAlberta/GrandForum/commit/{$output[0]}'>$revId</a>";
                $branchFull = "<a title='$branch' target='_blank' href='https://github.com/UniversityOfAlberta/GrandForum/tree/$branch'>$branch</a>";
                $docs = "<a title='docs' target='_blank' href='https://grand.cs.ualberta.ca/~dwt/glyconet_test/docs/_build/html/'>Docs</a>";
                
                if(strstr($wgScriptPath, "staging") !== false){
                    echo "<div style='position:absolute;top:15px;left:525px;'>
                            STAGING ($branchFull, $revIdFull), $docs&nbsp;&nbsp;<a target='_blank' href='https://grand.cs.ualberta.ca/~dwt/behat_test/symfony/output/output.html'><img src='https://grand.cs.ualberta.ca/~dwt/behat_test/testSuiteStatus.php' /></a></div>";
                }
                else{
                    echo "<div style='position:absolute;top:15px;left:525px;'>
                            DEVELOPMENT ($branchFull, $revIdFull), $docs&nbsp;&nbsp;<a target='_blank' href='https://grand.cs.ualberta.ca/~dwt/behat_test/symfony/output/output.html'><img src='https://grand.cs.ualberta.ca/~dwt/behat_test/testSuiteStatus.php' /></a></div>";
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
           	
           	$headerTabs = array();
           	foreach($tabs as $key => $tab){
		        if($key == "Main"){
		            continue;
		        }
		        if($tab['href'] != "" && $tab['text'] != ""){
		            $headerTabs[] = "<li class='top-nav-element {$tab['selected']}'>
                                        <a id='{$tab['id']}' class='top-nav-mid highlights-tab' href='{$tab['href']}'>{$tab['text']}</a>
                                     </li>";
                }
		    }
        ?>
	    <div id="header" style="<?php if(count($headerTabs) == 0){ echo 'display:none;'; } ?>">
	        <a id="allTabs"><img src="<?php echo $wgServer.$wgScriptPath; ?>/skins/hamburger<?php if($config->getValue("sideInverted")){ echo "_inverted"; } ?>.png" /></a>
		    <a name="top" id="contentTop"></a>
	        <ul class="top-nav">
	            <?php echo implode("\n", $headerTabs); ?>
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
		           	                if(count($dropdown['dropdown']) > 0){
		           	                    echo "<li class='$class hidden {$dropdown['selected']}'><a style='cursor: default; background: white; color: {$config->getValue('highlightColor')} !important;'>".htmlspecialchars($dropdown['text'])."</a></li>";
		           	                    foreach($dropdown['dropdown'] as $subdropdown){
		           	                        echo "<li class='$class hidden {$subdropdown['selected']}'><a class='highlights-tab' href='".htmlspecialchars($subdropdown['href'])."' style='line-height: 23px !important; height: 23px !important;'>&nbsp;&nbsp;&nbsp;".htmlspecialchars($subdropdown['text'])."</a></li>";
		           	                    }
		           	                }
		           	                else{
		           	                    echo "<li class='$class hidden {$dropdown['selected']}'><a class='highlights-tab' href='".htmlspecialchars($dropdown['href'])."'>".htmlspecialchars($dropdown['text'])."</a></li>";
		           	                }
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
		       	 if($config->getValue('wikiEnabled')){
		           	 foreach($this->data['content_actions'] as $key => $action) {
		           	    if($key == "nstab-special" || 
		           	       $key == "varlang-watch"){
		           	        continue;
		           	    }
		               ?><li
		               <?php if($action['class']) { ?>class="<?php echo htmlspecialchars($action['class']) ?>"<?php } ?>
		               ><a class='highlights-tab' href="<?php echo htmlspecialchars($action['href']) ?>"><?php
		               echo htmlspecialchars($action['text']) ?></a></li><?php
		             }
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
		    <?php
		        echo "<div id='sideFooter' style='font-size: 0.80em; text-align:center; padding:5px; position:absolute; bottom:0;'>";
		        if($config->getValue('networkSite') != ""){
                    echo "&nbsp;&nbsp;<a target='_blank' href='{$config->getValue('networkSite')}'>{$config->getValue('networkName')} Website</a>&nbsp;&nbsp;";
                }
                if(!isExtensionEnabled("ContactUs")){
                    echo "&nbsp;&nbsp;<a href='mailto:{$config->getValue('supportEmail')}'><span class='en'>Support</span><span class='fr'>Soutien</span></a>&nbsp;&nbsp;";
                }
                echo "    <p style='text-align:left;'><span class='en'>The following NCEs have contributed to the development of the Forum: <br />GRAND, AGE-WELL, GlycoNet, CFN</span><span class='fr'>Les RCE suivants ont contribué au développement du Forum : GRAND, AGE-WELL, GlycoNet, CFN</span></p>
                      </div>";
            ?>
		</div><!-- end of SIDE div -->
		<div id="allTabsDropdown" style="display:none;"></div>
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
			<div id="footer"><table style="width:100%"><tr><td align="left" width="1%" nowrap="nowrap">
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
    ?>
    </ul>
    </td></tr></table><img style='display:none;' src='<?php echo "$wgServer$wgScriptPath"; ?>/skins/Throbber.gif' alt='Throbber' />
	    </div><!-- end of the FOOTER div -->
		</div><!-- end of MAINCONTENT div -->	
	</div><!-- end of MBODY div -->
</div><!-- end of the CONTAINER div -->
<?php echo wfReportTimeOld(); ?>

</body>
</html>

<?php
	}
	function toolbox() {
?>
	<li class="portlet" id="p-tb">
<?php global $config, $wgServer, $wgScriptPath;
    if($config->getValue('networkName') == "FES"){ ?>
        <style>
            a.administration {
                padding-bottom:6px;
            }
            
            a.administration:hover {
                box-shadow: 0 22px 22px -22px rgba(0,0,0,0.3) inset;
            }
        </style>
        <a class="administration highlights-background-hover" href="<?php echo "$wgServer$wgScriptPath/index.php/"; ?>Administration">Administration</a>
        <a class="administration highlights-background-hover" href="<?php echo "$wgServer$wgScriptPath/index.php/"; ?>EDI">Equity, Diversity & Inclusion</a>
<?php } ?>
<?php
	global $wgServer, $wgScriptPath, $wgUser, $wgRequest, $wgAuth, $wgTitle, $config;
	    $GLOBALS['toolbox'] = array();
        
        $GLOBALS['toolbox']['People'] = TabUtils::createToolboxHeader("People");
        $GLOBALS['toolbox']['Products'] = TabUtils::createToolboxHeader("Outputs");
        $GLOBALS['toolbox']['Postings'] = TabUtils::createToolboxHeader("Postings");
        $GLOBALS['toolbox']['Other'] = TabUtils::createToolboxHeader("Other");
        
		if($wgUser->isLoggedIn()){
		    if(isset($_GET['returnto'])){
		        redirect("$wgServer$wgScriptPath/index.php/{$_GET['returnto']}");
		    }
		    $me = Person::newFromWgUser();
		    wfRunHooks('ToolboxHeaders', array(&$GLOBALS['toolbox']));
	        wfRunHooks('ToolboxLinks', array(&$GLOBALS['toolbox']));
	        if($config->getValue("showUploadFile") && !$me->isCandidate()){
	            $GLOBALS['toolbox']['Other']['links'][1000] = TabUtils::createToolboxLink("Upload File", "$wgServer$wgScriptPath/index.php/Special:Upload");
	        }
	        if($wgUser->isLoggedIn() && $config->getValue('networkName') == "AGE-WELL"){ 
	            $GLOBALS['toolbox']['Other']['links'][] = TabUtils::createToolboxLink("Members' Intranet", "$wgServer$wgScriptPath/index.php/Resources");
	        }
	        if($wgUser->isLoggedIn() && $config->getValue('networkName') == "GlycoNet"){
	            $GLOBALS['toolbox']['Other']['links'][] = TabUtils::createToolboxLink("Logos/Templates", "$wgServer$wgScriptPath/index.php/Logos_Templates");
	            $GLOBALS['toolbox']['Other']['links'][] = TabUtils::createToolboxLink("E-Resource Library", "$wgServer$wgScriptPath/index.php/E-Resource_Library");
	            $GLOBALS['toolbox']['Other']['links'][] = TabUtils::createToolboxLink("Ecosystem Map", "$wgServer$wgScriptPath/index.php/Ecosystem_Map");
	            $GLOBALS['toolbox']['Other']['links'][] = TabUtils::createToolboxLink("Forum Help and FAQs", "$wgServer$wgScriptPath/index.php/FAQ");
	        }
	        if($config->getValue("showOtherTools") && !$me->isCandidate()){
	            $GLOBALS['toolbox']['Other']['links'][9999] = TabUtils::createToolboxLink("Other Tools", "$wgServer$wgScriptPath/index.php/Special:SpecialPages");
	        }
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
	            if($key == "People" && $config->getValue('networkName') == "AI4Society"){
	                echo "<hr />";
	                echo "<a class='administration highlights-background-hover' style='padding: 5px 8px 5px 10px;' href='$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:Projects'>Projects</a>";
	            }
	        }
		}
		else {
		    global $wgSiteName, $wgOut, $wgLang;
		    setcookie('sideToggled', 'out', time()-3600);
		    $loginFailed = (isset($_POST['wpLoginattempt']) || isset($_POST['wpMailmypassword']));
		    if($loginFailed){
		        if(isset($_POST['wpName'])){
		            $_POST['wpUsername'] = $_POST['wpName'];
		        }
		        else{
		            $_POST['wpName'] = $_POST['wpUsername'];
		        }
		        $_POST['wpName'] = sanitizeInput($_POST['wpName']);
		        $_POST['wpUsername'] = $_POST['wpName'];
		        $_POST['wpPassword'] = sanitizeInput($_POST['wpPassword']);
		        $person = Person::newFromName($_POST['wpName']);
		        $user = User::newFromName($_POST['wpName']);
		        if($user == null || $user->getId() == 0 || $user->getName() != $_POST['wpName']){
		            $failMessage = "<p class='inlineError'>
		                                <span class='en'>There is no user by the name of <b>{$_POST['wpName']}</b>.  If you are an HQP and do not have an account, please ask your supervisor to create one for you.</span>
		                                <span class='fr'>Il n’y a pas d’utilisateurs avec le nom <b>{$_POST['wpName']}</b>. Si vous êtes un-e PHQ et que vous n’avez pas de compte, veuillez demander à votre superviseur de vous en créer.</span><br />";
		            if(isset($_POST['wpMailmypassword'])){
		                $failMessage .= "<b>Password request failed</b>";
		            }
		            $failMessage .= "</p>";
		        }
		        else if(isset($_POST['wpMailmypassword'])){
		            $user = User::newFromName($_POST['wpUsername']);
		            $user->load();
		            $failMessage = "<p class='inlineSuccess'><span class='en'>A new password has been sent to the e-mail address registered for &quot;{$_POST['wpName']}&quot;.  Please wait a few minutes for the email to appear.  If you do not recieve an email, then contact <a class='highlights-text-hover' style='padding: 0;background:none;display:inline;border-width: 0;' href='mailto:{$config->getValue('supportEmail')}'>Forum Support</a>.<br /><br /><b>NOTE:</b> Only one password reset can be requested every 10 minutes.</span><span class='fr'>Un nouveau mot de passe a été envoyé au courriel enregistré sous « {$_POST['wpName']} ». Veuillez attendre quelques minutes pour que le courriel apparaisse. Si vous ne recevez pas de courriel, veuillez contacter le <a class='highlights-text-hover' style='padding: 0;background:none;display:inline;border-width: 0;' href='mailto:{$config->getValue('supportEmail')}'>Service d’assistance du forum</a>.<br /><br /><b>N.B. :</b> Une seule demande de réinitialisation du mot de passe peut être faite toutes les 10 minutes.</span></p>";
		        }
		        else{
		            $failMessage = "<p class='inlineError'>
		                                <span class='en'>Incorrect password entered. Please try again.</span>
		                                <span class='fr'>Vous avez mis le mauvais mot de passe.</span>
		                            </p>";
		        }
		        if($user != null && $user->checkTemporaryPassword($_POST['wpPassword'])){
		            LoginForm::clearLoginThrottle($_POST['wpName']);
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
		        if($wgLang->getCode() == "en"){
		            $wgOut->setPageTitle("Login");
		        }
		        else if($wgLang->getCode() == "fr"){
		            $wgOut->setPageTitle("Connexion");
		        }
		        $wgOut->addHTML("
		        <div class='en'>
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
                    </ol>
                </div>
                <div class='fr'>
                    <p>Problèmes typiques de connexion:</p>
                    <ol>
                        <li>Votre compte n’a pas encore été configuré
                            <ul>
                                <li>Vous avez un compte, mais vous ne vous souvenez pas de votre identifiant</li>
                            </ul>
                        </li>
                        <li>Vous avez un compte, mais vous ne vous souvenez pas de votre identifiant
                            <ul>
                                <li>Cherchez le nom dans la zone de texte « chercher » ci-dessus (notez que Candidat-e n'apparaîtra généralement pas dans la recherche)</li>
                                <li>Quand vous voyez votre nom dans la liste déroulante, cliquez dessus et allez à votre page de profil</li>
                                <li>L'URL indique l'identifiant de connexion (est sensible à la casse, il faut un point entre prénom et nom, les accents sont nécessaires)</li>
                            </ul>
                        </li>
                        <li>Vous connaissez votre identifiant, mais pas votre mot de passe
                            <ul>
                                <li>Cliquez sur le lien « Envoyer nouveau mot de passe » pour recevoir un mot de passe temporaire dans votre messagerie (regardez dans votre dossier spam ; si vous ne le recevez pas dans les 30 minutes qui suivent, contactez le coordinateur thématique pour vérifier si votre adresse courriel a été bien configuré)</li>
                            </ul>
                        </li>
                    </ol>
                </div>");
		        $message = "
<p>
<span class='en'>You must have cookies enabled to log in to {$config->getValue('siteName')}.</span>
<span class='fr'>Vous devez activer les cookies pour vous connecter au Forum {$config->getValue('networkName')}.</span>
<br />
</p>
<p>
<span class='en'>Your login ID is a concatenation of your first and last names: <b>First.Last</b> (case sensitive)
If you have forgotten your password please enter your login and ID and request a new random password to be sent to the email address associated with your Forum account.</span>
<span class='fr'>Votre identifiant de connexion est une combinaison de votre prénom et nom : <b>Premier.Dernier</b> (sensible à la casse). Si vous avez oublié votre mot de passe veuillez entrer votre information de connexion et identifiant, puis demandez qu’un nouveau mot de passe aléatoire soit envoyé au courriel associé à votre compte Forum.</span>
</p>";
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
		    $emailPassword = "
		        <form target='resetFrame' method='post' action='$wgServer$wgScriptPath/index.php/Special:PasswordReset' style='position:relative;left:10px;'>
		            <div style='width:180px;'>
                        <input id='wpUsername1' type='hidden' name='wpUsername' value='' />
                        <input type='hidden' name='wpEmail' value='' />
                        <input class='dark' type='submit' name='wpMailmypassword' id='wpMailmypassword' tabindex='6' value='E-mail new password' />
                    </div>
		        </form>
		        <iframe name='resetFrame' id='resetFrame' src='' style='width:0;height:0;border:0;' frameborder='0' width='0' height='0'></iframe>
		        <script type='text/javascript'>
		            function showResetMessage(message){
		                $('#failMessage').parent().show();
		                $('#failMessage').hide();
		                $('#failMessage').html(message);
		                $('#failMessage').slideDown();
		            }
		            $('#wpUsername1').attr('value', $('#wpName1').val());
		            $('#wpName1').change(function(){
		                $('#wpUsername1').attr('value', $('#wpName1').val());
		            }).keyup(function(){
		                $('#wpUsername1').attr('value', $('#wpName1').val());
		            });
		        </script>";
		    if($failMessage != ""){
		        $message .= "<script type='text/javascript'>
		            $('#failMessage').parent().show();
	                $('#failMessage').hide();
	                $('#failMessage').slideDown();
		        </script>";
		    }
		    $token = LoginForm::getLoginToken();
		    $name = $wgRequest->getText('wpName');
		    $name = sanitizeInput($name);
		    echo "<span class='highlights-text pBodyLogin en'>Login</span><span class='highlights-text pBodyLogin fr'>Connexion</span>
			<ul class='pBody pBodyLogin'>";
		    echo <<< EOF
<form style='position:relative;left:10px;' name="userlogin" method="post" action="$wgServer$wgScriptPath/index.php?title=Special:UserLogin&amp;action=submitlogin&amp;type=login&amp;returnto={$returnTo}">
    <div style="width:180px;">
        <div style="display:none;">
            <div style='display:inline-block;width:100%;font-size:12px;margin-bottom:10px;' id='failMessage'>$failMessage</div>
        </div>
        $message
        <input type='text' class='loginText dark' class='tooltip' title="Your username is in the form of 'First.Last' (case-sensitive)"
               style='width:100%; box-sizing: border-box; margin:0; margin-bottom:10px;' name="wpName" value="$name" id="wpName1" placeholder='Username (First.Last)' tabindex="1" size='20' /><br />
        <input type='password' class='loginPassword dark' 
               style='width:100%; box-sizing: border-box; margin:0; margin-bottom:10px;' name="wpPassword" id="wpPassword1" placeholder='Password' tabindex="2" size='20' /><br />
        <input type='checkbox' name="wpRemember"
               tabindex="4" value="1" id="wpRemember" /> <label class='en' for="wpRemember">Remember my login on this computer</label><label class='fr' for="wpRemember">Se souvenir de mes informations de connexion sur cet ordinateur</label><br />
        <input type='submit' class='dark' name="wpLoginattempt" id="wpLoginattempt" tabindex="5" style="margin-bottom:10px;" value="Log in" />
        <input type="hidden" name="wpLoginToken" value="$token" />
    </div>
</form>
$emailPassword
<script type='text/javascript'>
    if(wgLang == 'fr'){
        $('#wpName1').attr('placeholder', 'Mot d’utilisateur');
        $('#wpPassword1').attr('placeholder', 'Mot de passe');
        $('#wpLoginattempt').attr('value', 'Connexion');
        $('#wpMailmypassword').attr('value', 'Envoyer nouveau mot de passe').css('font-size', '0.75em');
    }
</script>
</li></ul>
EOF;
            if(isExtensionEnabled("Shibboleth")){
                echo "
                <script type='text/javascript'>
                    $('.pBodyLogin').parent().hide();
                    $('.pBodyLogin').parent().css('width', '100%');
                    $('#side').append(\"<div style='text-align: center; margin-bottom:15px;'><a id='ssoLogin' class='button' style='width: 130px;padding-left:0;padding-right:0;' href='{$config->getValue('shibLoginUrl')}'>Login w CCID</a></div>\");
                    $('#side').append(\"<div style='text-align: center; margin-bottom:15px;'><a id='forumLogin' class='button' style='width: 130px;padding-left:0;padding-right:0;'>Login w/out CCID</a></div>\");
                    $('#forumLogin').click(function(){
                        $(this).remove();
                        $('.pBodyLogin').parent().slideDown();
                    });
                </script>
                ";
                if(isset($_POST['wpLoginattempt']) || isset($_POST['wpMailmypassword'])){
                    echo "<script type='text/javascript'>
                        $('#forumLogin').click();
                    </script>";
                }
            }
        }
		wfRunHooks( 'MonoBookTemplateToolboxEnd', array( &$this ) );
		wfRunHooks( 'SkinTemplateToolboxEnd', array( &$this ) );
?>
	</li>
<?php
        
	}
    
} // end of class
