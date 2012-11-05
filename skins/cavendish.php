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
		global $wgRequest, $wgServer, $wgScriptPath, $wgLogo, $wgTitle, $wgUser, $wgMessage;
		$this->skin = $skin = $this->data['skin'];
		$action = $wgRequest->getText( 'action' );

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
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery-ui.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.cookie.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.resizeY.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.limit-1.2.source.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.multiLimit.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery-ui.triggeredAutocomplete.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/DataTables/js/jquery.dataTables.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/filter.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/autosave.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/countries.en.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo "$wgServer$wgScriptPath"; ?>/scripts/jquery.qtip.min.js"></script>
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
	    <link type="text/css" href="<?php $this->text('stylepath') ?>/switcheroo/switcheroo.css" rel="Stylesheet" />
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
		<script type="text/javascript" src="<?php $this->text('stylepath' ) ?>/common/wikibits.js"></script>
		<script type='text/javascript'>
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
	        var sideToggled = 'out';
		    $(document).ready(function(){
		        $('a.disabledButton').click(function(e){
                    e.preventDefault();
                });
		        if($(".top-nav-element.selected").length > 1){
		            $("#grand-tab").removeClass("selected");
		        }
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
		        $("#sideToggle").click(function(){
		            $("#sideToggle").stop();
		            if(sideToggled == 'out'){
		                var marginRight = '0';
		                if ($.browser.msie && $.browser.version <= 7){
		                    marginRight = '-229px';
		                }
		                $('#mBody').animate({'margin-left' : '-229px',
		                                     'left' : '-229px',
		                                     'margin-right' : marginRight
                                            }, 200, 'swing', function(){
                                                
                                            });
                        sideToggled = 'in';
                    }
                    else{
                        $('#mBody').animate({'margin-left' : '0px',
                                             'left' : '0px',
                                             'margin-right' : '0px'
                                            }, 200, 'swing', function(){
                                                
                                            });
                        sideToggled = 'out';
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

	<div id="p-personal">
		
	</div>

	<div id="header">
		<a
	    href="<?php echo htmlspecialchars($this->data['nav_urls']['mainpage']['href'])?>"
	    title="<?php $this->msg('mainpage') ?>"><img style='margin-top:5px;' height='80px' src="<?php echo $wgServer.$wgScriptPath.'/skins/GrandForum-banner.gif'; ?>" alt='Logo' /></a>
	    <?php
	    
	    if($wgScriptPath != ""){
	        echo "<font style='font-size:36px;color:#FFFFFF;position:relative;bottom:-14px;left:-200px;'>DEVELOPMENT VERSION</font>";
	    }
	    
	    ?>
	    <!--span style='font-size:18px;color:red;position:relative;bottom:-14px;left:-250px;'>Important: site will be down for maintenance from 8-9AM (MST), Wed, Nov 16.</span-->
		<a name="top" id="contentTop"></a>
<ul class="top-nav">
      <?php 
				  global $notifications, $notificationFunctions, $wgUser, $wgScriptPath, $wgMessage;
		  ?>
		    <li class="top-nav-element tab-right"
		    <?php if($wgTitle->getNSText() == "Help"){
		        echo "selected";
		    } ?>>
				<span class="top-nav-left">&nbsp;</span>
				<a class="top-nav-mid" href="<?php echo $wgServer.$wgScriptPath; ?>/index.php/Help:Contents">Help/FAQ</a>	
				<span class="top-nav-right">&nbsp;</span>
			</li>
			<li id='grand-tab' class="top-nav-element tab-left
		    <?php if($wgTitle->getNSText() != "Help"){
		        echo "selected";
		    } ?>" style='margin-right:25px;margin-left:215px;'>
				<span class="top-nav-left">&nbsp;</span>
				<a class="top-nav-mid" href="<?php echo $wgServer.$wgScriptPath; ?>/index.php/Main_Page">GRAND</a>	
				<span class="top-nav-right">&nbsp;</span>
			</li>
			<?php
				$user = Person::newFromName($wgUser->getName());
			 	if($user->isRoleAtLeast(Manager) ){ ?>
			<li id='grand-tab' class="top-nav-element tab-left
			    <?php if($wgTitle->getText() == "EvaluationTable" ||
                         $wgTitle->getText() == "AcknowledgementsTable" ||
                         $wgTitle->getText() == "Duplicates" ||
                         $wgTitle->getText() == "EmptyEmailList" ||
                         $wgTitle->getText() == "InactiveUsers"){
			        echo "selected";
			    } ?>" >
				<span class="top-nav-left">&nbsp;</span>
				<a class="top-nav-mid" href="<?php echo $wgServer.$wgScriptPath; ?>/index.php/Special:EvaluationTable?section=RMC">Manager</a>	
				<span class="top-nav-right">&nbsp;</span>
			</li>
			<?php } ?>
			<li id='grand-tab' class="top-nav-element 
			    <?php if($wgTitle->getText() == "Survey"){
			        echo "selected";
			    } ?>" >
				<span class="top-nav-left">&nbsp;</span>
				<a class="top-nav-mid" href="<?php echo $wgServer.$wgScriptPath; ?>/index.php/Special:Survey">NAVEL Survey</a>	
				<span class="top-nav-right">&nbsp;</span>
			</li>
			<?php foreach($this->data['personal_urls'] as $key => $item) {
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
			        $item['text'] = "Logout";
			        $tabLeft = "tab-right";
			    }
			    else if($key == "anonlogin"){
			        continue;
			    }
			?>
			
			<li class="top-nav-element <?php echo $selected.' '.$tabLeft; ?>">
				<span class="top-nav-left">&nbsp;</span>
				<a id="lnk-<?php echo $key; ?>" class="top-nav-mid" href="<?php
					echo htmlspecialchars($item['href']) ?>"<?php
					if(!empty($item['class'])) { ?> class="<?php
						   echo htmlspecialchars($item['class']) ?>"<?php } ?>><?php
						   echo $item['text'] ?></a>
				<span class="top-nav-right">&nbsp;</span>
				<?php
				} ?>
			</li>
			<?php 
				// Report Tab
				global $notifications, $notificationFunctions, $wgUser, $wgScriptPath;
				if($wgUser->isLoggedIn()){
				    $p = Person::newFromId($wgUser->getId());
				    // Notification Tab
                    if(count($notifications) == 0){
                        foreach($notificationFunctions as $function){
                            call_user_func($function);
                        }
                    }	
					
                    if(count($p->getProjects()) > 0 && !$user->isRoleAtLeast(MANAGER)){
			            Project::createTab();
			        }
					if(!$user->isRoleAtLeast(MANAGER)){
					    ReportArchive::createTab();
					}
					    Report::createTab();
					if(!$user->isRoleAtLeast(MANAGER)){
					    MyMailingLists::createTab();
					}
					Notification::createTab();
				}
			?>
		</ul>
		
		<form name="searchform" action="<?php $this->text('wgScript') ?>" id="searchform">
			<div id="loggedin">
				<?php 
					if($wgUser->isLoggedIn()){ 
						$p = Person::newFromId($wgUser->getId());
						$name = $p->getNameForForms();
						$href = "#";
						if(count($p->getRoles()) > 0){
				            $href = "{$p->getUrl()}";
				        }
						echo "Logged in as: <a style='color:#FFFFFF;font-weight:bold;' href='$href'>$name</a>";
					}
					else{
						echo "Not logged in";
					}
				?>
			</div>
			<div>
			<input type="hidden" name="title" value="<?php $this->text('searchtitle') ?>"/>
			<input id="searchInput" name="search" type="text"<?php echo $this->skin->tooltipAndAccesskey('search');
					if( isset( $this->data['search'] ) ) {
						?> value="<?php $this->text('search') ?>"<?php } ?> />
			<input type='submit' name="go" class="searchButton" id="searchGoButton"	value="<?php $this->msg('searcharticle') ?>"<?php echo $this->skin->tooltipAndAccesskey( 'search-go' ); ?> />&nbsp;
			<input type='submit' name="fulltext" class="searchButton" id="mw-searchButton" value="<?php $this->msg('searchbutton') ?>"<?php echo $this->skin->tooltipAndAccesskey( 'search-fulltext' ); ?> />
	       </div>
		</form>
	</div>
    <div id='submenu'>
        <ul>
		   	<?php
		   	 TabUtils::grandTabs($this->data['content_actions']);
		   	 foreach($this->data['content_actions'] as $key => $action) {
		       ?><li
		       <?php if($action['class']) { ?>class="<?php echo htmlspecialchars($action['class']) ?>"<?php } ?>
		       ><a href="<?php echo htmlspecialchars($action['href']) ?>"><?php
		       echo htmlspecialchars($action['text']) ?></a></li><?php
		     } ?>
		</ul>
    </div>
    <a id="sideToggle">Toggle Menu</a>
	<div id="mBody" class='displayTable'>
	    <div class='displayTableRow'>
            
		<div id="side" class='displayTableCell'>
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
		<div id="spacer" class='displayTableCell'>
		</div>
		<div id="bodyContent" class='displayTableCell'>
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
		</div><!-- end of MAINCONTENT div -->	
	</div>
	</div><!-- end of MBODY div -->
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
	    echo "					<li id='f-disclaimer'><a href='index.php?action=api.index'>API</a></li>
						    <li id='f-disclaimer'><a href='mailto:support@forum.grand-nce.ca'>Support</a></li>\n";
    ?>
    </ul></td><td align="right" width="1%" nowrap="nowrap"><?php if($this->data['poweredbyico']) { ?><div id="f-poweredbyico"><?php $this->html('poweredbyico') ?></div><?php } ?></td></tr></table><img style='display:none;' src='../skins/Throbber.gif' />
	    </div><!-- end of the FOOTER div -->
	<div class="visualClear"></div>
	
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
		    echo "<span>People</span>
			<ul class='pBody' style='background:#F3EBF5'>";
		    $me = Person::newFromId($wgUser->getId());
		    echo "<li id='userRequest'><a href='{$wgScriptPath}/index.php/Special:UserSearch'>Find Member</a></li>";
		    if($me->isRoleAtLeast(CNI)){    
		        echo "<li id='userRequest'><a href='{$wgScriptPath}/index.php/Special:AddMember'>Add Member</a></li>";
		        echo "<li id='userEditRequest'><a href='{$wgScriptPath}/index.php/Special:EditMember'>Edit Member</a></li>";
		        echo "<li id='userEditRelation'><a href='{$wgScriptPath}/index.php/Special:EditRelations'>Edit Relations</a></li>";
		    }
		    echo "</ul>";
		    echo "<span>Products</span>
			<ul class='pBody' style='background:#F3EBF5'>";
		    echo "<li id='addPublication'><a href='{$wgScriptPath}/index.php/Special:AddPublicationPage'>Add/Edit Publication</a></li>";
		    echo "<li id='addArtifact'><a href='{$wgScriptPath}/index.php/Special:AddArtifactPage'>Add/Edit Artifact</a></li>";
		    echo "<li id='addPresentation'><a href='{$wgScriptPath}/index.php/Special:AddPresentationPage'>Add/Edit Presentation</a></li>";
			echo "<li id='addActivity'><a href='{$wgScriptPath}/index.php/Special:AddActivityPage'>Add/Edit Activity</a></li>";
			echo "<li id='addPress'><a href='{$wgScriptPath}/index.php/Special:AddPressPage'>Add/Edit Press</a></li>";
			echo "<li id='addAward'><a href='{$wgScriptPath}/index.php/Special:AddAwardPage'>Add/Edit Award</a></li>";
			if($me->isRoleAtLeast(CNI)){
			    echo "<li id='addContribution'><a href='{$wgScriptPath}/index.php/Special:AddContributionPage'>Add/Edit Contribution</a></li>";
			}
			echo "<li id='addMultimedia'><a href='{$wgScriptPath}/index.php/Special:AddMultimediaStoryPage'>Add/Edit Multimedia Story</a></li>";
			echo "</ul>";
		    echo "<span>Other</span>
			<ul class='pBody' style='background:#F3EBF5'>";
			echo "<li id='messageBoard'><a href='{$wgScriptPath}/index.php/Special:Postings'>Message Board</a></li>";
			echo "<li id='recentNews'><a href='{$wgScriptPath}/index.php?action=getNews'>Recent News</a></li>";
			echo "<li id='recentNews'><a href='{$wgScriptPath}/index.php/Special:SpecialPages'>Other Tools</a></li>";
		}
		else {
		    global $wgSiteName, $wgTitle;
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
		    $name = $wgRequest->getText( 'wpName' );
		    
		    $returnTo = "";
		    if($wgTitle->getNsText() != ""){
		        $returnTo = str_replace(" ", "_", $wgTitle->getNsText()).':';
		    }
		    $returnTo .= str_replace(" ", "_", $wgTitle->getText());
		    echo "<span>Login</span>
			<ul class='pBody' style='background:#F3EBF5'>";
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
