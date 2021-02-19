<?php


use Phinx\Migration\AbstractMigration;

class UofaNews extends AbstractMigration
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
        $table = $this->table('grand_uofa_news', array("id"=>"id"));
        $table->addColumn('user_id','integer')
              ->addColumn('title','string', array('limit' => '256'))
              ->addColumn('url', 'string', array('limit' => '256'))
              ->addColumn('first_sentences', 'text')
              ->addColumn('img', 'string', array('limit' => '256'))
              ->addColumn('date', 'timestamp')
              ->addIndex(array('user_id'))
              ->create();
        $this->execute("ALTER TABLE `grand_uofa_news` ENGINE = MYISAM");
        $this->execute("ALTER TABLE `grand_uofa_news` CONVERT TO CHARACTER SET utf8");
        $this->execute("ALTER TABLE `grand_uofa_news` ADD FULLTEXT (`title`)");
    }
}
