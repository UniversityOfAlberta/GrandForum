<?php

use Phinx\Migration\AbstractMigration;

class AddCourseTable extends AbstractMigration
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
        $this->execute("CREATE TABLE IF NOT EXISTS `grand_courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Acad Org` varchar(10) DEFAULT NULL,
  `Term` int(4) DEFAULT NULL,
  `Short Desc` varchar(9) DEFAULT NULL,
  `Class Nbr` int(5) DEFAULT NULL,
  `Subject` varchar(5) DEFAULT NULL,
  `Catalog` varchar(5) DEFAULT NULL,
  `Component` varchar(3) DEFAULT NULL,
  `Sect` varchar(4) DEFAULT NULL,
  `Descr` varchar(30) DEFAULT NULL,
  `Crs Status` varchar(1) DEFAULT NULL,
  `Facil ID` int(8) DEFAULT NULL,
  `Place` varchar(10) DEFAULT NULL,
  `Pat` varchar(4) DEFAULT NULL,
  `Start Date` int(5) DEFAULT NULL,
  `End Date` int(5) DEFAULT NULL,
  `Hrs From` varchar(5) DEFAULT NULL,
  `Hrs To` varchar(5) DEFAULT NULL,
  `Mon` varchar(1) DEFAULT NULL,
  `Tues` varchar(1) DEFAULT NULL,
  `Wed` varchar(1) DEFAULT NULL,
  `Thurs` varchar(1) DEFAULT NULL,
  `Fri` varchar(1) DEFAULT NULL,
  `Sat` varchar(1) DEFAULT NULL,
  `Sun` varchar(1) DEFAULT NULL,
  `Class Type` varchar(1) DEFAULT NULL,
  `Cap Enrl` int(3) DEFAULT NULL,
  `Tot Enrl` int(3) DEFAULT NULL,
  `Campus` varchar(4) DEFAULT NULL,
  `Location` varchar(4) DEFAULT NULL,
  `Notes Nbr` int(1) DEFAULT NULL,
  `Note Nbr` int(4) DEFAULT NULL,
  `Note` varchar(100) DEFAULT NULL,
  `Rq Group` int(6) DEFAULT NULL,
  `Restriction Descr` varchar(30) DEFAULT NULL,
  `Approved Hrs` varchar(8) DEFAULT NULL,
  `Duration` varchar(6) DEFAULT NULL,
  `Career` varchar(4) DEFAULT NULL,
  `Consent` varchar(1) DEFAULT NULL,
  `Course Descr` varchar(1008) DEFAULT NULL,
  `Max Units` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Subject` (`Subject`),
  KEY `Catalog` (`Catalog`)
) ");

    }
   public function down(){

}

}
