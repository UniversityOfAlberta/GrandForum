<?php

use Phinx\Migration\AbstractMigration;

class ConferenceAttendance extends AbstractMigration
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
        $conf_table = $this->table('grand_conference_attendance', array('id' => true, 'primary_key' => array('id')));
        if(!$conf_table->exists()){
            $conf_table->addColumn('person_id', 'integer')
                       ->addColumn('date', 'date')
                       ->addColumn('conference', 'string', array('limit' => 256))
                       ->addColumn('location', 'string', array('limit' => 256))
                       ->addColumn('title', 'string', array('limit' => 256))
                       ->addIndex(array('person_id'))
                       ->addIndex(array('date'))
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
