<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class Bibliography extends AbstractMigration
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
        $table = $this->table("grand_bibliography", array("id" => "id"));
        if(!$table->exists()){
            $table->addColumn('person_id', 'integer')
                  ->addColumn('products', 'text', array('limit' => MysqlAdapter::TEXT_LONG))
                  ->addIndex('person_id')
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
