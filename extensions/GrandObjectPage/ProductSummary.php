<?php

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['ProductSummary'] = 'ProductSummary'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ProductSummary'] = $dir . 'ProductSummary.i18n.php';
$wgSpecialPageGroups['ProductSummary'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'ProductSummary::createSubTabs';

function runProductSummary($par){
    ProductSummary::execute($par);
}

class ProductSummary extends SpecialPage{

	function ProductSummary() {
		SpecialPage::__construct("ProductSummary", null, false, 'runProductSummary');
	}
	
	function userCanExecute($wgUser){
	    $person = Person::newFromUser($wgUser);
	    return $person->isRoleAtLeast(STAFF);
	}

	function execute($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $config;
		$wgOut->setPageTitle("{$config->getValue('productsTerm')} Summary");
		$phaseDates = $config->getValue('projectPhaseDates');
		$structure = Product::structure();
		$wgOut->addHTML("<div id='tabs'><ul>");
		// Tabs
		for($y=date('Y')-1; $y>=substr($phaseDates[1],0,4); $y--){
		    $wgOut->addHTML("<li><a href='#tabs-$y'>{$y}-".($y+1)."</a></li>");
		}
		$wgOut->addHTML("</ul>");
		for($y=date('Y')-1; $y>=substr($phaseDates[1],0,4); $y--){
		    $start = "$y-04-01";
		    $end = ($y+1)."-03-31";
		    $projects = Project::getAllProjectsDuring($start, $end);
		    $wgOut->addHTML("<div id='tabs-$y'>
		        <table class='wikitable' width='100%'>
		        <thead>
		            <tr>
		                <th rowspan='2'>Project</th>");
		    // Categories Header
		    foreach($structure['categories'] as $key => $category){
		        $wgOut->addHTML("<th colspan='".count($category['types'])."'>$key</th>");
		    }
		    $wgOut->addHTML("</tr>
		            <tr>");
		    // Types Header
		    foreach($structure['categories'] as $category){
		        $wgOut->addHTML("<th>".implode("</th><th>", array_keys($category['types']))."</th>");
		    }
		    $wgOut->addHTML("</tr>
		        </thead>
		        <tbody>");
		    // Project Rows
		    foreach($projects as $project){
		        $wgOut->addHTML("<tr>
		                <td style='white-space:nowrap;'>{$project->getName()}</td>");
		        foreach($structure['categories'] as $cat => $category){
		            $products = $project->getPapers($cat, $start, $end);
		            foreach($category['types'] as $key => $type){
		                $count = count(array_filter($products, function($product) use($key) { return ($product->getType() == $key); }));
		                $wgOut->addHTML("<td align='right'>{$count}</td>");
		            }
		        }
		        $wgOut->addHTML("</tr>");
		    }
		    $wgOut->addHTML("</tbody></table></div>");
		}
		$wgOut->addHTML("</div>
		    <script type='text/javascript'>
		        var tables = $('.wikitable').DataTable({
                    scrollX: true,
                    searching: true,
                    aLengthMenu: [
                        [25, 50, 100, 200, -1],
                        [25, 50, 100, 200, 'All']
                    ],
                    fixedColumns:   
                    {
                        leftColumns: 1
                    },
                    columnDefs: [
                       {type: 'natural', targets: 0}
                    ],
                    iDisplayLength: -1,
                    'dom': 'Blfrtip',
                    'buttons': [
                        'excel'
                    ]
                });
		        $('#tabs').tabs();
		        $('#tabs').bind('tabsselect', function(event, ui) {
                    _.defer(function(){
                        tables.draw();
                        $(window).trigger('resize');
                    });
                });
		        tables.draw();
		    </script>");
	}
	
	static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $config;

        if(self::userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "ProductSummary") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("{$config->getValue('productsTerm')} Summary", "$wgServer$wgScriptPath/index.php/Special:ProductSummary", $selected);
        }
        return true;
    }
}

?>
