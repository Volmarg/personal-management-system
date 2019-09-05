<?php
namespace App\DataFixtures\Providers\Business;

class Shops {

    # Supermarkets
    const SHOP_NAME_LIDL     = 'Lidl';
    const SHOP_NAME_ALDI     = 'Aldi';
    const SHOP_NAME_REVE     = 'Reve';
    const SHOP_NAME_KAUFLAND = 'Kaufland';

    const SUPERMARKETS = [
        self::SHOP_NAME_LIDL,
        self::SHOP_NAME_ALDI,
        self::SHOP_NAME_REVE,
        self::SHOP_NAME_KAUFLAND,
    ];

    # Domestic shops
    const SHOP_NAME_DM       = 'Dm';
    const SHOP_NAME_ROSSMANN = 'Rossmann';

    const DOMESTIC_SHOPS = [
        self::SHOP_NAME_DM,
        self::SHOP_NAME_ROSSMANN,
    ];

    # all
    const KEY_GROUP_SUPERMARKETS   = 'supermarkets';
    const KEY_GROUP_DOMESTIC_SHOPS = 'domestic_shops';

    const ALL = [
        self::KEY_GROUP_SUPERMARKETS   => self::SUPERMARKETS,
        self::KEY_GROUP_DOMESTIC_SHOPS => self::DOMESTIC_SHOPS,
    ];

    /**
     * @var boolean $areGroups
     */
    private $areGroups = true;


}