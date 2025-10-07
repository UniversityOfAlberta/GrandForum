<?php


use Phinx\Migration\AbstractMigration;

class Collaborations extends AbstractMigration
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
        $collabs = $this->table("grand_collaborations", array("id" => "id"));
        $projects = $this->table("grand_collaboration_projects", array("id" => false, 'primary_key' => array('collaboration_id', 'project_id')));

        $projects->addColumn('collaboration_id', 'integer', array('default' => 0, 'null' => false))
            ->addColumn('project_id', 'integer', array('default' => 0, 'null' => false))
            ->create();

        $collabs->addColumn('organization_name', 'string', array('limit' => 256))
            ->addColumn('sector', 'string', array('limit' => 64))
            ->addColumn('country', 'string', array('limit' => 64))
            ->addColumn('planning', 'text')
            ->addColumn('design', 'text')
            ->addColumn('analysis', 'text')
            ->addColumn('dissemination', 'text')
            ->addColumn('user', 'text')
            ->addColumn('other', 'text')
            ->addColumn('person_name', 'string', array('limit' => 64))
            ->addColumn('position', 'string', array('limit' => 64))
            ->addIndex('organization_name')
            ->create();

        
    }
}
