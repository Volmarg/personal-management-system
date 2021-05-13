<?php

namespace App\Annotation\System;

use Doctrine\Common\Annotations\Annotation\Attribute;

/**
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *      @Attribute("name", type="string")
 * })
 *
 * This annotation handles checking if given method should be called, by ensuring that it's not relating to locked
 * resource
 *
 * This is especially helpful to control access to the action methods / classes
 *
 */
class ModuleAnnotation
{
    const ATTRIBUTE_KEY_NAME = "name";

    /**
     * @var string $name
     * @Required
     */
    private string $name = "";

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * LockedResource constructor.
     *
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->name = $values[self::ATTRIBUTE_KEY_NAME];
    }

}