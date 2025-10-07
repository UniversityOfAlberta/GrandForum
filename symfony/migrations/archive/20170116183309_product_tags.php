<?php

use Phinx\Migration\AbstractMigration;

class ProductTags extends AbstractMigration
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
        $table = $this->table("grand_product_tags", array("id" => "id"));
        if(!$table->exists()){
            $table->addColumn('tag', 'string', array('limit' => 128))
                  ->addColumn('product_id', 'integer')
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
