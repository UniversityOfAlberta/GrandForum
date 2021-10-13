<?php

use Phinx\Migration\AbstractMigration;

class SubItemLength extends AbstractMigration
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
        $table = $this->table('grand_report_blobs');
        $table->changeColumn('rp_subitem', 'string', array('limit' => 256))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
