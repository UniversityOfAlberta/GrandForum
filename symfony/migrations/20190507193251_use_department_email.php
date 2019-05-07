<?php

use Phinx\Migration\AbstractMigration;

class UseDepartmentEmail extends AbstractMigration
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
        $table->addColumn('use_generic', 'boolean', array('after' => 'email', 'default' => 0))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
