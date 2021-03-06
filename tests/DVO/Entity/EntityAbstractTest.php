<?php

/**
 * This file is part of the DVO package.
 *
 * (c) Bobby DeVeaux <me@bobbyjason.co.uk> / t: @bobbyjason
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class EntityAbstractTest extends \PHPUnit_Framework_TestCase
{
    public function testEntityVoucher()
    {
        $obj = new \DVO\Entity\Voucher;
        $this->assertInstanceOf('\DVO\Entity\EntityAbstract', $obj);
    }

    public function testEntityMagicFuncs()
    {
        $obj = new \DVO\Entity\Voucher;
        $obj->setVoucher_Code('ASD123');

        $this->assertEquals($obj->getVoucher_Code(), 'ASD123');
    }

    public function testGetData()
    {
        $obj = new \DVO\Entity\Voucher;
        $obj->setVoucher_Code('ASD123');

        $data = $obj->getData();

        $this->assertEquals(array(
            'id' =>'',
            'voucher_code' => 'ASD123',
            'description' => '',
            'title'             => '',
            'merchant'          => '',
            'merchant_logo_url' => '',
            'merchant'          => '',
            'start_date'        => '',
            'expiry_date'       => '',
            'deep_link'         => '',
            'merchant_url'      => '',
            'category_id'       => '',
            'category'          => '',
            'deep_link'         => '',
        ), $data);
    }

    /**
     * @expectedException \DVO\Entity\Exception
     */
    public function testDefaultMagic()
    {
        $obj = new \DVO\Entity\Voucher;
        $var = $obj->placeholder();
    }

    /**
     * @expectedException \DVO\Entity\Exception
     */
    public function testMagicSetFuncsFail()
    {
        $obj = new \DVO\Entity\Voucher;
        $obj->something = 'test';
    }

    /**
     * @group entityTests
     * @expectedException \DVO\Entity\Exception
     */
    public function testMagicGetFuncsFail()
    {
        $obj = new \DVO\Entity\Voucher;
        $testing = $obj->something;
    }

}