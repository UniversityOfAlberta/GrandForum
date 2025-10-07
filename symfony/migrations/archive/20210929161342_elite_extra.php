<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class EliteExtra extends AbstractMigration
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
        $this->table('grand_elite_postings')
             ->addColumn('extra', 'text', array('limit' => MysqlAdapter::TEXT_MEDIUM, 'after' => 'image_caption_fr'))
             ->addColumn('type', 'string', array('limit' => 32, 'after' => 'image_caption_fr'))
             ->removeColumn('company_name')
             ->removeColumn('company_profile')
             ->removeColumn('reports_to')
             ->removeColumn('based_at')
             ->removeColumn('contact')
             ->removeColumn('email')
             ->removeColumn('phone')
             ->removeColumn('training')
             ->removeColumn('responsibilities')
             ->removeColumn('qualifications')
             ->removeColumn('skills')
             ->removeColumn('level')
             ->removeColumn('positions')
             ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
