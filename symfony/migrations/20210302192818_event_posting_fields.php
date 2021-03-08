<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class EventPostingFields extends AbstractMigration
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
        $table = $this->table('grand_posting_images', array('id' => 'id'));
        $table->addColumn('tbl', 'string', array('limit' => 32))
              ->addColumn('posting_id', 'integer')
              ->addColumn('index', 'integer')
              ->addColumn('mime', 'string', array('limit' => '64'))
              ->addColumn('data', 'text', array('limit' => MysqlAdapter::TEXT_MEDIUM))
              ->addIndex('tbl')
              ->addIndex('posting_id')
              ->addIndex('index')
              ->create();
              
        $rows = $this->fetchAll("SELECT * FROM `grand_event_postings` WHERE image != ''");
        foreach($rows as $row){
            $exploded = explode(";", $row['image']);
            $mime = @str_replace("data:", "", $exploded[0]);
            $this->execute("INSERT INTO `grand_posting_images` (`tbl`,`posting_id`,`index`,`mime`,`data`)
                            VALUES ('grand_event_postings', {$row['id']}, 0, '$mime', '{$row['image']}')");
        }
        
        $rows = $this->fetchAll("SELECT * FROM `grand_news_postings` WHERE image != ''");
        foreach($rows as $row){
            $exploded = explode(";", $row['image']);
            $mime = @str_replace("data:", "", $exploded[0]);
            $this->execute("INSERT INTO `grand_posting_images` (`tbl`,`posting_id`,`index`,`mime`,`data`)
                            VALUES ('grand_news_postings', {$row['id']}, 0, '$mime', '{$row['image']}')");
        }
        
        $rows = $this->fetchAll("SELECT * FROM `grand_bsi_postings` WHERE image != ''");
        foreach($rows as $row){
            $exploded = explode(";", $row['image']);
            $mime = @str_replace("data:", "", $exploded[0]);
            $this->execute("INSERT INTO `grand_posting_images` (`tbl`,`posting_id`,`index`,`mime`,`data`)
                            VALUES ('grand_bsi_postings', {$row['id']}, 0, '$mime', '{$row['image']}')");
        }
        
        $table = $this->table('grand_event_postings');
        $table->removeColumn('image')
              ->save();
              
        $table = $this->table('grand_news_postings');
        $table->removeColumn('image')
              ->save();
              
        $table = $this->table('grand_bsi_postings');
        $table->removeColumn('image')
              ->save();
              
        $table = $this->table('grand_event_postings');
        $table->addColumn('website', 'string', array('limit' => 256, 'after' => 'article_link'))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
