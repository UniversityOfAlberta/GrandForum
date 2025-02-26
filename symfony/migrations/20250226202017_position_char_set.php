<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PositionCharSet extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $this->execute("ALTER TABLE `grand_positions` CHANGE `position` `position` VARCHAR(256) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL; ");
        $this->execute("ALTER TABLE `grand_positions` CONVERT TO CHARACTER SET utf8;");
    }
}
