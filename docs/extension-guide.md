# Extension Guide

The application supports 3 methods of customization:

 1. Built-in extensions space
 2. Custom space
 2. Composer integration

On the latter, although this application is dependency free, it does provide Composer support as a means for community
friendly development. Refer to the included `composer.json` to see how to include this package with your own project.

The rest of this guide will explain how to use the built-in extension workspace.

## Creating an Extension

To create an extension using the built-in workspace, you must place your code into `/extensions`, and all namespaces
must begin with `LowlyPHP`. For example, all of the following class names are valid:

 - extensions/CustomModule/Class.php -> `LowlyPHP\CustomModule\Class`
 - extensions/SearchAPI/Engine/Default.php -> `LowlyPHP\SearchAPI\Engine\Default`
 - extensions/Catalog/Product/Type/Virtual.php -> `LowlyPHP\Catalog\Product\Type\Virtual`

This is made possible through the default configuration:

    {
        "paths": {
            "LowlyPHP": "./src",
            "LowlyPHP\\*": "./extensions"
        }
    }

Similar to Composer-style path mapping, the `paths` configuration block allows you to map a specific namespace to any
directory (relative or absolute). A wildcard pattern is supported to match any directory. This default wildcard pattern
is what allows either of the 3 class path/name combinations above to be found.

If you want to manage your extensions from a different directory, add an additional map entry to the `paths` block; ex:

    {
        "paths": {
            "Vendor\\ExtensionName": "/custom/path"
        }
    }
