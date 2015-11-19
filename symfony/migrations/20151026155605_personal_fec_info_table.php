<?php

use Phinx\Migration\AbstractMigration;

class PersonalFecInfoTable extends AbstractMigration
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
        $table = $this->table('grand_personal_fec_info');
	if(!$table->exists()){
	     $table->addColumn('user_id', 'integer')
		   ->addColumn('date_of_phd', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
		   ->addColumn('date_of_appointment', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
		   ->addColumn('date_assistant', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
		   ->addColumn('date_associate', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
		   ->addColumn('date_professor', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
		   ->addColumn('date_tenure', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
		   ->addColumn('date_retirement', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
		   ->addColumn('date_last_degree', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
		   ->addColumn('last_degree', 'string')
		   ->addColumn('publication_history_refereed', 'integer')
		   ->addColumn('publication_history_books', 'integer')
		   ->addColumn('publication_history_patents', 'integer')
		   ->addColumn('date_fso2', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
		   ->addColumn('date_fso3', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
		   ->addColumn('date_fso4', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
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
