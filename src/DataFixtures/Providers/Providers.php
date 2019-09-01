<?php


abstract class Providers {

    const KEY_PRICE_RANGE_MIN = 'price_range_min';
    const KEY_PRICE_RANGE_MAX = 'price_range_max';

    /**
     * Think about things like:
     *  getWithPrices
     *  getWithoutPrices
     *  amountOfSetsToCreateForMonth
     *  $this->canProductRepeat
     *  $this->productsRepeatCount = []
     *  Provider type should be either callable by construct type param or by making functions Providers->food()->...
     */

}