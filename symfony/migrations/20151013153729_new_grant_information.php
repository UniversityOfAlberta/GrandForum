<?php

use Phinx\Migration\AbstractMigration;

class NewGrantInformation extends AbstractMigration
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
	$table = $this->table('grand_contributions_new', array("id"=>"id"));
	if(!$table->exists()){
	     $table->addColumn('project_id', 'string')
	           ->addColumn('user_id', 'integer')
		   ->addColumn('title', 'string')
		   ->addColumn('description', 'text')
		   ->addColumn('start_date', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
		   ->addColumn('end_date', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
		   ->addColumn('user_role', 'text')
		   ->addColumn('team', 'text')
		   ->addColumn('keywords', 'text')
		   ->addColumn('request', 'text')
		   ->addColumn('available_funds_before', 'float')
		   ->addColumn('available_funds_after', 'float')
		   ->addColumn('total_award', 'float')
		   ->addColumn('speed_code', 'text')
		   ->addColumn('overexpenditure_status', 'string')
		   ->addColumn('sponsor', 'text')
		   ->addColumn('sponsor_program', 'text')
		   ->addColumn('project_type', 'string')
		   ->addColumn('project_status', 'string')
		   ->addColumn('percent_spent', 'float')
		   ->addColumn('award_num', 'integer')
		   ->addColumn('change_date', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
		   ->addIndex(array('user_id'))
		   ->addIndex(array('project_id'))
		   ->addIndex(array('award_num'))
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
