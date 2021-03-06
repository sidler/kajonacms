<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class test_class_dateHelperTest extends class_testbase  {

    public function testIsEasterHoliday() {
        $objHelper = new class_date_helper();

        //2014
        $this->assertTrue(!$objHelper->isEasterHoliday(new class_date(20140417010000)));//
        $this->assertTrue($objHelper->isEasterHoliday(new class_date(20140418010000)));//friday
        $this->assertTrue($objHelper->isEasterHoliday(new class_date(20140419010000)));
        $this->assertTrue($objHelper->isEasterHoliday(new class_date(20140420010000)));
        $this->assertTrue($objHelper->isEasterHoliday(new class_date(20140421010000)));
        $this->assertTrue(!$objHelper->isEasterHoliday(new class_date(20140422010000)));

        //2015 (leap year)
        $this->assertTrue(!$objHelper->isEasterHoliday(new class_date(20160324010000)));
        $this->assertTrue($objHelper->isEasterHoliday(new class_date(20160325010000)));//friday
        $this->assertTrue($objHelper->isEasterHoliday(new class_date(20160326010000)));
        $this->assertTrue($objHelper->isEasterHoliday(new class_date(20160327010000)));
        $this->assertTrue($objHelper->isEasterHoliday(new class_date(20160328010000)));
        $this->assertTrue(!$objHelper->isEasterHoliday(new class_date(20160329010000)));


        $this->assertTrue($objHelper->isEasterHoliday(new class_date(20170414010000)));//friday
        $this->assertTrue($objHelper->isEasterHoliday(new class_date(20180330010000)));//friday
        $this->assertTrue($objHelper->isEasterHoliday(new class_date(20190419010000)));//friday

        $this->assertTrue($objHelper->isEasterHoliday(new class_date(20200410010000)));//leap year
        $this->assertTrue($objHelper->isEasterHoliday(new class_date(20240329010000)));//leap year


    }

    public function testIsTarget2Day() {
        $objHelper = new class_date_helper();

        //1.1.
        $this->assertTrue(!$objHelper->isValidTarget2Day(new class_date(20140101010000)));
        //1.5.
        $this->assertTrue(!$objHelper->isValidTarget2Day(new class_date(20140501010000)));

        //easter
        $this->assertTrue($objHelper->isValidTarget2Day(new class_date(20140417010000)));
        $this->assertTrue(!$objHelper->isValidTarget2Day(new class_date(20140418010000)));
        $this->assertTrue(!$objHelper->isValidTarget2Day(new class_date(20140419010000)));
        $this->assertTrue(!$objHelper->isValidTarget2Day(new class_date(20140420010000)));
        $this->assertTrue(!$objHelper->isValidTarget2Day(new class_date(20140421010000)));
        $this->assertTrue($objHelper->isValidTarget2Day(new class_date(20140422010000)));

        //xmas
        $this->assertTrue(!$objHelper->isValidTarget2Day(new class_date(20141225010000)));
        $this->assertTrue(!$objHelper->isValidTarget2Day(new class_date(20141226010000)));

        $this->assertTrue(!$objHelper->isValidTarget2Day(new class_date(20141227010000)));
        $this->assertTrue(!$objHelper->isValidTarget2Day(new class_date(20141228010000)));
        $this->assertTrue($objHelper->isValidTarget2Day(new class_date(20141229010000)));

        //random weekday
        $this->assertTrue($objHelper->isValidTarget2Day(new class_date(20141121010000)));
        $this->assertTrue($objHelper->isValidTarget2Day(new class_date(20141124010000)));


        //random weekend
        $this->assertTrue(!$objHelper->isValidTarget2Day(new class_date(20141122010000)));
        $this->assertTrue(!$objHelper->isValidTarget2Day(new class_date(20141123010000)));
    }

    public function testIsLeapYear() {
        $objHelper = new class_date_helper();

        $this->assertTrue($objHelper->isLeapYear(2016));
        $this->assertTrue(!$objHelper->isLeapYear(2017));
        $this->assertTrue(!$objHelper->isLeapYear(2018));
        $this->assertTrue(!$objHelper->isLeapYear(2019));
        $this->assertTrue($objHelper->isLeapYear(2020));
        $this->assertTrue(!$objHelper->isLeapYear(2021));
        $this->assertTrue(!$objHelper->isLeapYear(2022));
        $this->assertTrue(!$objHelper->isLeapYear(2023));
        $this->assertTrue($objHelper->isLeapYear(2024));
        $this->assertTrue(!$objHelper->isLeapYear(2025));
        $this->assertTrue(!$objHelper->isLeapYear(2026));
        $this->assertTrue(!$objHelper->isLeapYear(2027));
        $this->assertTrue($objHelper->isLeapYear(2028));
    }


}

