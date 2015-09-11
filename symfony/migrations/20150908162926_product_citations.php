<?php

use Phinx\Migration\AbstractMigration;

class ProductCitations extends AbstractMigration
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
      $table = $this->table("grand_product_citations",array("id" => "id"));
      if(!$table->exists()){
            $table->addColumn('product_id', 'integer')
                  ->addColumn('type', 'string', array("limit"=>64))
		  ->addColumn('citation_count', 'integer')
                  ->addIndex(array('product_id'))
                  ->addIndex(array('type'))
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
