<?php
namespace NeosRulez\ShoppingCart\Domain\Model;

/*
 * This file is part of the NeosRulez.ShoppingCart package.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Scope("session")
 */
class Cart {

    /**
     * @var array
     */
    protected $items = array();

    /**
     * @param array $item
     * @return void
     * @Flow\Session(autoStart = TRUE)
     */
    public function addItem($item) {
        $cart = $this->items;
        $quantity = intval($item['quantity']);
        $key = array_search($item['articleNumber'], array_column($cart, 'articleNumber'));

        if ($key!==FALSE) {
            $quantitycheck = intval($cart[$key]['quantity']);
            if ($quantitycheck>0) {
                $quantity = intval($quantitycheck+$quantity);
            }
        }

        $item['quantity'] = $quantity;
        $item['timestamp'] = time();

        $price = $item['articlePrice'];
        $price = str_replace(',', '.', $price);
        $fullprice = $price*$quantity;
        $item['fullprice'] = floatval($fullprice);
        $item['articlePrice'] = floatval($price);

        $tax = intval($item['tax']);
        $taxvalue = floatval($price)/100*$tax;
        $item['taxvalue'] = $taxvalue*$quantity;

        if ($key!==FALSE) {
            $this->items[$key] = $item;
        } else {
            $this->items[] = $item;
        }

    }

    /**
     * @param array $item
     * @return void
     * @Flow\Session(autoStart = TRUE)
     */
    public function updateItem($item) {
        $cart = $this->items;
        $quantity = intval($item['quantity']);
        $key = array_search($item['articleNumber'], array_column($cart, 'articleNumber'));

        $item['quantity'] = $quantity;
        $item['timestamp'] = time();

        $price = $item['articlePrice'];
        $price = str_replace(',', '.', $price);
        $fullprice = $price*$quantity;
        $item['fullprice'] = floatval($fullprice);
        $item['articlePrice'] = floatval($price);

        $tax = intval($item['tax']);
        $taxvalue = floatval($price)/100*$tax;
        $item['taxvalue'] = $taxvalue*$quantity;

        $this->items[$key] = $item;

    }

    /**
     * @return void
     */
    public function miniCart() {
        $cart = $this->items;
        $cartcount = count($cart);
        if ($cartcount>0) {
            $sum = FALSE;
            $sum = intval($sum);
            foreach ($cart as $dat) {
                $quantity = intval($dat["quantity"]);
                $sum += $quantity;
            }
        } else {
            $sum = '0';
        }
        return $sum;
    }

    /**
     * @return void
     */
    public function cart() {
        $cart = $this->items;
        return $cart;
    }

    /**
     * @return void
     */
    public function deleteCart() {
        $cart = $this->items;
        $cartcount = count($cart);
        for ($i = 0; $i < $cartcount; $i++) {
            unset($this->items[$i]);
        }
    }

    /**
     * @param string $id
     * @return void
     */
    public function removeItem($id) {
        $cart = $this->items;
        $key = array_search($id, array_column($cart, 'timestamp'));
        unset($this->items[$key]);
        sort($this->items);
    }

}
