<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Solr'] = 'Solr';
$wgExtensionMessagesFiles['Solr'] = $dir . 'Solr.i18n.php';
$wgSpecialPageGroups['Solr'] = 'grand-tools';

// SQL tables
$sqlTables = array("milestone" => "grand_milestones",
                   "posting" => "grand_postings",
                   "product" => "grand_products",
                   "project" => "grand_project_descriptions",
                   "report" => "grand_report_blobs" );

// SQL Primary Keys
$sqlPrimaryKeys = array("milestone" => "id",
                        "posting" => "id",
                        "product" => "id",
                        "project" => "id",
                        "report" => "blob_id" );


// SQL table field names to be retrieved
// Legend for secondary searches:  label | table name | id field | target field
$milestoneFields = array("project_id" => "Project | grand_project | id | name",
												 "title" => "Title",
												 "status" => "Status",
												 "description" => "Description",
												 "assessment" => "Assessment",
												 "edited_by" => "Editor | mw_user | user_id | user_name.blob",
												 "start_date" => "Start Date",
												 "end_date" => "End Date");

$postingFields = array("title" => "Title",
                       "descr" => "Description");

$productFields = array("title" => "Title",
											 "status" => "Status",
											 "authors.blob" => "Authors",
											 "data.blob" => ""); // uses blob field names

$projectFields = array("full_name" => "Title",
                       "description" => "Description");

$reportFields = array("edited_by" => "Editor | mw_user | user_id | user_name.blob",
											"user_id" => "User | mw_user | user_id | user_name.blob",
											"proj_id" => "Project | grand_project | id | name",
											"changed" => "Date",
											"data.blob" => ""); // uses blob field names


$resultFields = array("milestone" => $milestoneFields,
									  	"posting" => $postingFields,
											"product" => $productFields,
											"project" => $projectFields,
											"report" => $reportFields );



function runSolr($par) {
	Solr::run($par);
}

