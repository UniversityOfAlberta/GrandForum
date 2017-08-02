<?php

use Phinx\Migration\AbstractMigration;

class AddCoapplicantsAward extends AbstractMigration
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
      $table = $this->table('grand_new_grants');
        $table -> addColumn('coapplicants', 'text')
               -> save();
    
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
