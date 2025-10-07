<?php

use Phinx\Migration\AbstractMigration;

class TopProductType extends AbstractMigration
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
        $table = $this->table('grand_top_products');
        $table->addColumn('product_type', 'string', array('after' => 'obj_id', 'limit' => 32))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
