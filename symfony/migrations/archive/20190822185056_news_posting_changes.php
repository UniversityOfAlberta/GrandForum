<?php

use Phinx\Migration\AbstractMigration;

class NewsPostingChanges extends AbstractMigration
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
        $table->removeColumn('translated_id')
              ->removeColumn('posted_date')
              ->addColumn('start_date', 'datetime', array('after' => 'article_link'))
              ->addColumn('end_date', 'datetime', array('after' => 'start_date'))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
