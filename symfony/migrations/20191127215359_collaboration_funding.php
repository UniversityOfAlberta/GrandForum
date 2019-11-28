<?php

use Phinx\Migration\AbstractMigration;

class CollaborationFunding extends AbstractMigration
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
        $table = $this->table('grand_collaborations');
        $table->renameColumn('funding', 'cash')
              ->addColumn('inkind', 'decimal', array('scale'=>2, 'precision'=>65, 'after' => 'cash'))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
