<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ProductAuthorType extends AbstractMigration
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
        $table = $this->table('grand_product_authors');
        $table->addColumn('type', 'string', array('after' => 'product_id', 'limit' => 8))
              ->addIndex('type')
              ->save();
    }
}
