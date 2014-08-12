namespace HStore;

class HStoreParser
{
    private p;
    private quoted;

    private function charAfterSpaces(const string! str) -> char|boolean
    {
        let this->p = intval(this->p) + strspn(str, " \t\r\n", this->p);

        if this->p >= str->length() {
            return false;
        }

        return substr(str, this->p, 1);
    }

    private function readString(const string! str) -> string
    {
        var c, len, value, m = null;

        let c = substr(str, this->p, 1);

        // Unquoted string.
        if c != "\"" {
            let this->quoted = false;
            let len = strcspn(str, " \r\n\t,=>", this->p);
            let value = substr(str, this->p, len);
            let this->p = intval(this->p) + len;

            return stripcslashes(value);
        }

        // Quoted string.
        let this->quoted = true;
        if (preg_match("/\" ((?" . ">[^\"\\\\]+|\\\\.)*) \"/Asx", str, m, 0, this->p)) {
            let value = stripcslashes(m[1]);
            let this->p = intval(this->p) + strlen(m[0]);

            return value;
        }

        throw new Exception\ConversionException(str);
    }

    public function parse(string! str) -> array
    {
        uint len;
        var c, key, value, buffer;
        array result = [];

        let len = str->length();
        let this->p = 0;

        loop {
            let c = this->charAfterSpaces(str);
            // End of string.
            if c === false {
                break;
            }

            // Next element.
            if (c == ",") {
                let this->p = intval(this->p) + 1;
                continue;
            }

            // Key.
            let key = this->readString(str);
            // '=>' sequence.
            this->charAfterSpaces(str);
            let buffer = substr(str, this->p, 2);
            if buffer != "=>" {
                throw new Exception\ConversionException(str);
            }

            let this->p = intval(this->p) + 2;
            this->charAfterSpaces(str);

            // Value.
            let value = this->readString(str);
            if this->quoted === false && value === "NULL" {
                let result[key] = null;
            } else {
                let result[key] = value;
            }
        }

        if (this->p != len) {
            throw new Exception\ConversionException(str);
        }

        return result;
    }
}