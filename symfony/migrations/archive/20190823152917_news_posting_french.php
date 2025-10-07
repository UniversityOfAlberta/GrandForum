<?php

use Phinx\Migration\AbstractMigration;

class NewsPostingFrench extends AbstractMigration
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
        $table = $this->table('grand_news_postings');
        $table->addColumn('title_fr', 'string', array('limit' => 300, 'after' => 'title'))
              ->addColumn('summary_fr', 'text', array('after' => 'summary'))
              ->addColumn('image_caption_fr', 'string', array('limit' => 128, 'after' => 'image_caption'))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
