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
     * @param $column_name
     * @return string
     */
    public function hideIdColumn($column_name) {
        $id_columns_names = [static::ID];

        if (in_array($column_name, $id_columns_names)) {
            return static::CLASS_D_NONE;
        }
        return '';
    }

    /**
     * @param $column_name
     * @return string
     */
    public function hideDeletedColumn($column_name) {
        $id_columns_names = [static::DELETED];

        if (in_array($column_name, $id_columns_names)) {
            return static::CLASS_D_NONE;
        }
        return '';
    }

    /**
     * @param $column_name
     * @return string
     */
    public function hideCountryColumn($column_name){
        $country_columns_names = [static::COUNTRY];

        if (in_array($column_name, $country_columns_names)) {
            return static::CLASS_D_NONE;
        }
        return '';
    }

    /**
     * @param $column_name
     * @return string
     */
    public function hideLocationColumn($column_name){
        $location_columns_names = [static::LOCATION];

        if (in_array($column_name, $location_columns_names)) {
            return static::CLASS_D_NONE;
        }
        return '';
    }

}