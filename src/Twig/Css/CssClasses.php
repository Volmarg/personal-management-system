<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 16.05.19
 * Time: 20:34
 */

namespace App\Twig\Css;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @deprecated - shall be cleaned one day
 * Class CssClasses
 * @package App\Twig
 */
class CssClasses extends AbstractExtension {

    const ID            = 'id';
    const DELETED       = 'deleted';
    const COUNTRY       = 'country';
    const LOCATION      = 'location';

    const CLASS_D_NONE  = 'd-none';

    public function getFunctions() {
        return [
            new TwigFunction('hideIdColumn', [$this, 'hideIdColumn']),
            new TwigFunction('hideCountryColumn', [$this, 'hideCountryColumn']),
            new TwigFunction('hideLocationColumn', [$this, 'hideLocationColumn']),
        ];
    }

    /**
     * @param $columnName
     * @return string
     */
    public function hideIdColumn($columnName) {
        $idColumnsNames = [static::ID];

        if (in_array($columnName, $idColumnsNames)) {
            return static::CLASS_D_NONE;
        }
        return '';
    }

    /**
     * @param $columnName
     * @return string
     */
    public function hideDeletedColumn($columnName) {
        $idColumnsNames = [static::DELETED];

        if (in_array($columnName, $idColumnsNames)) {
            return static::CLASS_D_NONE;
        }
        return '';
    }

    /**
     * @param $columnName
     * @return string
     */
    public function hideCountryColumn($columnName){
        $countryColumnsNames = [static::COUNTRY];

        if (in_array($columnName, $countryColumnsNames)) {
            return static::CLASS_D_NONE;
        }
        return '';
    }

    /**
     * @param $columnName
     * @return string
     */
    public function hideLocationColumn($columnName){
        $locationColumnsNames = [static::LOCATION];

        if (in_array($columnName, $locationColumnsNames)) {
            return static::CLASS_D_NONE;
        }
        return '';
    }

}