<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 16.05.19
 * Time: 20:34
 */

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CssClasses extends AbstractExtension {

    const PRICE         = 'price';
    const ID            = 'id';
    const DELETED       = 'deleted';
    const COUNTRY       = 'country';
    const LOCATION      = 'location';
    const REJECTED      = 'rejected';
    const NON_REJECTED  = 'non_rejected';
    const SIMPLE        = 'simple';
    const MEDIUM        = 'medium';
    const HARD          = 'hard';
    const HARDCORE      = 'hardcore';

    const CLASS_TEXT_SUCCESS    = 'text-success';
    const CLASS_TEXT_DANGER     = 'text-danger';
    const CLASS_D_NONE          = 'd-none';
    const CLASS_ALERT           = 'alert';
    const CLASS_ALERT_SUCCESS   = 'alert-success';
    const CLASS_ALERT_WARNING   = 'alert-warning';
    const CLASS_ALERT_SECONDARY = 'alert-secondary';
    const CLASS_ALERT_DANGER    = 'alert-danger';

    public function getFunctions() {
        return [
            new TwigFunction('getClassForAchievementType', [$this, 'getClassForAchievementType']),
            new TwigFunction('getClassForProductType', [$this, 'getClassForProductType']),
            new TwigFunction('isRowHiddenForProductType', [$this, 'isRowHiddenForProductType']),
            new TwigFunction('hideIdColumn', [$this, 'hideIdColumn']),
            new TwigFunction('hideCountryColumn', [$this, 'hideCountryColumn']),
            new TwigFunction('hideLocationColumn', [$this, 'hideLocationColumn']),
        ];
    }

    public function getClassForAchievementType(string $achievement_typ) {
        $class = '';

        switch (strtolower($achievement_typ)) {
            case static::SIMPLE:
                $class = static::CLASS_ALERT . ' ' . static::CLASS_ALERT_SUCCESS;
                break;
            case static::MEDIUM:
                $class = static::CLASS_ALERT . ' ' . static::CLASS_ALERT_WARNING;
                break;
            case static::HARD:
                $class = static::CLASS_ALERT . ' ' . static::CLASS_ALERT_SECONDARY;
                break;
            case static::HARDCORE:
                $class = static::CLASS_ALERT . ' ' . static::CLASS_ALERT_DANGER;
                break;
        }

        return $class;
    }

    public function hideIdColumn($column_name) {
        $id_columns_names = [static::ID];

        if (in_array($column_name, $id_columns_names)) {
            return static::CLASS_D_NONE;
        }
        return '';
    }

    public function hideDeletedColumn($column_name) {
        $id_columns_names = [static::DELETED];

        if (in_array($column_name, $id_columns_names)) {
            return static::CLASS_D_NONE;
        }
        return '';
    }

    public function hideCountryColumn($column_name){
        $country_columns_names = [static::COUNTRY];

        if (in_array($column_name, $country_columns_names)) {
            return static::CLASS_D_NONE;
        }
        return '';
    }

    public function hideLocationColumn($column_name){
        $location_columns_names = [static::LOCATION];

        if (in_array($column_name, $location_columns_names)) {
            return static::CLASS_D_NONE;
        }
        return '';
    }

    public function getClassForProductType(string $rejection_type, int $rejection_status, string $column_name) {
        $class = '';

        if ($column_name == static::PRICE)
            if (
                strtolower($rejection_type) === static::NON_REJECTED && $rejection_status === 0 ||
                strtolower($rejection_type) === static::REJECTED && $rejection_status === 0
            ) {
                $class = static::CLASS_TEXT_SUCCESS;
            } elseif (
                strtolower($rejection_type) === static::NON_REJECTED && $rejection_status === 1 ||
                strtolower($rejection_type) === static::REJECTED && $rejection_status === 1
            ) {
                $class = static::CLASS_TEXT_DANGER;
            }

        return $class;
    }

    public function isRowHiddenForProductType(string $rejection_type, int $rejection_status) {
        $status = false;

        if (
            strtolower($rejection_type) === static::NON_REJECTED && $rejection_status === 0 ||
            strtolower($rejection_type) === static::REJECTED && $rejection_status === 1
        ) {
            $status = true;
        } elseif (
            strtolower($rejection_type) === static::NON_REJECTED && $rejection_status === 1 ||
            strtolower($rejection_type) === static::REJECTED && $rejection_status === 0
        ) {
            $status = false;
        }

        return $status;
    }

}