<?php

use Phinx\Migration\AbstractMigration;

class ProjectLeaderRole extends AbstractMigration
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
        exec("php ../maintenance/copyLeaders.php", $output);
        foreach($output as $out){
            echo $out."\n";
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
