<?php

use Phinx\Migration\AbstractMigration;

class PostingPreview extends AbstractMigration
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
        $table = $this->table('grand_event_postings', array('id' => 'id'));
        $table->addColumn('preview_code', 'string', array('limit' => 32, 'after' => 'image_caption_fr'))
              ->update();
              
        $table = $this->table('grand_news_postings', array('id' => 'id'));
        $table->addColumn('preview_code', 'string', array('limit' => 32, 'after' => 'image_caption_fr'))
              ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
