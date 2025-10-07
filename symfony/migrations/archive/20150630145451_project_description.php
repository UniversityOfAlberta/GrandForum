<?php

use Phinx\Migration\AbstractMigration;

class ProjectDescription extends AbstractMigration
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
        $table->removeColumn('problem')
              ->removeColumn('solution')
              ->addColumn('long_description', 'text', array('after' => 'description'))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
