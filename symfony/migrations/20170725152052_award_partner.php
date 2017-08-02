<?php

use Phinx\Migration\AbstractMigration;

class AwardPartner extends AbstractMigration
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
           $table = $this->table('grand_new_grant_partner', array("id"=>"id"));
        if(!$table->exists()){
            $table->addColumn('cle', 'integer')
                  ->addColumn('part_organization_id', 'string', array('limit' => 256))
                  ->addColumn('part_institution', 'string', array('limit' => 256))
                  ->addColumn('province', 'string', array('limit' => 256))
                  ->addColumn('country', 'string', array('limit'=>256))
                  ->addColumn('committee_name', 'string', array('limit' => 256))
                  ->addColumn('fiscal_year', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                  ->addColumn('org_type', 'string', array('limit' => 256))
                  ->addIndex(array('cle'))
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
