DoctrineExtensionsTablePrefixerBundle
=====================================
The table prefixer reads the bundle's config file to add prefixes and schema to
the beginning of doctrine table names.  Prefixes are add to raw table names so
if prefix is set to 'prefix\_'

    object
    schema.object

become

    prefix_student
    schema.prefix_student

Schema are added before table names so

    object


becomes

    schema.object

The prefixes and schema are configured in config.yml:

    mesd_doctrine_extensions_table_prefixer:
        prefixed_bundles:
            default:
                schema: def_schema
                prefix: def_prefix
            Bundle1:
                schema: ~
                prefix: ~
            Bundle2:
                schema: ~
            Bundle3:
                prefix: ~

The tildes (~) mean do _not\_ use defaults for this bundle:
* Bundle1 will use no defaults
* Bundle2 will use prefix defaults
* Bundle3 will use schema defaults
* Bundle4 (not specified above) will use both schema and prefix defaults

The defaults will override the schema (only) set in bundle-specific orm files
unless tilde is specified.

A special default schema of blank will remove any bundle-specific schema.

    mesd_doctrine_extensions_table_prefixer:
        prefixed_bundles:
            default:
                schema: blank
                prefix:
            Bundle1:
                schema: ~
                prefix: ~
            Bundle2:
                schema: ~
            Bundle3:
                prefix: ~

Leads to

    bundle_schema.object

becoming

    object

A good way to test prefixes is:

    app/console doctrine:schema:update --dump-sql


TablePrefixer Bundle Install and Config
=======================================

Include code for bandcamp require in composer.json:

    "require": {
    ...
        "mesd/bandcamp": "dev-master",
    ...
    },

Also register in the AppKernel.php, e.g.:

    public function registerBundles()
    {
        $bundles = array(

        ...

            new Mesd\DoctrineExtensions\TablePrefixerBundle\MesdDoctrineExtensionsTablePrefixerBundle(),

        ...

        );
