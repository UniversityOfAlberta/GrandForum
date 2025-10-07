<?php

use Phinx\Migration\AbstractMigration;

class ThemeWiki extends AbstractMigration
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
        $table = $this->table('grand_themes');
        $table->addColumn('resources', 'text', array('after' => 'description'))
              ->addColumn('wiki', 'text', array('after' => 'resources'))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
