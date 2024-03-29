<?php

use Phinx\Migration\AbstractMigration;

class DropWeasleWords extends AbstractMigration
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
        $products = $this->table('grand_products');
        if($products->hasColumn('weasel_words')){
            $products->removeColumn('weasel_words')
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
