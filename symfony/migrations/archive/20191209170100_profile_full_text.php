<?php

use Phinx\Migration\AbstractMigration;

class ProfileFullText extends AbstractMigration
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
        $this->execute("ALTER TABLE `mw_user` ENGINE = MYISAM");
        $this->execute("ALTER TABLE `mw_user` CONVERT TO CHARACTER SET utf8");
        $this->execute("ALTER TABLE `mw_user` MODIFY `user_public_profile` TEXT");
        $this->execute("ALTER TABLE `mw_user` MODIFY `user_private_profile` TEXT");
        $this->execute("ALTER TABLE `mw_user` ADD FULLTEXT (`user_public_profile`)");
        $this->execute("ALTER TABLE `mw_user` ADD FULLTEXT (`user_private_profile`)");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
