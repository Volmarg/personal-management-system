<?php

namespace App\Response;

use App\Response\Base\BaseResponse;

class PaginatedResponse extends BaseResponse
{
    private int $maxPageNumber;
    private int $currentPageNumber;
    private int $totalResults;

    public function getMaxPageNumber(): int
    {
        return $this->maxPageNumber;
    }

    public function setMaxPageNumber(int $maxPageNumber): void
    {
        $this->maxPageNumber = $maxPageNumber;
    }

    public function getCurrentPageNumber(): int
    {
        return $this->currentPageNumber;
    }

    public function setCurrentPageNumber(int $currentPageNumber): void
    {
        $this->currentPageNumber = $currentPageNumber;
    }

    public function getTotalResults(): int
    {
        return $this->totalResults;
    }

    public function setTotalResults(int $totalResults): void
    {
        $this->totalResults = $totalResults;
    }

}