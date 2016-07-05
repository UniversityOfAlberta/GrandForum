<?php

use Phinx\Migration\AbstractMigration;

class FixFilesWithLinks extends AbstractMigration
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
        $table = $this->table('mw_an_upload_permissions');
        $table->addColumn('url', 'string', array('limit'=>1024))
	      ->addColumn('keywords', 'text')
	      ->addColumn('title', 'string', array('limit'=>64))
              ->save();    
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
