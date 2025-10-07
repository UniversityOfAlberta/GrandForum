<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class BibliographyTitle extends AbstractMigration
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
        $table = $this->table("grand_bibliography");
        $table->addColumn('title', 'string', array('limit' => 512, 'after' => 'id'))
              ->addColumn('description', 'text', array('limit' => MysqlAdapter::TEXT_LONG, 'after' => 'title'))
              ->addIndex('title')
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
