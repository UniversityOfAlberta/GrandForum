<?php

use Phinx\Migration\AbstractMigration;

class BlobType extends AbstractMigration
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
        $table->changeColumn('rp_type', 'string', array('limit' => 64))
              ->changeColumn('rp_section', 'string', array('limit' => 64))
              ->changeColumn('rp_item', 'string', array('limit' => 64))
              ->changeColumn('rp_subitem', 'string', array('limit' => 64))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
