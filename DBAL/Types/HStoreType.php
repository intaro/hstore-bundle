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
    private static $parser;

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

        if (!self::$parser) {
            // php extension
            if (class_exists('\HStore\HStoreParser')) {
                self::$parser = new \HStore\HStoreParser();
            } else {
                self::$parser = new \Intaro\HStoreBundle\HStore\HStoreParser();
            }
        }

        try {
            $value = self::$parser->parse($value);
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
