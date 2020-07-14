<?php

use Phinx\Migration\AbstractMigration;

class PostingCaptionLengths extends AbstractMigration
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
        $table = $this->table("grand_news_postings");
        $table->changeColumn('image_caption', 'string', array('limit' => 500))
              ->changeColumn('image_caption_fr', 'string', array('limit' => 500))
              ->update();
              
        $table = $this->table("grand_event_postings");
        $table->changeColumn('image_caption', 'string', array('limit' => 500))
              ->changeColumn('image_caption_fr', 'string', array('limit' => 500))
              ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
