<?php

use Phinx\Migration\AbstractMigration;

class BlobMd5 extends AbstractMigration
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
        $blobs_table = $this->table('grand_report_blobs');
        $blobs_table->addColumn('md5', 'text', array('after' => 'data'))
                    ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
