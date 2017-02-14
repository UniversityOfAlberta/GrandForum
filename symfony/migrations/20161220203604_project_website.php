<?php

use Phinx\Migration\AbstractMigration;

class ProjectWebsite extends AbstractMigration
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
        $table->addColumn('website', 'string', array('after' => 'long_description', 'limit' => 256))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
