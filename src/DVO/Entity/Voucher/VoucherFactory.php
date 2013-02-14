<?php

namespace DVO\Entity\Voucher;

use DVO\Cache;


class VoucherFactory
{
    protected $_gateway;
    protected $_cache;
    
    /**
     * VoucherFactory constructor
     *
     * @return void
     * @author
     **/
    public function __construct(VoucherGateway $gateway, Cache $cache)
    {   
        $this->_gateway = $gateway;
        $this->_cache   = $cache;
    }

    /**
     * Creates the Voucher
     *
     * @return void
     * @author 
     **/
    public static function create() {
        return new \DVO\Entity\Voucher;
    }

    /**
     * Gets the vouchers
     *
     * @return void
     * @author 
     **/
    public function getVouchers()
    {
        $vouchers = array_map(function($voucher) {
            $vc = VoucherFactory::create();
            foreach ($voucher as $key => $value) {
                $vc->$key = $value;
            }

            return $vc;
        }, $this->_gateway->getAllVouchers());

        return $vouchers;
    }
}