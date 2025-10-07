<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class GrandCcv extends AbstractMigration
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
        $ccv_table = $this->table('grand_ccv', array('id' => 'id'));
        if(!$ccv_table->exists()){
            $ccv_table->addColumn('user_id', 'integer')
                      ->addColumn('ccv', 'text', array('limit' => MysqlAdapter::TEXT_LONG))
                      ->addColumn('date', 'timestamp', array('default' => 'CURRENT_TIMESTAMP'))
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
