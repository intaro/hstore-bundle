#include <string>
#include <phpcpp.h>

class HStoreParser : public Php::Base
{
private:
    unsigned int p;
    bool quoted;

    void charAfterSpaces(std::string str)
    {
        unsigned int n = str.length();
        for (; this->p < n; this->p++) {
            if (
                str[this->p] != '\t' &&
                str[this->p] != '\r' &&
                str[this->p] != '\n' &&
                str[this->p] != ' '
            ) {
                return;
            }
        }

        this->p = n;
    }

    std::string readString(std::string str)
    {
        this->quoted = str[this->p] == '"';

        unsigned int n = str.length();

        if (!this->quoted) {
            int i = 0;
            for (; this->p + i < n; i++) {
                if (
                    str[this->p + i] == '\t' ||
                    str[this->p + i] == '\r' ||
                    str[this->p + i] == '\n' ||
                    str[this->p + i] == ',' ||
                    str[this->p + i] == '=' ||
                    str[this->p + i] == '>' ||
                    str[this->p + i] == ' '
                ) {
                    break;
                }
            }

            std::string value = str.substr(this->p, i);
            this->p += i;

            return Php::call("stripcslashes", value);
        }

        // " symbol
        this->p++;

        int i = 0;
        for (; this->p + i < n; i++) {
            if (str[this->p + i] == '"' && str[this->p + i - 1] != '\\') {
                break;
            }
        }

        std::string value = str.substr(this->p, i);
        this->p += i + 1;

        return Php::call("stripcslashes", value);
    }

public:

    Php::Value parse(Php::Parameters &params)
    {
        std::string str = params[0];
        unsigned int len = str.length();
        this->p = 0;

        std::string key, value, buffer;

        Php::Value result;
        while (true) {
            this->charAfterSpaces(str);
            if (this->p == len) {
                break;
            }

            if (str[this->p] == ',') {
                this->p++;
                continue;
            }

            key = this->readString(str);

            this->charAfterSpaces(str);
            buffer = str.substr(this->p, 2);
            if (buffer != "=>") {
                throw Php::Exception("Can not parse hstore string.");
            }

            this->p += 2;
            this->charAfterSpaces(str);

            // value
            value = this->readString(str);
            if (!this->quoted && value == "NULL") {
                result[key] = nullptr;
            } else {
                result[key] = value;
            }
        }

        if (this->p != len) {
            throw Php::Exception("Can not parse hstore string.");
        }

        return result;
    }
};

/**
 *  tell the compiler that the get_module is a pure C function
 */
extern "C" {

    /**
     *  Function that is called by PHP right after the PHP process
     *  has started, and that returns an address of an internal PHP
     *  strucure with all the details and features of your extension
     *
     *  @return void*   a pointer to an address that is understood by PHP
     */
    PHPCPP_EXPORT void *get_module()
    {
        // static(!) Php::Extension object that should stay in memory
        // for the entire duration of the process (that's why it's static)
        static Php::Extension extension("hstorecpp", "1.0");

        Php::Namespace hstoreNamespace("HStoreCpp");

        Php::Class<HStoreParser> parser("HStoreParser");
        parser.method("parse", &HStoreParser::parse, {
            Php::ByVal("str", Php::Type::String, true)
        });

        hstoreNamespace.add(std::move(parser));

        extension.add(std::move(hstoreNamespace));

        // return the extension
        return extension;
    }
}
