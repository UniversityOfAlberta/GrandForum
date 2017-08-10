<?php

use Phinx\Migration\AbstractMigration;

class GrantAwardColumns extends AbstractMigration
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
        $table = $this->table('grand_new_grants');
        $table->removeColumn('organization')
              ->removeColumn('area_of_application_code')
              ->removeColumn('research_subject_code')
              ->removeColumn('committee_code')
              ->save();
        $table = $this->table('grand_new_grant_partner');
        $table->removeColumn('part_organization_id')
              ->changeColumn('fiscal_year', 'integer')
              ->save();
    }
}
