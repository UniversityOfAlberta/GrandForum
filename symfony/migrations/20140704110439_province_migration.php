<?php

use Phinx\Migration\AbstractMigration;

class ProvinceMigration extends AbstractMigration
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
        $uni_table = $this->table('grand_universities');
        if($uni_table->hasColumn('color')){
            $uni_table->removeColumn('color')
                      ->save();
        }
        
        $prov_table = $this->table('grand_provinces', array('id' => true, 'primary_key' => array('id')));
        if(!$prov_table->exists()){
            $prov_table->addColumn('province', 'string', array('limit' => 256))
                       ->addColumn('color', 'string', array('limit' => 16))
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
