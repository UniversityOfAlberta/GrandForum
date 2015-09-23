<?php

use Phinx\Migration\AbstractMigration;

class GrandUserAcmMetrics extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */
    
    /**
     * Migrate Up.
     */
    public function up()
    {
	$table = $this->table("grand_user_metrics", array("id" => "id"));
	if(!$table->exists()){
	    $table->addColumn('user_id', 'integer')
		  ->addColumn('acm_start_date','timestamp',array('default'=>'0000-00-00 00:00:00'))
	    	  ->addColumn('acm_end_date', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
	    	  ->addColumn('acm_publication_count','integer')
		  ->addColumn('acm_avg_citations_per_article','float')
		  ->addColumn('acm_citation_count', 'integer')
		  ->addColumn('acm_avg_download_per_article', 'float')
		  ->addColumn('acm_available_download', 'integer')
		  ->addColumn('acm_download_cumulative','integer')
		  ->addColumn('acm_download_6_weeks', 'integer')
		  ->addColumn('acm_download_1_year', 'integer')
		  ->addColumn('sciverse_coauthor_count', 'integer')
		  ->addColumn('sciverse_hindex', 'integer')
		  ->addColumn('sciverse_citation_count', 'integer')
		  ->addColumn('sciverse_cited_by_count', 'integer')
		  ->addColumn('sciverse_doc_count', 'integer')
		  ->addColumn('change_date', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
		  ->create();
	    
	}
    
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
