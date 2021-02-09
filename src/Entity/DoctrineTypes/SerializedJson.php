<?php
namespace App\Entity\DoctrineTypes;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Symfony\Component\HttpFoundation\Response;


class SerializedJson extends Type
{
    const TYPE_NAME = 'serialized_json'; // modify to match your type name

    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return $platform->getVarcharTypeDeclarationSQL($column);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        try{
            $array = unserialize($value);
        }catch(\Exception $e){
            throw new \Exception("Could not unserialize json from DB", Response::HTTP_INTERNAL_SERVER_ERROR, $e);
        }

        return $array;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if( !is_array($value) ){
            throw new \TypeError("Expected array! Got: " . gettype($value) );
        }

        $serializedJson = serialize($value);
        return $serializedJson;
    }

    public function getName()
    {
        return self::TYPE_NAME;
    }
}