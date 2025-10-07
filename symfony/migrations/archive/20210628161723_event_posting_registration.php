<?php

use Phinx\Migration\AbstractMigration;

class EventPostingRegistration extends AbstractMigration
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
             ->addColumn('enable_registration', 'boolean', array('after' => 'image_caption_fr'))
             ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
