<?php

use Phinx\Migration\AbstractMigration;

class AddNewProfileFields extends AbstractMigration
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
        $table = $this->table('grand_personal_caps_info', array("id"=>"id"));
	if(!$table->exists()){
	     $table->addColumn('user_id', 'integer')
		   ->addColumn('postal_code', 'string')
		   ->addColumn('city', 'string')
		   ->addColumn('province', 'integer')
		   ->addColumn('specialty', 'string')
		   ->addColumn('years_in_practice', 'integer')
		   ->addColumn('prior_abortion_service', 'integer')
		   ->addColumn('accept_referrals', 'integer')
		   ->addIndex('user_id')
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
