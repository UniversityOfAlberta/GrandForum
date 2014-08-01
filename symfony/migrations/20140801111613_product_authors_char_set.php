<?php

use Phinx\Migration\AbstractMigration;

class ProductAuthorsCharSet extends AbstractMigration
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
        $this->execute("ALTER TABLE `grand_product_authors` CHANGE `author` `author` VARCHAR( 128 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
        $this->execute("ALTER TABLE `grand_product_authors` CONVERT TO CHARACTER SET utf8;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
