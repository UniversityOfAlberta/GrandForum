<?php

use Phinx\Migration\AbstractMigration;

class ProductFecInfo extends AbstractMigration
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
	$table -> addColumn('acceptance_date', 'date', array('after'=> 'date'))
	       -> addColumn('ratio', 'integer', array('after'=>'acceptance_date'))
	       -> addColumn('acceptance_ratio_numerator', 'integer', array('after'=>'ratio'))
               -> addColumn('acceptance_ratio_denominator', 'integer', array('after'=>'acceptance_ratio_numerator'))
	       -> save();       
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