class Solr extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('Solr');
		SpecialPage::SpecialPage('Solr', MANAGER.'+', true, 'runSolr');
	}

	function run(){
	    global $wgUser, $wgOut, $wgServer, $wgScriptPath, 
             $sqlTables, $resultFields, $sqlPrimaryKeys;

      if(isset($_GET['type'])){
        $type = $_GET['type'];
        $ids = explode(",",$_GET['id']); // 0:field_name  1:key
        if($type == "product"){
          Solr::getProducts($ids);
        }
        else if($type == "project"){
          Solr::getProjects($ids);
        }
        else{
          header('Content-Type: text/json');
          print json_encode(array());
          exit;
        }
      }

      $sqlTables = json_encode($sqlTables);
      $sqlPrimaryKeys = json_encode($sqlPrimaryKeys);
      $resultFields = json_encode($resultFields);

      $script =<<<EOF
        <script type="text/javascript">

          // Write PHP arrays
          var sqlTables = {$sqlTables};
          var sqlPrimaryKeys = {$sqlPrimaryKeys};
          var resultFields = {$resultFields};

          
          $(document).ready(function(){

						$("#query").focus();
            // productsDT = $('#resultsTable').dataTable({
            //     "bPaginate": false,
            //     "bLengthChange": false,
            //     "bFilter": false,
            // });
          });

					$("#searchForm").submit(function() {
							return false;
					});


          $("#query").live("keypress", function(e){
            if (e.which == 13){
							e.preventDefault();
              query = $("#query").val();
              url_solr = "{$wgServer}{$wgScriptPath}/extensions/Solr/curl.php?query=(" + query + ")"

							$.getJSON(url_solr,
								function(data) {
									$.each(data, function(key, val) { walkJsonTree(key, val) });
							});

							e.preventDefault();
							return null;
            }
          });

          
					$("#b_search").live("click", function() {
						var press = jQuery.Event("keypress");
						press.which = 13;
						$("#query").trigger(press);
            return false;
					});

          var datatable_refs = new Array();
          datatable_refs['product'] = null;
          datatable_refs['project'] = null;
          datatable_refs['milestone'] = null;

          function populateTable(data, type){
            table = "";
            count = 0;
            $.each(data, function(row_i, row){
              table += "<tr>";
              $.each(row, function(col_i, col){
                table += "<td>"+col+"</td>";
              })
              table += "</tr>";
              count++;
            });

            if(count > 0){
              $("#"+type+"_results").show();
              if(datatable_refs[type] !== null){
                datatable_refs[type].fnDestroy();
              }
              $("#"+type+"_results #"+type+"sTable tbody").html(table);
              datatable_refs[type] = $("#"+type+"_results #"+type+"sTable").dataTable({
                "bPaginate": false,
                "bLengthChange": false,
                "bFilter": false
              });
            }
            else{
              $("#"+type+"_results").hide();
            }
            
          }


          function fetchProducts(ids){
            if(ids.length == 0){
              return;
            }
            ids = ids.join(',');
            var url_sql = "{$wgServer}{$wgScriptPath}/index.php/Special:Solr?type=product&id=" + ids;
                
            $.ajax({
              url: url_sql, 
              success: function(data){ 
                populateTable(data, "product");
              }, 
              async: false 
            }); 
          }
          function fetchProjects(ids){
            if(ids.length == 0){
              return;
            }
            ids = ids.join(',');
            var url_sql = "{$wgServer}{$wgScriptPath}/index.php/Special:Solr?type=project&id=" + ids;
                
            $.ajax({
              url: url_sql, 
              success: function(data){ 
                populateTable(data, "project");
              }, 
              async: false 
            }); 
          }

          function walkJsonTree(key, val) {
            if(key == 'response' && val.numFound > 0){
              docs = val.docs;
              products = new Array();
              projects = new Array();
              milestones = new Array();
              $.each(docs, function(ind, pair){
                $.each(pair, function(type_id, id){
                  if(type_id == 'product_id'){
                    products.push(id);
                  }
                  else if(type_id == 'project_id'){
                    projects.push(id);
                  }
                  else if(type_id == 'milestone_id'){
                    milestones.push(id);
                  }
                  //console.log('TYPE='+type_id +"; ID="+id);
                });
              });
              
              fetchProducts(products);
              //fetchProjects(projects);
              //fetchMilestones(milestones);
            }

          }

					

        </script>
EOF;

      $wgOut->addScript($script);

	    $wgOut->addHTML('
        <form id=searchForm>
          <input type="text" size=70 id="query" />
          <input type=button value=Search name="b_search" id="b_search" />
        </form>
        <br/><br/>
			');

	    $wgOut->addHTML('
        <div id="results">

            <div id="product_results" style="display:none;">
            <h3>Products</h3>
            <table class="indexTable dataTable" cellspacing="1" cellpadding="3" frame="box" rules="all" id="productsTable">
            <thead>
            <tr>
            <th width="20%">Date</th>
            <th>Category/Type</th>
            <th>Title</th>
            <th>Authors</th>
            <th>Projects</th>
            </tr>
            </thead>
            <tbody>
            <tr><td>Empty</td><td>Empty</td><td>Empty</td><td>Empty</td><td>Empty</td></tr>
            </tbody>
            </table>
            </div>

            <div id="project_results" style="display:none;">
            <h3>Projects</h3>
            <table class="indexTable dataTable" cellspacing="1" cellpadding="3" frame="box" rules="all" id="projectsTable">
            <thead>
            <tr>
            <th>Category/Type</th>
            <th>Title</th>
            <th>Authors</th>
            </tr>
            </thead>
            <tbody>
            
            </tbody>
            </table>
            </div>
        </div>
			');

  }

  static function getProducts($ids){
    global $wgServer, $wgScriptPath;

    $products = array();
    foreach($ids as $id){
      $product = Paper::newFromId($id);
      
      $date = $product->getDate();
      $cat = $product->getCategory();
      $type = $product->getType();
      $cat_type = $cat ." / ". $type;
      $title = "<a href='{$wgServer}{$wgScriptPath}/index.php/{$cat}:{$id}'>". $product->getTitle() ."</a>";
      //$status = $product->getStatus();
     
      $auths = $product->getAuthors();
      $authors = array();
      foreach($auths as $auth){
        $authors[] = "<a href='". $auth->getUrl() ."'>". $auth->getNameForForms() ."</a>";
      }
      $authors = implode(', ', $authors);

      $projs = $product->getProjects();
      $projects = array();
      foreach($projs as $proj){
        $projects[] = $proj->getName();
      }
      $projects = implode(', ', $projects);
      //$data = $product->getData();

      $products[] = array('date'=>$date, 'cat_type'=>$cat_type, 'title'=>$title, 'authors'=>$authors, 'projects'=>$projects);
    }
    header('Content-Type: text/json');
    echo json_encode($products);
    exit;
  }

  static function getProjects($ids){
    global $wgServer, $wgScriptPath, $wgUser;
    $me = Person::newFromId($wgUser->getId());

    $projects = array();
    foreach($ids as $id){
      $project = Project::newFromId($id);
      
      $name = $project->getName();
      $type = $project->getType();
      $status = $project->getStatus();
      $type = $type .'/'.$status;
      $description = $project->getDescription();
      //$themes = $project->getThemes();
     
      $title = "<a href='{$wgServer}{$wgScriptPath}/index.php/{$name}:Main'>". $name ."</a>";
      //$status = $product->getStatus();
     
      // $auths = $product->getAuthors();
      // $authors = array();
      // foreach($auths as $auth){
      //   $authors[] = "<a href='". $auth->getUrl() ."'>". $auth->getNameForForms() ."</a>";
      // }
      // $authors = implode(', ', $authors);

      // $projs = $product->getProjects();
      // $projects = array();
      // foreach($projs as $proj){
      //   $projects[] = $proj->getName();
      // }
      // $projects = implode(', ', $projects);
      //$data = $product->getData();

      $projects[] = array('title'=>$title, 'type'=>$type, 'description'=>$authors);
    }
    header('Content-Type: text/json');
    echo json_encode($projects);
    exit;
  }

  static function getMilestones($ids){
    global $wgServer, $wgScriptPath, $wgUser;
    $me = Person::newFromId($wgUser->getId());

    $milestoned = array();
    foreach($ids as $id){
      $milestone = Milestone::newFromId($id);
        
      $project = $milestone->getProject();
      $project_name = $project->getName();
      $project_name = "<a href='{$wgServer}{$wgScriptPath}/index.php/{$project_name}:Main'>". $project_name ."</a>";

      $title = $milestone->getTitle();
      
      $date = $product->getDate();
      $cat = $product->getCategory();
      $type = $product->getType();
      $cat_type = $cat ." / ". $type;
      $title = "<a href='{$wgServer}{$wgScriptPath}/index.php/{$cat}:{$id}'>". $product->getTitle() ."</a>";
      //$status = $product->getStatus();
     
      $auths = $product->getAuthors();
      $authors = array();
      foreach($auths as $auth){
        $authors[] = "<a href='". $auth->getUrl() ."'>". $auth->getNameForForms() ."</a>";
      }
      $authors = implode(', ', $authors);

      $projs = $product->getProjects();
      $projects = array();
      foreach($projs as $proj){
        $projects[] = $proj->getName();
      }
      $projects = implode(', ', $projects);
      //$data = $product->getData();

      $products[] = array('project_name'=>$project_name, 'cat_type'=>$cat_type, 'title'=>$title, 'authors'=>$authors, 'projects'=>$projects);
    }
    header('Content-Type: text/json');
    echo json_encode($products);
    exit;
  }

}

?>
