<?php

use Phinx\Migration\AbstractMigration;

class BlobMd5Index extends AbstractMigration
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
        $table = $this->table("grand_report_blobs");
        $table->changeColumn('md5', 'string', array('limit' => 32))
              ->addIndex('md5')
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
