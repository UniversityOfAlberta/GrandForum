<?php

use Phinx\Migration\AbstractMigration;

class ThemeCoordinator extends AbstractMigration
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
        $table = $this->table('grand_theme_leaders');
        $table->addColumn('coordinator', 'string', array('after' => 'co_lead', 'limit' => 16))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
