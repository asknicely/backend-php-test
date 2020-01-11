<?php

use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;

$app = new Application;

$app->register(new DoctrineServiceProvider, array(
    "db.options" => array(
        "driver" => "pdo_sqlite",
        "path" => "/path/to/sqlite.db",
    ),
));

$app->register(new DoctrineOrmServiceProvider, array(
    "orm.proxies_dir" => "/path/to/proxies",
    "orm.em.options" => array(
        "mappings" => array(
            // Using actual filesystem paths
            array(
                "type" => "annotation",
                "namespace" => "Foo\Entities",
                "path" => __DIR__."/src/Foo/Entities",
            ),
            array(
                "type" => "xml",
                "namespace" => "Bat\Entities",
                "path" => __DIR__."/src/Bat/Resources/mappings",
            ),
            // As of 1.1, you can also use the simplified
            // XML/YAML driver (Symfony2 style)
            // Mapping files can be named like Foo.orm.yml
            // instead of Baz.Entities.Foo.dcm.yml
            array(
                "type" => "simple_yml",
                "namespace" => "Baz\Entities",
                "path" => __DIR__."/src/Bat/Resources/config/doctrine",
            ),
            // Using PSR-0 namespaceish embedded resources
            // (requires registering a PSR-0 Resource Locator
            // Service Provider)
            array(
                "type" => "annotation",
                "namespace" => "Baz\Entities",
                "resources_namespace" => "Baz\Entities",
            ),
            array(
                "type" => "xml",
                "namespace" => "Bar\Entities",
                "resources_namespace" => "Bar\Resources\mappings",
            ),
        ),
    ),
));
