<?php

use Phinx\Migration\AbstractMigration;

class TopProducts extends AbstractMigration
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
        $top_table = $this->table('grand_top_products', array('id' => 'id'));
        if(!$top_table->exists()){
            $top_table->addColumn('type', 'string', array('limit' => 32))
                      ->addColumn('obj_id', 'integer')
                      ->addColumn('product_id', 'integer')
                      ->addColumn('changed', 'timestamp')
                      ->addIndex('type')
                      ->addIndex('obj_id')
                      ->addIndex('product_id')
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
