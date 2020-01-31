<?php

use Phinx\Migration\AbstractMigration;

class ProfileUrls extends AbstractMigration
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
        $table = $this->table("mw_user");
        $table->addColumn('user_google_scholar', 'string', array('limit' => 1024, 'after' => 'user_linkedin'))
              ->addColumn('user_orcid', 'string', array('limit' => 1024, 'after' => 'user_google_scholar'))
              ->addColumn('user_scopus', 'string', array('limit' => 1024, 'after' => 'user_orcid'))
              ->addColumn('user_researcherid', 'string', array('limit' => 1024, 'after' => 'user_scopus'))
              ->update();  
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
