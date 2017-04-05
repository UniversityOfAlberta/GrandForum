<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class LatestNews extends AbstractMigration
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
        $table = $this->table("grand_latest_news", array("id" => "id"));
        $table->addColumn('date', 'timestamp')
              ->addColumn('en', 'binary')
              ->addColumn('fr', 'binary')
              ->create();
        $this->execute("ALTER TABLE `grand_latest_news` CHANGE `en` `en` MEDIUMBLOB NOT NULL");
        $this->execute("ALTER TABLE `grand_latest_news` CHANGE `fr` `fr` MEDIUMBLOB NOT NULL");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
