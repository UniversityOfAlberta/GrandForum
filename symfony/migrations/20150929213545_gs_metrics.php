<?php

use Phinx\Migration\AbstractMigration;

class GsMetrics extends AbstractMigration
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
	$table = $this->table('grand_user_gsmetrics', array("id"=>"id"));
	if(!$table->exists()){
	    $table->addColumn('user_id','integer')
		  ->addColumn('start_date', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
		  ->addColumn('hindex_5_years', 'float')
	          ->addColumn('i10_index_5_years', 'float')
	          ->addColumn('hindex','float')
	      	  ->addColumn('i10_index', 'float')
	          ->addColumn('change_date', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
		  ->addIndex(array('user_id'))
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
