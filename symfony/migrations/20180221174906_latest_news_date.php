<?php

use Phinx\Migration\AbstractMigration;

class LatestNewsDate extends AbstractMigration
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
    
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("ALTER TABLE `grand_latest_news` CHANGE `date` `date` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00'");
    }
}
