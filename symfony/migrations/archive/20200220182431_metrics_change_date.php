<?php

use Phinx\Migration\AbstractMigration;

class MetricsChangeDate extends AbstractMigration
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
        $table = $this->table('grand_user_metrics');
        $table->changeColumn('change_date', 'timestamp', array('update' => 'CURRENT_TIMESTAMP'))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
