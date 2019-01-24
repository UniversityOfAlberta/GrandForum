<?php

use Phinx\Migration\AbstractMigration;

class EvalSub2 extends AbstractMigration
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
        $table = $this->table("grand_eval");
        $table->addColumn('sub2_id', 'integer', array('after' => 'sub_id'))
              ->addIndex('sub2_id')
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
