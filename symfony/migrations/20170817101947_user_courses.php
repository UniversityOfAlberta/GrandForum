<?php

use Phinx\Migration\AbstractMigration;

class UserCourses extends AbstractMigration
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
/*    public function change()
    {

    }*/
    public function up()
    {
      $table = $this->table("grand_user_courses",array("id" => "id"));
      if(!$table->exists()){
            $table->addColumn('user_id', 'integer')
                  ->addColumn('course_id', 'integer')
                  ->addIndex(array('user_id'))
                  ->addIndex(array('course_id'))
                  ->create();
      }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
