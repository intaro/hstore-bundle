<?php

namespace Intaro\HStoreBundle\DBAL\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * HStore data type
 * Zephir mirror-class in Resources/zephir/hstore
 */
class HStoreType extends Type
{
    const HSTORE = 'hstore'; // modify to match your type name
    const ESCAPE = '"\\';

    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return self::HSTORE;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        $parser = null;
        // php extension
        if (class_exists('\HStoreCppParser')) {
            $parser = new \HStoreCppParser();
        } elseif (class_exists('\HStore\HStoreParser')) {
            $parser = new \HStore\HStoreParser();
        } else {
            $parser = new \Intaro\HStoreBundle\HStore\HStoreParser();
        }

        try {
            $value = $parser->parse($value);
        } catch (\Exception $e) {
            throw ConversionException::conversionFailed($e->getMessage(), $this->getName());
        }

        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (is_null($value)) {
            return null;
        }
        if (!is_array($value)) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        $parts = array();
        foreach ($value as $key => $value) {
            $parts[] =
                '"' . addcslashes($key, self::ESCAPE) . '"' .
                '=>' .
                ($value === null? 'NULL' : '"' . addcslashes($value, self::ESCAPE) . '"');
        }

        return join(',', $parts);
    }

    public function getName()
    {
        return self::HSTORE;
    }
}
