<?php

namespace Intaro\HStoreBundle\HStore;

use Intaro\HStoreBundle\HStore\Exception\ConversionException;

class HStoreParser
{
    /**
     * Move $p to skip spaces from position $p of the string.
     * Return next non-space character at position $p or
     * false at the string end.
     *
     * @param string $str
     * @param int $p
     * @return string
     */
    private function charAfterSpaces($str, &$p)
    {
        $p += strspn($str, " \t\r\n", $p);

        return substr($str, $p, 1);
    }

    private function readString($str, &$p, &$quoted)
    {
        $c = substr($str, $p, 1);

        // Unquoted string.
        if ($c != '"') {
            $quoted = false;
            $len = strcspn($str, " \r\n\t,=>", $p);
            $value = substr($str, $p, $len);
            $p += $len;

            return stripcslashes($value);
        }

        // Quoted string.
        $quoted = true;
        $m = null;
        if (preg_match('/" ((?' . '>[^"\\\\]+|\\\\.)*) "/Asx', $str, $m, 0, $p)) {
            $value = stripcslashes($m[1]);
            $p += strlen($m[0]);

            return $value;
        }

        throw new ConversionException($str);
    }

    public function parse($str)
    {
        $len = strlen($str);
        $p = 0;

        $result = array();
        $quoted = null;

        while (true) {
            $c = $this->charAfterSpaces($str, $p);

            // End of string.
            if ($c === false) {
                break;
            }

            // Next element.
            if ($c == ',') {
                $p++;
                continue;
            }

            // Key.
            $key = $this->readString($str, $p, $quoted);

            // '=>' sequence.
            $this->charAfterSpaces($str, $p);
            if (substr($str, $p, 2) != '=>') {
                throw new ConversionException($str);
            }

            $p += 2;
            $this->charAfterSpaces($str, $p);

            // Value.
            $value = $this->readString($str, $p, $quoted);
            if (!$quoted && $value === 'NULL') {
                $result[$key] = null;
            } else {
                $result[$key] = $value;
            }
        }

        if ($p != $len) {
            throw new ConversionException($str);
        }

        return $result;
    }
}
