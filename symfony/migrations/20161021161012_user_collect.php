<?php

use Phinx\Migration\AbstractMigration;

class UserCollect extends AbstractMigration
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
        $table = $this->table('mw_user');
        $table->addColumn('confidential', 'boolean', array('after' => 'candidate'))
              ->addColumn('collect_demo', 'boolean', array('after' => 'candidate'))
              ->addColumn('collect_comments', 'boolean', array('after' => 'candidate'))
              ->save(); 
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
