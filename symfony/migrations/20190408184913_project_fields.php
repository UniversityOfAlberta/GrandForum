<?php

use Phinx\Migration\AbstractMigration;

class ProjectFields extends AbstractMigration
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
        $table->addColumn('short_name', 'string', array('after' => 'full_name', 'limit' => 128))
              ->addColumn('dept_website', 'string', array('after' => 'website', 'limit' => 256))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
