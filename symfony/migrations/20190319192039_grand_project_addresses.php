<?php

use Phinx\Migration\AbstractMigration;

class GrandProjectAddresses extends AbstractMigration
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
        $addr_table = $this->table('grand_project_addresses', array('id' => 'id'));
        if(!$addr_table->exists()){
            $addr_table->addColumn('proj_id', 'integer')
                       ->addColumn('type', 'string', array('limit' => 64))
                       ->addColumn('line1', 'string', array('limit' => 256))
                       ->addColumn('line2', 'string', array('limit' => 256))
                       ->addColumn('line3', 'string', array('limit' => 256))
                       ->addColumn('line4', 'string', array('limit' => 256))
                       ->addColumn('line5', 'string', array('limit' => 256))
                       ->addColumn('city', 'string', array('limit' => 256))
                       ->addColumn('code', 'string', array('limit' => 16))
                       ->addColumn('country', 'string', array('limit' => 64))
                       ->addColumn('province', 'string', array('limit' => 64))
                       ->addColumn('start_date', 'timestamp', array('default' => '0000-00-00 00:00:00'))
                       ->addColumn('end_date', 'timestamp', array('default' => '0000-00-00 00:00:00'))
                       ->addColumn('primary_indicator', 'boolean')
                       ->addIndex(array('proj_id'))
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
