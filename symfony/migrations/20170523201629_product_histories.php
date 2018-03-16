<?php

use Phinx\Migration\AbstractMigration;

class ProductHistories extends AbstractMigration
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
        $table = $this->table('grand_product_histories', array("id"=>"id"));
        if(!$table->exists()){
            $table->addColumn('user_id','integer')
                  ->addColumn('year','integer')
                  ->addColumn('type','string', array('limit' => 32))
                  ->addColumn('value','string', array('limit' => 32))
                  ->addColumn('created', 'timestamp', array('default' => 'CURRENT_TIMESTAMP'))
                  ->addColumn('updated', 'timestamp', array('default' => '0000-00-00 00:00:00'))
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
