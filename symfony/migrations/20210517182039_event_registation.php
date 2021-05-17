<?php

use Phinx\Migration\AbstractMigration;

class EventRegistation extends AbstractMigration
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
        $table = $this->table('grand_event_registration', array('id' => 'id'));
        $table->addColumn('event_id', 'integer')
              ->addColumn('email', 'string', array('limit' => 128))
              ->addColumn('name', 'string', array('limit' => 128))
              ->addColumn('role', 'string', array('limit' => 64))
              ->addColumn('receive_information', 'boolean')
              ->addColumn('join_newsletter', 'boolean')
              ->addColumn('create_profile', 'boolean')
              ->addColumn('similar_events', 'boolean')
              ->addIndex('event_id')
              ->create();
    }
    
    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
