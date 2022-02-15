# Composer Exclude Files Plugin

This plugin allows you to exclude files from your project's autoloader.

## Installation

The plugin can be installed globally or on a project basis. Run the following command from your terminal:

```bash
composer require themosis/composer-exclude-files
```

## Usage

In order to exclude files from being autoloaded upon a [Composer](https://getcomposer.org) operation, simply specify an `exclude-from-files` key, followed with a list of files to exclude, under the `extra` property of your `composer.json` file like so:

```json
"extra": {
    "exclude-from-files": {
        "laravel/framework": [
            "src/Foundation/helpers.php"
        ],
        "symfony/var-dumper": [
            "Resources/functions/dump.php"
        ]
    }
},
```

First defined the package name you wish to target and then pass it an array of relative paths to files to exclude from that package.

## Notes

This Composer plugin automatically exclude files from your project by looking at all its dependencies. Meaning that exclude rules can be defined in both your root package or into one of its dependencies.

## Credits

This Composer plugin has been inspired by the [mcaskill/composer-plugin-exclude-files](https://github.com/mcaskill/composer-plugin-exclude-files) package.

The plugin is a working piece of the [Themosis framework](https://framework.themosis.com).