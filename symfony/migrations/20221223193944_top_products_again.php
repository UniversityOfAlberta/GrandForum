<?php

use Phinx\Migration\AbstractMigration;

class TopProductsAgain extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $top_table = $this->table('grand_top_products', array('id' => 'id'));
        if(!$top_table->exists()){
            $top_table->addColumn('type', 'string', array('limit' => 32))
                      ->addColumn('obj_id', 'integer')
                      ->addColumn('product_id', 'integer')
                      ->addColumn('changed', 'timestamp')
                      ->addIndex('obj_id')
                      ->addIndex('product_id')
                      ->create();
        }
    }
}
