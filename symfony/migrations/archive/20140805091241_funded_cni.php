<?php

use Phinx\Migration\AbstractMigration;

class FundedCni extends AbstractMigration
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
        $cni_table = $this->table('grand_funded_cni', array('id' => false, 'primary_key' => array('user_id', 'year')));
        if(!$cni_table->exists()){
            $cni_table->addColumn('user_id', 'integer', array('default' => 0, 'null' => false))
                      ->addColumn('year', 'integer', array('default' => 0, 'null' => false))
                      ->addIndex(array('user_id'))
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
