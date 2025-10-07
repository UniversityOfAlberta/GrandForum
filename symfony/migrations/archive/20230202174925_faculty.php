<?php

use Phinx\Migration\AbstractMigration;

class Faculty extends AbstractMigration
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
        $this->table('grand_user_university')
             ->addColumn('faculty', 'string', array('after' => 'university_id', 'limit' => 255))
             ->save();
             
        $this->table('grand_user_request')
             ->addColumn('faculty', 'string', array('after' => 'university', 'limit' => 256))
             ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
