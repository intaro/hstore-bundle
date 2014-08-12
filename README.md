# HStoreBundle

PostgreSQL module `hstore` allows to store sets of key/value pairs within a single PostgreSQL value. More about it [here](http://www.postgresql.org/docs/current/static/hstore.html).

The HStoreBundle contains DBAL type `hstore` and registers Doctrine type `hstore`.

## Installation

HStoreBundle requires Symfony 2.3 or higher.

Require the bundle in your `composer.json` file:

````json
{
    "require": {
        "intaro/hstore-bundle": "~0.0.2",
    }
}
```

Register the bundle in `AppKernel`:

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        //...

        new Intaro\HStoreBundle\IntaroHStoreBundle(),
    );

    //...
}
```

Install the bundle:

```
$ composer update intaro/hstore-bundle
```

### Installing the PHP extension

The PHP extension is optional but as it brings some nice performance improvements, you might want to install it in your production environment.

The extension is written in [Zephir](http://zephir-lang.com) thats why you should [install Zephir](http://zephir-lang.com/install.html) before extension compiling.

```bash
cd path/to/Intaro/HStoreBundle/Resources/zephir
zephir install
```

Finally, enable the extension in your `php.ini` configuration file:

```ini
extension = hstore.so # For Unix systems
```
