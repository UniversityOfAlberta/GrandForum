<?php


use Phinx\Migration\AbstractMigration;

class PersonDemographics extends AbstractMigration
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
	$table = $this->table("mw_user");
	$table->addColumn('user_age', 'string', array('limit' => 32, 'after' => 'user_gender'))
	->addColumn('user_indigenous_status', 'string', array('limit' => 32, 'after' => 'user_age'))
	->addColumn('user_minority_status', 'string', array('limit' => 32, 'after' => 'user_indigenous_status'))
	->addColumn('user_disability_status', 'string', array('limit' => 32, 'after' => 'user_minority_status'))
	->update();    
    }
}
