<?php

use Phinx\Migration\AbstractMigration;

class GoogleScholarCitations extends AbstractMigration
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
	$table = $this->table("grand_gs_citations", array("id" => false, 'primary_key' => array('user_id', 'year')));
	if(!$table->exists()){
	    $table->addColumn('user_id', 'integer')
	          ->addColumn('year', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
	    	  ->addColumn('count', 'integer')
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
