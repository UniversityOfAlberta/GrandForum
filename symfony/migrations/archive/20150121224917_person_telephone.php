<?php

use Phinx\Migration\AbstractMigration;

class PersonTelephone extends AbstractMigration
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
        $phone_table = $this->table('grand_user_telephone', array('id' => 'id'));
        if(!$phone_table->exists()){
            $phone_table->addColumn('user_id', 'integer')
                        ->addColumn('type', 'string', array('limit' => 64))
                        ->addColumn('country_code', 'string', array('limit' => 32))
                        ->addColumn('area_code', 'string', array('limit' => 32))
                        ->addColumn('number', 'string', array('limit' => 32))
                        ->addColumn('extension', 'string', array('limit' => 32))
                        ->addColumn('start_date', 'timestamp', array('default' => '0000-00-00 00:00:00'))
                        ->addColumn('end_date', 'timestamp', array('default' => '0000-00-00 00:00:00'))
                        ->addColumn('primary_indicator', 'boolean')
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
