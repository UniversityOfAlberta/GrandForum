<?php

use Phinx\Migration\AbstractMigration;

class EventPostingMaterials extends AbstractMigration
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
        $this->table('grand_event_postings')
             ->addColumn('enable_materials', 'boolean', array('after' => 'enable_registration'))
             ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
