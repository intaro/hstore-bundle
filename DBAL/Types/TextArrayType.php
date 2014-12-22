<?php

namespace Intaro\HStoreBundle\DBAL\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Postgres array data type
 */
class TextArrayType extends Type
{
    const TEXT_ARRAY = 'text_array';
    const ESCAPE = '"\\';

    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'text[]';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        $value = mb_substr($value, 1, -1);
        $result = explode(',', $value);

        return $result;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (is_null($value)) {
            return null;
        }

        if (!is_array($value)) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        $result = addcslashes(implode(',', array_filter($value)), self::ESCAPE);
        $result = '{' . $result . '}';

        return $result;
    }

    public function getName()
    {
        return self::TEXT_ARRAY;
    }
}
