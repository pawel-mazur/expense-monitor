<?php

require 'vendor/autoload.php';
require 'vendor/deployer/deployer/recipe/symfony3.php';

use Symfony\Component\Console\Input\InputOption;

set('console', 'bin/console');

option('force', null, InputOption::VALUE_NONE, 'Force operation');

localServer('local')
    ->env('env', 'prod')
    ->env('console', 'bin/console');

task('composer:install', function () {
    run(env('bin/composer').' install');
})->setPrivate();

task('database:setup', function () {
    if (false === input()->getOption('force') && false === askConfirmation('Are you sure?')) throw new RuntimeException('');
    run('{{console}} doctrine:database:drop --force --env={{env}}');
    run('{{console}} doctrine:database:create --env={{env}}');
    run('{{console}} doctrine:schema:create --env={{env}}');
})->desc('Setup database schema');

task('database:update', function () {
    if (false === input()->getOption('force') && false === askConfirmation('Are you sure?')) throw new RuntimeException();
    run('{{console}} doctrine:migration:migrate -n --env={{env}}');
    run('{{console}} doctrine:schema:update -n --env={{env}} --force');
})->desc('Update database schema');

task('assets:install', function () {
    run('{{console}} assets:install --env={{env}}');
})->desc('Install assets')->setPrivate();

task('cache:clear', function() {
    run('{{console}} cache:clear --env={{env}}');
})->desc('Clear application cache');

task('cs:fix', function () {
    run('bin/php-cs-fixer fix');
})->desc('Run cs-fix command');

task('build:dev', ['database:setup', 'assets:install', 'cache:clear'])->desc('Build dev environment');
task('build:prod', ['database:update', 'cache:clear'])->desc('Build prod environment');
