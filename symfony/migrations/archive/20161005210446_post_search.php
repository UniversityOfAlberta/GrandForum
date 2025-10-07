<?php

use Phinx\Migration\AbstractMigration;

class PostSearch extends AbstractMigration
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
        $table = $this->table('grand_posts');
        $table->addColumn('search', 'text', array('after' => 'message'))
              ->save();
        $this->execute("ALTER TABLE grand_posts DROP INDEX message");
        $this->execute("ALTER TABLE `grand_posts` ADD FULLTEXT (`search`)");
        $this->execute("UPDATE `grand_posts` SET `search` = `message`");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
