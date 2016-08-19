<?php

use Phinx\Migration\AbstractMigration;

class FixVarLengthFileTitle extends AbstractMigration
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
        $table->changeColumn('title', 'string', array('limit' => 255))
              ->save();    
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
