<?php

namespace App\Services\Annotation;

use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionException;

class AnnotationReaderService
{

    /**
     * @var Reader $annotationReader
     */
    private Reader $annotationReader;

    /**
     * AnnotationReaderService constructor.
     *
     * @param Reader $annotationReader
     */
    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * @param string $classNamespace
     * @param string $annotationName
     * @return object|null
     * @throws ReflectionException
     */
    public function getClassAnnotation(string $classNamespace, string $annotationName): ?object
    {
        $reflectionClass  = new ReflectionClass($classNamespace);
        $annotation       = $this->annotationReader->getClassAnnotation($reflectionClass, $annotationName);

        return $annotation;
    }

}