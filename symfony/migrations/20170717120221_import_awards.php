<?php

use Phinx\Migration\AbstractMigration;

class ImportAwards extends AbstractMigration
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
        $table = $this->table('grand_new_grants', array("id"=>"id"));
        if(!$table->exists()){
            $table->addColumn('user_id','integer')
		  ->addColumn('cle', 'integer')
                  ->addColumn('department', 'string', array('limit' => 256))
		  ->addColumn('organization', 'integer')
		  ->addColumn('institution', 'string', array('limit' => 256))
		  ->addColumn('province', 'string', array('limit' => 256))
		  ->addColumn('country', 'string', array('limit' => 256))
		  ->addColumn('fiscal_year', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
	 	  ->addColumn('competition_year', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
		  ->addColumn('amount', 'integer')
		  ->addColumn('program_id', 'string', array('limit' => 256))
		  ->addColumn('program_name', 'string', array('limit' => 256))
		  ->addColumn('group', 'string', array('limit' => 256))
		  ->addColumn('committee_code', 'integer')
		  ->addColumn('committee_name', 'string', array('limit' => 256))
		  ->addColumn('area_of_application_code', 'integer')
		  ->addColumn('area_of_application_group', 'string', array('limit' => 256))
		  ->addColumn('area_of_application', 'string', array('limit' => 256))
		  ->addColumn('research_subject_code', 'integer')
		  ->addColumn('research_subject_group', 'string', array('limit' => 256))
		  ->addColumn('installment', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
		  ->addColumn('partie', 'integer')
		  ->addColumn('nb_partie', 'integer')
		  ->addColumn('application_title', 'string', array('limit' => 256))
		  ->addColumn('keyword', 'string', array('limit' => 256))
		  ->addColumn('application_summary', 'string', array('limit' => 256))
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
