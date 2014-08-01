<?php

use Phinx\Migration\AbstractMigration;

class ProductBibtexId extends AbstractMigration
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
        if(!$table->hasColumn('bibtex_id')){
            $table->addColumn('bibtex_id', 'string', array('limit' => '256',
                                                           'after' => 'ccv_id'))
                  ->addIndex(array('bibtex_id'))
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
