<?php

namespace App\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class CollectionService
{
    /**
     * This is needed for cases when the collection gets filtered, and its data is used on frontend.
     * If indexes are lost, then the frontend might turn the array into the object, and it will break the frontend.
     *
     * @param Collection $collection
     * @param callable   $filterCallback
     *
     * @return Collection
     */
    public static function filterAndReindex(Collection $collection, callable $filterCallback): Collection
    {
        return new ArrayCollection($collection->filter($filterCallback)->getValues());
    }
}