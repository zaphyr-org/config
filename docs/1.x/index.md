# Table of contents

- [Loading files](#loading-files)
- [Loading files from a directory](#loading-files-from-a-directory)
- [Has values](#check-if-a-variable-exists)
- [Getting values](#getting-values)
- [Get all configuration values](#get-all-configuration-values)
- [Supported configuration reader](#supported-configuration-reader)
- [Create custom reader](#create-custom-reader)
- [Create custom replacer](#create-custom-replacer)

# Loading files

Files can be loaded via the `load()` method, or by direct instantiation:

```php
use Zaphyr\Config\Config;

$items = [
    'app' => 'app.php',

    'database' => 'database.php',
];

// Load files via load() method
$config = new Config();
$config->load($items);

// Load files by direct instantiation
$config = new Config($items);
```

**Warning:** Do not include untrusted configuration in PHP format. It could contain and execute malicious code!

## Loading files from a directory

It is also possible to load a complete directory with configuration files.
This can be done either via the `load()` method, or by direct instantiation:

```php
use Zaphyr\Config\Config;

// Load directory files via loadDir() method
$config = new Config();
$config->loadDir(['./config']);

// Load directory files by direct instantiation
$config = new Config(['./config']);
```

>**Note:** Files are parsed and loaded depending on the file extension. When loading a directory,
the path is `glob`ed and files are loaded in by name alphabetically.

If a directory is loaded, the filename is the namespace to call the respective configuration:

```php
use Zaphyr\Config\Config;

// Directory structure
config/
    | -- app.php
    | -- database.json

// Load directory
$config = new Config(['./config']);

// Get configuration values from app.php
$config->get('app');

// Get configuration values from database.json
$config->get('database');
```

**Warning:** Do not include untrusted configuration in PHP format. It could contain and execute malicious code!

## Check if a variable exists

If you want to check if a configuration variable exists, use the `has()` method:

```php
$config->has('app.debug');
```

## Getting values

Getting configuration values can be done by using the `get()` method:

```php
// Get value using key
$app = $config->get('app');

// Get value using nested key
$host = $config->get('database.host');

// Get a value with a default fallback
$ttl = $config->get('app.timeout', 3000);
```

Maybe you want to use an env variable in your configuration file. There is also a solution for that.

```php
// Your configuration file looks like this (e.g. database.php)
<?php

return [
    'host' => '%env:DB_HOST%'
];
```

If you have previously set an env variable e.g. via `putenv`,
the placeholder `%env:DB_HOST%` will be replaced by the actual env variable.

You can also create your own replacers for your configuration files.
Read more: [Create custom replacers](#create-custom-replacers).

## Get all configuration values

To get all the loaded configuration items, simply use the `toArray()` method:

```php
$config->toArray();
```

## Supported configuration reader

| File extension     | Reader class                         |
|--------------------|--------------------------------------|
| `*.php`            | `Zaphyr\Config\Readers\ArrayReader` |
| `*.ini`            | `Zaphyr\Config\Readers\IniReader`   |
| `*.json`           | `Zaphyr\Config\Readers\JsonReader`  |
| `*.xml`            | `Zaphyr\Config\Readers\XmlReader`   |
| `*.yml` / `*.yaml` | `Zaphyr\Config\Readers\YamlReader`  |

## Create custom reader

If the offered configuration readers are not sufficient, own readers can be created:

```php
use Zaphyr\Config\Config;
use Zaphyr\Config\Readers\AbstractReader;

class MySuperCustomReader extends AbstractReader
{
    /**
     * {@inheritdoc}
     */
    public function read(): array
    {
        // Your custom reader logic
    }
}

// Add new readers by direct instantiation
$readers = ['custom' => MySuperCustomReader::class];
$config = new Config(['./config'], $readers);

// Add a new reader with the addReader method
$config->addReader('custom', MySuperCustomReader::class);
```

>**Note:** New reader instances must always be added before the first use of the `load()` method.
Otherwise, the `load()` method throws an error because it does not yet know the new reader!

## Create custom replacer

You can also create your own replacer for your configuration files:

```php
use Zaphyr\Config\Config;
use Zaphyr\Confg\Contracts\ReplacerInterface;

class MySuperCustomReplacer implements ReplacerInterface
{
    /**
     * {@inheritdoc}
     */
    public function replace(string $value): string
    {
        // Your custom replacer logic
    }
}

// Add new replacers by direct instantiation
$replacers = ['custom' => MySuperCustomReplacer::class];
$config = new Config(['./config'], null, $replacers);

// Add a new replacer with the addReplacer method
$config->addReplacer('custom', MySuperCustomReplacer::class);

// You can now use your new replacer in your configuration file
<?php

return [
    'config' => '%custom:value%'
];

```

>**Note:** New replacers instances must always be added before the first use of the `load()` method.
Otherwise, the `load()` method throws an error because it does not yet know the new replacer!
