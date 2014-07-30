<?php

use Phinx\Migration\AbstractMigration;

class ProductCcvId extends AbstractMigration
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
        if(!$table->hasColumn('ccv_id')){
            $table->addColumn('ccv_id', 'string', array('limit' => '256',
                                                        'after' => 'created_by'))
                  ->addIndex(array('ccv_id'))
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
