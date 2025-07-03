<?php

use Phinx\Migration\AbstractMigration;

class ContributionEdits extends AbstractMigration
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
        $table = $this->table('grand_contribution_edits', array('id' => false, 'primary_key' => array('id', 'user_id')));
        $table->addColumn('id', 'integer', array('default' => 0, 'null' => false))
              ->addColumn('user_id', 'integer', array('default' => 0, 'null' => false))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
