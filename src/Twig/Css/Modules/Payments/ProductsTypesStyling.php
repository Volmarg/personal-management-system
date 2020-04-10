<?php

namespace App\Twig\Css\Modules\Payments;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ProductsTypesStyling extends AbstractExtension {

    const PRICE         = 'price';
    const REJECTED      = 'rejected';
    const NON_REJECTED  = 'non_rejected';

    const CLASS_TEXT_SUCCESS    = 'text-success';
    const CLASS_TEXT_DANGER     = 'text-danger';

    public function getFunctions() {
        return [
            new TwigFunction('getClassForProductType', [$this, 'getClassForProductType']),
            new TwigFunction('isRowHiddenForProductType', [$this, 'isRowHiddenForProductType']),
        ];
    }

    /**
     * @param string $rejection_type
     * @param int $rejection_status
     * @param string $column_name
     * @return string
     */
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

    /**
     * @param string $rejection_type
     * @param int $rejection_status
     * @return bool
     */
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