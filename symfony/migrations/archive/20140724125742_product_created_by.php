<?php

use Phinx\Migration\AbstractMigration;

class ProductCreatedBy extends AbstractMigration
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
        $table = $this->table('grand_products');
        if(!$table->hasColumn('created_by')){
            $table->addColumn('created_by', 'integer', array('after' => 'access_id'))
                  ->addIndex(array('created_by'))
                  ->save();
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
