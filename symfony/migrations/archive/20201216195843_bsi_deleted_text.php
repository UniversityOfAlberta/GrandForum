<?php

use Phinx\Migration\AbstractMigration;

class BsiDeletedText extends AbstractMigration
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
        $table = $this->table('grand_bsi_postings');
        $table->addColumn('deleted_text', 'text', array('after' => 'image_caption_fr'))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
