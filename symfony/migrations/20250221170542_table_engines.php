<?php

use Phinx\Migration\AbstractMigration;

class TableEngines extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $this->execute("ALTER TABLE `grand_courses` ENGINE = InnoDB");
        $this->execute("ALTER TABLE `grand_movedOn` ENGINE = InnoDB");
        $this->execute("ALTER TABLE `grand_names_cache` DROP INDEX name_2");
        $this->execute("ALTER TABLE `grand_names_cache` ENGINE = InnoDB");
        $this->execute("ALTER TABLE `grand_products` ENGINE = InnoDB");
        $this->execute("ALTER TABLE `grand_relations` ENGINE = InnoDB");
        $this->execute("ALTER TABLE `grand_roles` ENGINE = InnoDB");
        $this->execute("ALTER TABLE `grand_theses` ENGINE = InnoDB");
        $this->execute("ALTER TABLE `grand_universities` ENGINE = InnoDB");
        $this->execute("ALTER TABLE `grand_user_request` ENGINE = InnoDB");
        $this->execute("ALTER TABLE `grand_user_university` ENGINE = InnoDB");
        $this->execute("ALTER TABLE `mw_searchindex` DROP INDEX si_title");
        $this->execute("ALTER TABLE `mw_searchindex` DROP INDEX si_text");
        $this->execute("ALTER TABLE `mw_searchindex` ENGINE = InnoDB");
    }
}
