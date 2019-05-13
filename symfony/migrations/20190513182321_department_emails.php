<?php

use Phinx\Migration\AbstractMigration;

class DepartmentEmails extends AbstractMigration
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
        $table->addColumn('admin_email', 'string', array('after' => 'use_generic', 'limit' => 64))
              ->addColumn('admin_use_generic', 'boolean', array('after' => 'admin_email', 'default' => 0))
              ->addColumn('tech_email', 'string', array('after' => 'admin_use_generic', 'limit' => 64))
              ->addColumn('tech_use_generic', 'boolean', array('after' => 'tech_email', 'default' => 0))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
