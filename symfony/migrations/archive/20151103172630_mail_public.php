<?php

use Phinx\Migration\AbstractMigration;

class MailPublic extends AbstractMigration
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
        $this->execute("UPDATE `mw_an_extranamespaces`
                        SET `public` = 1
                        WHERE `nsName` = 'Mail'");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
