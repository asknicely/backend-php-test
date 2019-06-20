<?php

/**
 * Command line script to run Migrations
 * Inspired by phar-cli-stup.php
 */
 
use Symfony\Component\Console;
use Symfony\Component\Yaml\Yaml;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Migrations\MigrationsVersion;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\DBAL\Migrations\Tools\Console\Command as MigrationsCommand;
 
require (__DIR__ . '/../vendor/autoload.php');
 
// Set current directory to application root so we can find config files
chdir(__DIR__ . '/..');
 
// DB Connection
$dbConfigFilePath = getcwd() . '/config/config.yml';
$dbConfig = Yaml::parse(file_get_contents($dbConfigFilePath));
$connection = DriverManager::getConnection($dbConfig['database']);

// Instantiate console application
$cli = new Console\Application('Doctrine Migrations', MigrationsVersion::VERSION());
$cli->setCatchExceptions(true);

 
$helperSet = new Console\Helper\HelperSet();
$helperSet->set(new Console\Helper\DialogHelper(), 'dialog');
$helperSet->set(new ConnectionHelper($connection), 'db');
$cli->setHelperSet($helperSet);
 
// Add Migrations commands
$commands = array();
$commands[] = new MigrationsCommand\ExecuteCommand();
$commands[] = new MigrationsCommand\GenerateCommand();
$commands[] = new MigrationsCommand\LatestCommand();
$commands[] = new MigrationsCommand\MigrateCommand();
$commands[] = new MigrationsCommand\StatusCommand();
$commands[] = new MigrationsCommand\VersionCommand();
 
// remove the "migrations:" prefix on each command name
foreach ($commands as $command) {
    $command->setName(str_replace('migrations:', '', $command->getName()));
}
$cli->addCommands($commands);
 
// Run!
$cli->run();