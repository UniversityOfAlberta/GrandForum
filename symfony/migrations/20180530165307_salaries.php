<?php

use Phinx\Migration\AbstractMigration;

class Salaries extends AbstractMigration
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
        $table = $this->table('grand_salary_scales', array('id' => false, 'primary_key' => array('year')));
        $table->addColumn('year', 'string', array('limit' => 4))
              ->addColumn('min_salary_assoc', 'integer')
              ->addColumn('min_salary_assist', 'integer')
              ->addColumn('min_salary_prof', 'integer')
              ->addColumn('min_salary_fso2', 'integer')
              ->addColumn('min_salary_fso3', 'integer')
              ->addColumn('min_salary_fso4', 'integer')
              ->addColumn('max_salary_assoc', 'integer')
              ->addColumn('max_salary_assist', 'integer')
              ->addColumn('max_salary_prof', 'integer')
              ->addColumn('max_salary_fso2', 'integer')
              ->addColumn('max_salary_fso3', 'integer')
              ->addColumn('max_salary_fso4', 'integer')
              ->addColumn('increment_assoc', 'integer')
              ->addColumn('increment_assist', 'integer')
              ->addColumn('increment_prof', 'integer')
              ->addColumn('increment_fso2', 'integer')
              ->addColumn('increment_fso3', 'integer')
              ->addColumn('increment_fso4', 'integer')
              ->create();
              
        $table = $this->table('grand_user_salaries');
        $table->addColumn('user_id', 'integer')
              ->addColumn('year', 'string', array('limit' => 4))
              ->addColumn('salary', 'integer')
              ->addIndex('user_id')
              ->addIndex('year')
              ->create();
    }
}
