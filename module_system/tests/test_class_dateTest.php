<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_class_date extends class_testbase  {


    public function testNextMonth() {
        $objDate = new class_date(20130101000000);
        $objDate->setNextMonth();
        $this->assertEquals($objDate->getLongTimestamp(), 20130201000000);

        $objDate = new class_date(20130115120000);
        $objDate->setNextMonth();
        $this->assertEquals($objDate->getLongTimestamp(), 20130215120000);

        $objDate = new class_date(20130131120000);
        $objDate->setNextMonth();
        $this->assertEquals($objDate->getLongTimestamp(), 20130228120000);

        $objDate = new class_date(20130228120000);
        $objDate->setNextMonth();
        $this->assertEquals($objDate->getLongTimestamp(), 20130328120000);

        $objDate = new class_date(20130331120000);
        $objDate->setNextMonth();
        $this->assertEquals($objDate->getLongTimestamp(), 20130430120000);
    }


    public function testPreviousMonth() {
        $objDate = new class_date(20130101120000);
        $objDate->setPreviousMonth();
        $this->assertEquals($objDate->getLongTimestamp(), 20121201120000);

        $objDate = new class_date(20130430120000);
        $objDate->setPreviousMonth();
        $this->assertEquals($objDate->getLongTimestamp(), 20130330120000);

        $objDate = new class_date(20130331120000);
        $objDate->setPreviousMonth();
        $this->assertEquals($objDate->getLongTimestamp(), 20130228120000);

        $objDate = new class_date(20130831120000);
        $objDate->setPreviousMonth();
        $this->assertEquals($objDate->getLongTimestamp(), 20130731120000);
    }


    public function testNextWeek() {
        $objDate = new class_date(20130115120000);
        $objDate->setNextWeek();
        $this->assertEquals($objDate->getLongTimestamp(), 20130122120000);
    }

    public function testPreviousWeek() {
        $objDate = new class_date(20130122120000);
        $objDate->setPreviousWeek();
        $this->assertEquals($objDate->getLongTimestamp(), 20130115120000);
    }


}
