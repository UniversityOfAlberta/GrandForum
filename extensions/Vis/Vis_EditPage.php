<?php

/**
 * Author: Brendan Tansey
 * Contains functions for creating the customized EditPage interface
 * 
 */

//TODO: This entire file has been deprecated, and will likely disappear soon.  Don't rely on it.
class Vis_EditPage {
  function addToolbars($editpage){
    global $wgUser, $wgOut;

    if( $wgUser->getOption('showtoolbar') and !$editpage->isCssJsSubpage ) {
      $extratoolbar = $this->getExtraToolbar();
      $editcombobox = $this->getComboBox();
      $flowchartbar = $this->getFlowChartToolBar();
      $lineartoolbar = $this->getLinearToolBar();
      $topicmaptoolbar = $this->getTopicMapToolBar();
      $hierarchytoolbar = $this->getHierarchyToolBar();
      $spidermaptoolbar = $this->getSpiderMapToolBar();
      $storyevolutiontoolbar = $this->getStoryEvolutionToolBar();
    }

    else {
      $extratoolbar= '';
      $editcombobox = '';
      $flowchartbar = '';
      $lineartoolbar = '';
      $topicmaptoolbar = '';
      $hierarchytoolbar = '';
      $spidermaptoolbar = '';
      $storyevolutiontoolbar = '';
    }
    
    $wgOut->addHTML("{$extratoolbar}
		     {$editcombobox}
		     
		     {$flowchartbar}
		     {$lineartoolbar}
		     {$topicmaptoolbar}
		     {$hierarchytoolbar}
		     {$spidermaptoolbar}
		     {$storyevolutiontoolbar}
		     ");
    
  }
  
  function getComboBox(){
    global $wgJsMimeType;
    
    $toolbar ="<script type='$wgJsMimeType'>/*<![CDATA[*/\n";
    $toolbar.="document.writeln(\"<div id='combobox'>\");\n";
    $toolbar.="document.writeln(\"<p>Select the type of EGO you want: <select name='selector' size = '1' onChange='setSelectedToolbar(this.options.selectedIndex)'><option value =''></option><option value='value1'>Timeline</option><option value='value2'>Spider Map</option><option value='value3'>Topic Map</option><option value='value4'>Story Evolution</option><option value='value5'>Flow Chart</option><option value='value6'>Hierarchy</option></select></p>\");\n";
    $toolbar.="document.writeln(\"</div>\");\n";
    $toolbar.="/*]]>*/\n</script>";
    return $toolbar;
  }


  function getFlowChartToolBar(){
    global $wgLang, $wgMimeType, $wgJsMimeType, $ecVisImagePath;

    /**
     * toolarray an array of arrays which each include the filename of
     * the button image (without path), the opening tag, the closing tag,
     * and optionally a sample text that is inserted between the two when no
     * selection is highlighted.
     * The tip text is shown when the user moves the mouse over the button.
     *
     * Already here are accesskeys (key), which are not used yet until someone
     * can figure out a way to make them work in IE. However, we should make
     * sure these keys are not defined on the edit page.
     */


    $toolarray=array(
		     array(	'image'	=>'fc.png',
				'open'	=>	"<annokiblooms>\\n!GOTYPE!!FLOWCHART!</annokiblooms>\\n",
				'close'	=>	'',
				'sample'=>	'',
				'tip'	=>	"Add FlowChart",
				'key'	=>	'F'
				),
		     array(	'image' =>'nodeName.png',
				'open'	=>	'-x- ',
				'close'	=>	' -xx-',
				'sample'=>	'',
				'tip'	=>	"Node Name",
				'key'	=>	'K'
				),
		     array(	'image' =>'beginning.png',
				'open'	=>	'-b- ',
				'close'	=>	' -bb-',
				'sample'=>	'',
				'tip'	=>	"Add Beginning Tag",
				'key'	=>	'G'
				),
		     array(	'image' =>'decision.png',
				'open'	=>	'-d- ',
				'close'	=>	' -dd-',
				'sample'=>	'',
				'tip'	=>	"Add Decision Tag",
				'key'	=>	'W'
				),
		     array(	'image' =>'ending.png',
				'open'	=>	'-e- ',
				'close'	=>	' -ee-',
				'sample'=>	'',
				'tip'	=>	"Add Ending Tag",
				'key'	=>	'O'
				),
		     array(	'image' =>'summary.png',
				'open'	=>	'!S!',
				'close'	=>	'!SS!',
				'sample'=>	'Enter Summary of the step here',
				'tip'	=>	"Add Summary Tag",
				'key'	=>	'Q'
				),
		     array(	'image' =>'title.png',
				'open'	=>	'!T! ',
				'close'	=>	' !TT!',
				'sample'=>	'',
				'tip'	=>	"Add Title",
				'key'	=>	'U'
				),
		     array(	'image' =>'numberOfNodes.png',
				'open'	=>	'!#!Enter the step number here!##!!S!',
				'close'	=>	'!SS!',
				'sample'=>	'Enter summary of step here',
				'tip'	=>	"Add Number of Nodes",
				'key'	=>	'V'
				),

		     );
    $flowchartbar ="<script type='$wgJsMimeType'>\n/*<![CDATA[*/\n";

    $flowchartbar.="document.writeln(\"<div id='flowchart' style='display:none;'>\");\n";

    //add in int to count number of buttons, on specific number, add in a blanks pace
    $num =0;
    $type = 1;
    
    foreach($toolarray as $tool) {
      $image=$ecVisImagePath.$tool['image'];
      $open=$tool['open'];
      $close=$tool['close'];
      $sample = wfEscapeJsString( $tool['sample'] );
			
      // Note that we use the tip both for the ALT tag and the TITLE tag of the image.
      // Older browsers show a "speedtip" type message only for ALT.
      // Ideally these should be different, realistically they
      // probably don't need to be.
      $tip = wfEscapeJsString( $tool['tip'] );

      $flowchartbar.="addButtonNew('$image','$tip','$open','$close','$sample','$num','$type');\n";
      $num++;

    }
    $flowchartbar.="document.writeln(\"</div>\");\n";
    $flowchartbar.="/*]]>*/\n</script>";
    return $flowchartbar;
  }


  function getHierarchyToolBar(){
    global $ecVisImagePath;
    global $wgLang, $wgMimeType, $wgJsMimeType;

    /**
     * toolarray an array of arrays which each include the filename of
     * the button image (without path), the opening tag, the closing tag,
     * and optionally a sample text that is inserted between the two when no
     * selection is highlighted.
     * The tip text is shown when the user moves the mouse over the button.
     *
     * Already here are accesskeys (key), which are not used yet until someone
     * can figure out a way to make them work in IE. However, we should make
     * sure these keys are not defined on the edit page.
     */


    $toolarray=array(
		     array(	'image'	=>'h.png',
				'open'	=>	"<annokiblooms>\\n!GOTYPE!!HIERARCHY!</annokiblooms>\\n",
				'close'	=>	'',
				'sample'=>	'',
				'tip'	=>	"Add Hierarchy",
				'key'	=>	'F'
				),
		     array(	'image' =>'paragraph.png',
				'open'	=>	'<annokiblooms>\\n!P! ',
				'close'	=>	' !PP!',
				'sample'=>	'',
				'tip'	=>	"Add Paragraph Tag",
				'key'	=>	'P'
				),
		     array(	'image' =>'title.png',
				'open'	=>	'!T! ',
				'close'	=>	' !TT!',
				'sample'=>	'Add Title In Here',
				'tip'	=>	"Add Title",
				'key'	=>	'U'
				),
	
                     array(     'image' =>'summary.png',
                                'open'  =>      '!S!',
                                'close' =>      '!SS!</annokiblooms>\\n',
                                'sample'=>      'Enter Summary of the step here',
                                'tip'   =>      "Add Summary Tag",
                                'key'   =>      'Q'
                                ),
  		     );

    $hierarchytoolbar ="<script type='$wgJsMimeType'>\n/*<![CDATA[*/\n";
    $hierarchytoolbar.="document.writeln(\"<div id='hierarchy' style='display:none;'>\");\n";

    //add in int to count number of buttons, on specific number, add in a blanks pace
    $num =0;
    $type = 4;
    foreach($toolarray as $tool) {
      $image=$ecVisImagePath . $tool['image'];
      $open=$tool['open'];
      $close=$tool['close'];
      $sample = wfEscapeJsString( $tool['sample'] );
			
      // Note that we use the tip both for the ALT tag and the TITLE tag of the image.
      // Older browsers show a "speedtip" type message only for ALT.
      // Ideally these should be different, realistically they
      // probably don't need to be.
      $tip = wfEscapeJsString( $tool['tip'] );

      $hierarchytoolbar.="addButtonNew('$image','$tip','$open','$close','$sample','$num','$type');\n";
      $num++;

    }
    $hierarchytoolbar.="document.writeln(\"</div>\");\n";
    $hierarchytoolbar.="/*]]>*/\n</script>";
    return $hierarchytoolbar;
  }



  function getLinearToolBar(){
    global $ecVisImagePath;
    global $wgLang, $wgMimeType, $wgJsMimeType;

    /**
     * toolarray an array of arrays which each include the filename of
     * the button image (without path), the opening tag, the closing tag,
     * and optionally a sample text that is inserted between the two when no
     * selection is highlighted.
     * The tip text is shown when the user moves the mouse over the button.
     *
     * Already here are accesskeys (key), which are not used yet until someone
     * can figure out a way to make them work in IE. However, we should make
     * sure these keys are not defined on the edit page.
     */


    $toolarray=array(
		     array(	'image'	=> 'tl.png',
				'open'	=>	"<annokiblooms>\\n!GOTYPE!!LINEAR!</annokiblooms>\\n",
				'close'	=>	'',
				'sample'=>	'',
				'tip'	=>	"Add Time Line",
				'key'	=>	'F'
				),
		     array(	'image' =>'title.png',
				'open'	=>	'<annokiblooms>!T! ',
				'close'	=>	' !TT!',
				'sample'=>	'Add Title In Here',
				'tip'	=>	"Add Title",
				'key'	=>	'U'
				),	
		     array(	'image' =>'summary.png',
				'open'	=>	'!#!Enter Section Number Here!##!\\n</annokiblooms>\\n',
				'close'	=>	'=== Enter Title In Here ===\\n<annokiblooms>\\n\\n!P! Enter Story in here !PP!\\n</annokiblooms>\\n',
				'sample'=>	'',
				'tip'	=>	"Create a New Story Section",
				'key'	=>	'Q'
				),
	
		     );

    $lineartoolbar ="<script type='$wgJsMimeType'>\n/*<![CDATA[*/\n";

    $lineartoolbar.="document.writeln(\"<div id='timeline' style='display:none;'>\");\n";

    //add in int to count number of buttons, on specific number, add in a blanks pace
    $num =0;
    $type = 2;
    foreach($toolarray as $tool) {
      $image=$ecVisImagePath . $tool['image'];
      $open=$tool['open'];
      $close=$tool['close'];
      $sample = wfEscapeJsString( $tool['sample'] );
			
      // Note that we use the tip both for the ALT tag and the TITLE tag of the image.
      // Older browsers show a "speedtip" type message only for ALT.
      // Ideally these should be different, realistically they
      // probably don't need to be.
      $tip = wfEscapeJsString( $tool['tip'] );

      $lineartoolbar.="addButtonNew('$image','$tip','$open','$close','$sample','$num','$type');\n";
      $num++;

    }
    $lineartoolbar.="document.writeln(\"</div>\");\n";
    $lineartoolbar.="/*]]>*/\n</script>";
    return $lineartoolbar;
  }

  function getTopicMapToolBar(){
    global $ecVisImagePath;
    global $wgLang, $wgMimeType, $wgJsMimeType;

    /**
     * toolarray an array of arrays which each include the filename of
     * the button image (without path), the opening tag, the closing tag,
     * and optionally a sample text that is inserted between the two when no
     * selection is highlighted.
     * The tip text is shown when the user moves the mouse over the button.
     *
     * Already here are accesskeys (key), which are not used yet until someone
     * can figure out a way to make them work in IE. However, we should make
     * sure these keys are not defined on the edit page.
     */


    $toolarray=array(
		     array(	'image'	=> 'tm.png',
				'open'	=>	"<annokiblooms>\\n!GOTYPE!!TOPICMAP!</annokiblooms>\\n",
				'close'	=>	'',
				'sample'=>	'',
				'tip'	=>	"Add Topic Map",
				'key'	=>	'F'
				),
		     array(	'image' =>'summary.png',
				'open'	=>	'!S!',
				'close'	=>	'!SS!',
				'sample'=>	'Enter Summary of the step here',
				'tip'	=>	"Add Summary Tag",
				'key'	=>	'Q'
				),
		     array(	'image' =>'title.png',
				'open'	=>	'<annokiblooms> [root] ',
				'close'	=>	' [/root]',
				'sample'=>	'Add Title in here',
				'tip'	=>	"Add Title",
				'key'	=>	'U'
				),
		     array(	'image' =>'summary.png',
				'open'	=>	'!#!Enter Section Number Here!##!\\n</annokiblooms>\\n',
				'close'	=>	'=== Enter Heading Here ===\\n<annokiblooms>\\n\\n',
				'sample'=>	'',
				'tip'	=>	"Create a New SubTopic",
				'key'	=>	'Q'
				),
		     array(	'image' =>'summary.png',
				'open'	=>	'[keyinfo]',
				'close'	=>	'[/keyinfo]',
				'sample'=>	'',
				'tip'	=>	"Key Information",
				'key'	=>	'Q'
				),
		     array(	'image' =>'summary.png',
				'open'	=>	'[*]Enter Section Number Here[**]\\n</annokiblooms>\\n',
				'close'	=>	'==== Enter Heading Here ====\\n',//</annokiblooms>\\n\\n',
				'sample'=>	'',
				'tip'	=>	"Create a New Sub-SubTopic",
				'key'	=>	'Q'
				),
	
			
		     );

    $topicmaptoolbar ="<script type='$wgJsMimeType'>\n/*<![CDATA[*/\n";

    $topicmaptoolbar.="document.writeln(\"<div id='topicmap' style='display:none;'>\");\n";

    //add in int to count number of buttons, on specific number, add in a blanks pace
    $num =0;
    $type = 3;
    foreach($toolarray as $tool) {
      $image=$ecVisImagePath . $tool['image'];
      $open=$tool['open'];
      $close=$tool['close'];
      $sample = wfEscapeJsString( $tool['sample'] );
			
      // Note that we use the tip both for the ALT tag and the TITLE tag of the image.
      // Older browsers show a "speedtip" type message only for ALT.
      // Ideally these should be different, realistically they
      // probably don't need to be.
      $tip = wfEscapeJsString( $tool['tip'] );

      $topicmaptoolbar.="addButtonNew('$image','$tip','$open','$close','$sample','$num','$type');\n";
      $num++;

    }
    $topicmaptoolbar.="document.writeln(\"</div>\");\n";
    $topicmaptoolbar.="/*]]>*/\n</script>";
    return $topicmaptoolbar;
  }

  function getSpiderMapToolBar(){
    global $ecVisImagePath;
    global $wgLang, $wgMimeType, $wgJsMimeType;

    /**
     * toolarray an array of arrays which each include the filename of
     * the button image (without path), the opening tag, the closing tag,
     * and optionally a sample text that is inserted between the two when no
     * selection is highlighted.
     * The tip text is shown when the user moves the mouse over the button.
     *
     * Already here are accesskeys (key), which are not used yet until someone
     * can figure out a way to make them work in IE. However, we should make
     * sure these keys are not defined on the edit page.
     */


    $toolarray=array(
		     array(	'image'	=> 'addApplet.png',
				'open'	=>	"<annokiblooms>\\n!GOTYPE!!SPIDERMAP!</annokiblooms>\\n",
				'close'	=>	'',
				'sample'=>	'',
				'tip'	=>	"Add Spider Map",
				'key'	=>	'F'
				),
		     array(	'image' =>'summary.png',
				'open'	=>	'!S!',
				'close'	=>	'!SS!',
				'sample'=>	'Enter Summary of the step here',
				'tip'	=>	"Add Summary Tag",
				'key'	=>	'Q'
				),
		     array(	'image' =>'title.png',
				'open'	=>	'!T! ',
				'close'	=>	' !TT!',
				'sample'=>	'',
				'tip'	=>	"Add Title",
				'key'	=>	'U'
				),
		     array(	'image' =>'numberOfNodes.png',
				'open'	=>	'!#!Enter the step number here!##!!S!',
				'close'	=>	'!SS!',
				'sample'=>	'Enter summary of step here',
				'tip'	=>	"Add Number of Nodes",
				'key'	=>	'V'
				),
		     array(	'image' =>'nodeName.png',
				'open'	=>	'-x- ',
				'close'	=>	' -xx-',
				'sample'=>	'',
				'tip'	=>	"Node Name",
				'key'	=>	'K'
				),		

		     );

    $spidermaptoolbar ="<script type='$wgJsMimeType'>\n/*<![CDATA[*/\n";

    $spidermaptoolbar.="document.writeln(\"<div id='spidermap' style='display:none;'>\");\n";

    //add in int to count number of buttons, on specific number, add in a blanks pace
    $num =0;
    $type = 5;
    foreach($toolarray as $tool) {
      $image=$ecVisImagePath . $tool['image'];
      $open=$tool['open'];
      $close=$tool['close'];
      $sample = wfEscapeJsString( $tool['sample'] );
			
      // Note that we use the tip both for the ALT tag and the TITLE tag of the image.
      // Older browsers show a "speedtip" type message only for ALT.
      // Ideally these should be different, realistically they
      // probably don't need to be.
      $tip = wfEscapeJsString( $tool['tip'] );

      $spidermaptoolbar.="addButtonNew('$image','$tip','$open','$close','$sample','$num','$type');\n";
      $num++;

    }
    $spidermaptoolbar.="document.writeln(\"</div>\");\n";
    $spidermaptoolbar.="/*]]>*/\n</script>";
    return $spidermaptoolbar;
  }

  function getStoryEvolutionToolBar(){
    global $ecVisImagePath;
    global $wgLang, $wgMimeType, $wgJsMimeType;

    /**
     * toolarray an array of arrays which each include the filename of
     * the button image (without path), the opening tag, the closing tag,
     * and optionally a sample text that is inserted between the two when no
     * selection is highlighted.
     * The tip text is shown when the user moves the mouse over the button.
     *
     * Already here are accesskeys (key), which are not used yet until someone
     * can figure out a way to make them work in IE. However, we should make
     * sure these keys are not defined on the edit page.
     */


    $toolarray=array(
		     array(	'image'	=> 'addApplet.png',
				'open'	=>	"<annokiblooms>\\n!GOTYPE!!STORYEVOLUTION!</annokiblooms>\\n",
				'close'	=>	'',
				'sample'=>	'',
				'tip'	=>	"Add Story Evolution",
				'key'	=>	'F'
				),
		     array(	'image' =>'title.png',
				'open'	=>	'<annokiblooms>!T! ',
				'close'	=>	' !TT!',
				'sample'=>	'Add Title In Here',
				'tip'	=>	"Add Title",
				'key'	=>	'U'
				),	
		     array(	'image' =>'summary.png',
				'open'	=>	'!#!Enter Section Number Here!##!\\n</annokiblooms>\\n',
				'close'	=>	'=== Introduction ===\\n<annokiblooms>\\n\\n!P! Enter Story in here !PP!',
				'sample'=>	'',
				'tip'	=>	"Create an Introduction",
				'key'	=>	'Q'
				),
		     array(	'image' =>'summary.png',
				'open'	=>	'!#!Enter Section Number Here!##!\\n</annokiblooms>\\n',
				'close'	=>	'=== Initial Incident ===\\n<annokiblooms>\\n\\n!P! Enter Story in here !PP!',
				'sample'=>	'',
				'tip'	=>	"Create an Initial Incident",
				'key'	=>	'Q'
				),
		     array(	'image' =>'summary.png',
				'open'	=>	'!#!Enter Section Number Here!##!\\n</annokiblooms>\\n',
				'close'	=>	'=== Rising Action ===\\n<annokiblooms>\\n\\n!P! Enter Story in here !PP!',
				'sample'=>	'',
				'tip'	=>	"Create a Rising Action",
				'key'	=>	'Q'
				),
		     array(	'image' =>'summary.png',
				'open'	=>	'!#!Enter Section Number Here!##!\\n</annokiblooms>\\n',
				'close'	=>	'=== Climax ===\\n<annokiblooms>\\n\\n!P! Enter Story in here !PP!',
				'sample'=>	'',
				'tip'	=>	"Create a Climax",
				'key'	=>	'Q'
				),
		     array(	'image' =>'summary.png',
				'open'	=>	'!#!Enter Section Number Here!##!\\n</annokiblooms>\\n',
				'close'	=>	'=== Falling Action ===\\n<annokiblooms>\\n\\n!P! Enter Story in here !PP!',
				'sample'=>	'',
				'tip'	=>	"Create a Falling Action",
				'key'	=>	'Q'
				),
		     array(	'image' =>'summary.png',
				'open'	=>	'!#!Enter Section Number Here!##!\\n</annokiblooms>\\n',
				'close'	=>	'=== Resolution ===\\n<annokiblooms>\\n\\n!P! Enter Story in here !PP!',
				'sample'=>	'',
				'tip'	=>	"Create an Resolution",
				'key'	=>	'Q'
				),
		     array(	'image' =>'summary.png',
				'open'	=>	'!#!Enter Section Number Here!##!\\n</annokiblooms>\\n',
				'close'	=>	'=== Denouement ===\\n<annokiblooms>\\n\\n!P! Enter Story in here !PP!\\n</annokiblooms>\\n',
				'sample'=>	'',
				'tip'	=>	"Create a Denouement",
				'key'	=>	'Q'
				),
		     array(	'image' =>'summary.png',
				'open'	=>	'[keyinfo]',
				'close'	=>	'[/keyinfo]',
				'sample'=>	'',
				'tip'	=>	"Key Information",
				'key'	=>	'Q'
				),			
		

		     );

    $storyevolutiontoolbar ="<script type='$wgJsMimeType'>\n/*<![CDATA[*/\n";

    $storyevolutiontoolbar.="document.writeln(\"<div id='storyevolution' style='display:none;'>\");\n";

    //add in int to count number of buttons, on specific number, add in a blanks pace
    $num =0;
    $type = 6;
    foreach($toolarray as $tool) {
      $image=$ecVisImagePath . $tool['image'];
      $open=$tool['open'];
      $close=$tool['close'];
      $sample = wfEscapeJsString( $tool['sample'] );
			
      // Note that we use the tip both for the ALT tag and the TITLE tag of the image.
      // Older browsers show a "speedtip" type message only for ALT.
      // Ideally these should be different, realistically they
      // probably don't need to be.
      $tip = wfEscapeJsString( $tool['tip'] );

      $storyevolutiontoolbar.="addButtonNew('$image','$tip','$open','$close','$sample','$num','$type');\n";
      $num++;

    }
    $storyevolutiontoolbar.="document.writeln(\"</div>\");\n";
    $storyevolutiontoolbar.="/*]]>*/\n</script>";
    return $storyevolutiontoolbar;
  }


  function getExtraToolBar(){
    global $ecVisImagePath;
    global $wgLang, $wgMimeType, $wgJsMimeType;

    /**
     * toolarray an array of arrays which each include the filename of
     * the button image (without path), the opening tag, the closing tag,
     * and optionally a sample text that is inserted between the two when no
     * selection is highlighted.
     * The tip text is shown when the user moves the mouse over the button.
     *
     * Already here are accesskeys (key), which are not used yet until someone
     * can figure out a way to make them work in IE. However, we should make
     * sure these keys are not defined on the edit page.
     */


    $toolarray=array(
		     array(	'image'	=>'addDate.png',
				'open'	=>	date('M d Y'),
				'close'	=>	'',
				'sample'=>	'',
				'tip'	=>	"Add Date Tag",
				'key'	=>	'R'
				),
		     array(	'image'	=>'researchPaperTemplate.png',
				'open'	=>	'{{PaperTemplate|title=<>|authors=<>|sourcehtml=<>|abstract=<>|comments=<>}}',
				'close'	=>	'',
				'sample'=>	'',
				'tip'	=>	"Add Research Paper Template",
				'key'	=>	'A'
				),
		     array(	'image'	=>'cscCourseTemplate.png',
				'open'	=>	'To DO',
				'close'	=>	'\n',
				'sample'=>	'',
				'tip'	=>	"Add Biliography Course Page",
				'key'	=>	'B'
				),
				

		     );
    $extratoolbar ="<script type='$wgJsMimeType'>\n/*<![CDATA[*/\n";
    $extratoolbar.="document.writeln();";
    $extratoolbar.="document.writeln(\"<div id='extratoolbar'>\");\n";
		
    //add in int to count number of buttons, on specific number, add in a blanks pace
    $num =0;
    $type = 0;
    foreach($toolarray as $tool) {
      $image=$ecVisImagePath . $tool['image'];
      $open=$tool['open'];
      $close=$tool['close'];
      $sample = wfEscapeJsString( $tool['sample'] );
			
      // Note that we use the tip both for the ALT tag and the TITLE tag of the image.
      // Older browsers show a "speedtip" type message only for ALT.
      // Ideally these should be different, realistically they
      // probably don't need to be.
      $tip = wfEscapeJsString( $tool['tip'] );

      $extratoolbar.="addButtonNew('$image','$tip','$open','$close','$sample','$num','$type');\n";
      $num++;

    }
    $extratoolbar.="document.writeln(\"</div>\");\n";
    $extratoolbar.="/*]]>*/\n</script>";
    return $extratoolbar;
  }
}
?>
