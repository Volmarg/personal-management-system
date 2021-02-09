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
     * @param string $rejectionType
     * @param int $rejectionStatus
     * @param string $columnName
     * @return string
     */
    public function getClassForProductType(string $rejectionType, int $rejectionStatus, string $columnName) {
        $class = '';

        if ($columnName == static::PRICE)
            if (
                strtolower($rejectionType) === static::NON_REJECTED && $rejectionStatus === 0 ||
                strtolower($rejectionType) === static::REJECTED && $rejectionStatus === 0
            ) {
                $class = static::CLASS_TEXT_SUCCESS;
            } elseif (
                strtolower($rejectionType) === static::NON_REJECTED && $rejectionStatus === 1 ||
                strtolower($rejectionType) === static::REJECTED && $rejectionStatus === 1
            ) {
                $class = static::CLASS_TEXT_DANGER;
            }

        return $class;
    }

    /**
     * @param string $rejectionType
     * @param int $rejectionStatus
     * @return bool
     */
    public function isRowHiddenForProductType(string $rejectionType, int $rejectionStatus) {
        $status = false;

        if (
            strtolower($rejectionType) === static::NON_REJECTED && $rejectionStatus === 0 ||
            strtolower($rejectionType) === static::REJECTED && $rejectionStatus === 1
        ) {
            $status = true;
        } elseif (
            strtolower($rejectionType) === static::NON_REJECTED && $rejectionStatus === 1 ||
            strtolower($rejectionType) === static::REJECTED && $rejectionStatus === 0
        ) {
            $status = false;
        }

        return $status;
    }

}