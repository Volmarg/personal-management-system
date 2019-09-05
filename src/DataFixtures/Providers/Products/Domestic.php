<?php
namespace App\DataFixtures\Providers\Products;

class Domestic {

    # Chemical
    const DOMESTIC_RORAX           = 'rorax';
    const DOMESTIC_DISH_SOAP       = 'dish soap';
    const DOMESTIC_WINDOW_CLEANER  = 'window cleaner';

    const CHEMICAL = [
        self::DOMESTIC_RORAX,
        self::DOMESTIC_DISH_SOAP,
        self::DOMESTIC_WINDOW_CLEANER,
    ];

    # Personal usage
    const DOMESTIC_TOILET_PAPER    = 'toiler paper';
    const DOMESTIC_BEARD_SOAP      = 'beard soap';
    const DOMESTIC_FABRIC_SOFTENER = 'napkins';

    const PERSONAL_USAGE = [
        self::DOMESTIC_TOILET_PAPER,
        self::DOMESTIC_BEARD_SOAP,
        self::DOMESTIC_FABRIC_SOFTENER,
    ];

    # Other
    const DOMESTIC_BIO_FOIL        = 'bio foil';

    const OTHER = [
        self::DOMESTIC_BIO_FOIL
    ];

    # all
    const KEY_GROUP_OTHER          = 'other';
    const KEY_GROUP_CHEMICAL       = 'chemical';
    const KEY_GROUP_PERSONAL_USAGE = 'personal_usage';

    const ALL = [
      self::KEY_GROUP_OTHER           => self::OTHER,
      self::KEY_GROUP_CHEMICAL        => self::CHEMICAL,
      self::KEY_GROUP_PERSONAL_USAGE  => self::PERSONAL_USAGE
    ];

    /**
     * @var boolean $areGroups
     */
    private $areGroups = true;

    /**
     * @var array
     */
    public $all = [];

    public function __construct() {
        $this->all = array_merge(self::PERSONAL_USAGE, self::CHEMICAL, self::OTHER);
    }
}