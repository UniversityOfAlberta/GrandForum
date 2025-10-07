<?php

use Phinx\Migration\AbstractMigration;

class EvalIndex extends AbstractMigration
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
        $this->execute('ALTER TABLE `grand_eval` DROP PRIMARY KEY ,
                        ADD PRIMARY KEY ( `user_id` , `sub_id` , `sub2_id` , `type` , `year` )');
        $table = $this->table('grand_eval');
        $table->removeIndex('sub2_id')
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
