<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class GrantsCoPi extends AbstractMigration
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
        $this->table('grand_grants_copi', array("id" => false, "primary_key" => array("copi", "grant_id")))
             ->addColumn('copi', 'integer', array('null' => false))
             ->addColumn('grant_id', 'integer', array('null' => false))
             ->create();
        $this->execute('TRUNCATE grand_grants_copi');     
        $stmt = $this->query('SELECT * FROM grand_grants');
        foreach($stmt->fetchAll() as $row){
            $copis = unserialize($row['copi']);
            foreach($copis as $copi){
                if(is_numeric($copi)){
                    $this->execute("INSERT INTO grand_grants_copi (`copi`, `grant_id`) VALUES ('$copi','{$row['id']}')"); 
                }
            }
        }
    }
}
