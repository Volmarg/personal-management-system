<?php

namespace App\Annotation\System;

use Doctrine\Common\Annotations\Annotation\Attribute;

/**
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *      @Attribute("record", type="string"),
 *      @Attribute("type",   type="string"),
 *      @Attribute("target", type="string")
 * })
 *
 * This annotation handles checking if given method should be called, by ensuring that it's not relating to locked
 * resource
 *
 * This is especially helpful to control access to the action methods / classes
 *
 */
class LockedResource
{
    const ATTRIBUTE_NAME_RECORD = "record";
    const ATTRIBUTE_NAME_TYPE   = "type";
    const ATTRIBUTE_NAME_TARGET = "target";

    /**
     * @var string $record
     * @Required
     */
    private string $record = "";

    /**
     * @var string $type
     * @Required
     * @Required
     */
    private string $type;

    /**
     * @var string $target
     * @Required
     */
    private string $target;

    /**
     * @return string
     */
    public function getRecord(): string
    {
        return $this->record;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * LockedResource constructor.
     *
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->record = $values[self::ATTRIBUTE_NAME_RECORD] ?? "";
        $this->target = $values[self::ATTRIBUTE_NAME_TARGET];
        $this->type   = $values[self::ATTRIBUTE_NAME_TYPE];
    }

}