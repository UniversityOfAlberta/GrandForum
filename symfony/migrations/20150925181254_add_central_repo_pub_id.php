<?php

use Phinx\Migration\AbstractMigration;

class AddCentralRepoPubId extends AbstractMigration
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
	$table = $this->table('grand_products');
	$table -> addColumn('central_repo_id', 'integer', array('after'=>'bibtex_id'))
	 	->addIndex(array('central_repo_id'))
		->save();   
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
