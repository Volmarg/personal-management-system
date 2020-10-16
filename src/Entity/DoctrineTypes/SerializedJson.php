<?php
namespace App\Entity\DoctrineTypes;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Symfony\Component\HttpFoundation\Response;


class SerializedJson extends Type
{
    const TYPE_NAME = 'serialized_json'; // modify to match your type name

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        // return the SQL used to create your column type. To create a portable column type, use the $platform.
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
        // This is executed when the value is written to the database. Make your conversions here, optionally using the $platform.
    }

    public function getName()
    {
        return self::TYPE_NAME;
    }
}