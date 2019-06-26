<?php

use Phinx\Migration\AbstractMigration;

class FacultyList extends AbstractMigration
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
        $table = $this->table('grand_project_descriptions');
        $table->addColumn('faculty_list', 'string', array('limit' => '32', 'after' => 'member_status', 'default' => ''))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
