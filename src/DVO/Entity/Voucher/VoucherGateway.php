<?php

/**
 * This file is part of the DVO package.
 *
 * (c) Bobby DeVeaux <me@bobbyjason.co.uk> / t: @bobbyjason
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace DVO\Entity\Voucher;

use phpcassa\ColumnFamily;
use phpcassa\ColumnSlice;
use phpcassa\Connection\ConnectionPool;
use phpcassa\SystemManager;
use phpcassa\Index\IndexExpression;
use phpcassa\Index\IndexClause;
use phpcassa\Schema\DataType\UTF8Type;
use Davegardnerisme\Cruftflake;

class VoucherGateway
{
    protected $cf;
    protected $pool;
    protected $column_family;
    protected $column_family_counter;

    /**
     * Setup the VoucherGatway.
     *
     * @param CruftFlake\CruftFlake $cruftflake Instance of cruftflake.
     */
    public function __construct(CruftFlake\CruftFlake $cruftflake)
    {
        $this->cf = $cruftflake;
        $this->cf->setTimeout(500);
    }

    /**
     * Get the connection, no need to do it on construct.
     *
     * @return void
     */
    protected function getConnection()
    {
        if (true === empty($this->pool)) {
            $this->pool                  = new ConnectionPool('DVO');
            $this->column_family         = new ColumnFamily($this->pool, 'Vouchers');
            $this->column_family_counter = new ColumnFamily($this->pool, 'Counters');
        }
    }

    /**
     * Get vouchers.
     *
     * @param integer $voucherId The voucher ID.
     *
     * @return array
     */
    public function getVouchers($voucherId = null)
    {
        $this->getConnection();
        if (false === empty($voucherId)) {
            try {
                $vouchers[$voucherId] = $this->column_family->get($voucherId);
            } catch (\Exception $ex) {
                // nothing for now, just return an empty array
                $vouchers = array();
            }

        } else {
            try {
                $vouchers = iterator_to_array($this->column_family->get_range(), true);
            } catch (\Exception $ex) {
                // nothing for now, just return an empty array
                $vouchers = array();
            }
        }

        return $vouchers;
    }

    /**
     * Insert voucher.
     *
     * @param \DVO\Entity\Voucher $voucher An instance of a voucher.
     *
     * @return boolean
     */
    public function insertVoucher(\DVO\Entity\Voucher $voucher)
    {
        $id        = false;
        $increment = false;
        $this->getConnection();

        // insert is also an update (upsert), so we need to check to see if it exists
        $index_exp    = new IndexExpression('voucher_code', $voucher->getVoucherCode());
        $index_clause = new IndexClause(array($index_exp));
        $rows         = iterator_to_array($this->column_family->get_indexed_slices($index_clause));

        if (count($rows) > 0) {
            $id = key($rows);
        }

        // new voucher, get new ID.
        if ($id === false) {
            $id = $this->cf->generateId();
            if ($id === false) {
                return $id;
            }

            $increment = true;
        }


        // horrible try catches as phpcassa seems to throw them all over the shop.
        try {
            $this->column_family->insert(
                $id,
                array(
                    'voucher_code' => $voucher->getVoucherCode(),
                    'description'  => $voucher->getDescription())
            );

            $counter = 0;
            try {
                $this->column_family_counter->insert(
                    'vouchers',
                    array(
                        'dummy' => '-'
                    )
                );
                $counter = $this->column_family_counter->get('vouchers')['counter'];
            } catch (Exception $ex) {
                // log it
            }

            // only increment if it's a new voucher
            if (true === $increment) {
                $this->column_family_counter->insert(
                    'vouchers',
                    array(
                        'counter' => ++$counter
                    )
                );
            }

        } catch (Exception $ex) {
            // log this shizzle
            return false;
        }

        return $id;
    }
}
