# Configuring the Application

This guide describes how to configure or customize the application.

## Defaults

The default configuration is given in `config.json.dist`. This file must be present to run the application.

## Customizations

There are three ways to customize:

 1. Edit `config.json.dist` directly (not recommended)
 2. Copy `config.json.dist` to `config.json` and edit (recommended)
 3. Use environment variable `CONFIG_PATH` to specify a custom path (advanced)
 
Default configuration is _merged_ with customizations. Therefore, it is possible to extend the defaults by specifying
only the changes you need. For example, the following is a valid `config.json` file:

    {
        "providers": {
            "LowlyPHP\\Service\\Catalog\\ProductInterface": {
                "schema": {
                    "source": "renamed_product_table"
                }
            }
        }
    }

This customization will cause an existing product table to be renamed to "renamed_product_table."

## Service Replacement

The `providers` configuration block specifies the implementing classes (providers) for application services. The default
providers can be replaced when you want to extend functionality of core features.

For example, consider that you want to build a new storage driver. The default driver supports MySQL, and you can find
its preference in the default configuration; ex:

    {
        "providers": {
            "LowlyPHP\\Service\\Resource\\StorageInterface": {
                "type": "LowlyPHP\\Provider\\Resource\\Storage\\Driver\\Pdo\\Mysql"
            }
        }
    }

Replace the `type` property with your own class. Note that service replacement expects you to implement the interface.
