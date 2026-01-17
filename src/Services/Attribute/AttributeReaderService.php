<?php


namespace App\Services\Attribute;


use App\Services\Routing\UrlMatcherService;
use Laminas\Code\Reflection\MethodReflection;
use LogicException;
use Psr\Log\LoggerInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;

/**
 * This class handles reading/checking php8 attributes on classes and methods
 *
 * Class AttributeReaderService
 * @package App\Service\Attribute
 */
class AttributeReaderService
{

    /**
     * @var UrlMatcherService $urlMatcherService
     */
    private UrlMatcherService $urlMatcherService;

    public function __construct(
        UrlMatcherService $urlMatcherService,
        private readonly LoggerInterface $logger
    )
    {
        $this->urlMatcherService = $urlMatcherService;
    }

    /**
     * Will check if given route has attribute
     *
     * @param string $calledUri
     * @param string $attributeClass
     * @return bool
     * @throws ReflectionException
     */
    public function hasUriAttribute(string $calledUri, string $attributeClass): bool
    {
        $attribute = $this->getUriAttribute($calledUri, $attributeClass);
        return !empty($attribute);
    }

    /**
     * Return class attribute of given name, returns null if no such attribute was found.
     *
     * @param string $classFqn
     * @param string $attributeFqn
     *
     * @return ReflectionAttribute|null
     * @throws ReflectionException
     */
    public function getClassAttribute(string $classFqn, string $attributeFqn): ?ReflectionAttribute
    {
        $reflectionClass  = new ReflectionClass($classFqn);
        $attributes = $reflectionClass->getAttributes($attributeFqn);

        if (count($attributes) > 1) {
            throw new LogicException("Currently, this method does not support reading multiple attributes of the same type.");
        }

        return $attributes[0] ?? null;
    }

    /**
     * Will return null or the provided attribute class
     *
     * @param string $calledUri
     * @param string $attributeClass
     * @return ReflectionAttribute[]
     * @throws ReflectionException
     */
    public function getUriAttribute(string $calledUri, string $attributeClass): array
    {
        $uriWithoutQueryParams = preg_replace("#\?.*#", "", $calledUri);
        $classWithMethodForUri = $this->urlMatcherService->getClassAndMethodForCalledUrl($uriWithoutQueryParams);

        $methodReflection = new MethodReflection($classWithMethodForUri);
        $attributes       = $methodReflection->getAttributes();

        $matchingAttrs = [];
        foreach ($attributes as $reflectionAttribute) {
            if ($attributeClass === $reflectionAttribute->getName()) {
                $matchingAttrs[] = $reflectionAttribute;
            }
        }

        return $matchingAttrs;
    }

}