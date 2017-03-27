<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class Grants extends AbstractMigration
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
        $table = $this->table('grand_grants', array("id"=>"id"));
        if(!$table->exists()){
            $table->addColumn('user_id','integer')
                  ->addColumn('project_id', 'string', array('limit' => 256))
                  ->addColumn('sponsor', 'string', array('limit', 1024))
                  ->addColumn('total','float')
                  ->addColumn('funds_before','float')
                  ->addColumn('funds_after','float')
                  ->addColumn('speed_code', 'string', array('limit' => 32))
                  ->addColumn('title','string', array('limit' => 1024))
                  ->addColumn('description', 'string', array('limit' => MysqlAdapter::TEXT_LONG))
                  ->addColumn('request', 'string', array('limit' => MysqlAdapter::TEXT_LONG))
                  ->addColumn('start_date', 'timestamp', array('default' => '0000-00-00 00:00:00'))
                  ->addColumn('end_date', 'timestamp', array('default' => '0000-00-00 00:00:00'))
                  ->addIndex(array('user_id'))
                  ->addIndex(array('project_id'))
                  ->create();
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
