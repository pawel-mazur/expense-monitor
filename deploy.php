<?php

namespace Deployer;

require 'vendor/autoload.php';
require 'vendor/deployer/deployer/recipe/symfony3.php';
include 'hosts.php';

use Symfony\Component\Console\Input\InputOption;

set('bin/console', 'bin/console');

set('repository', 'git@github.com:pawel-mazur/spending-monitor.git');

localhost('local')
    ->set('release_path', '.')
    ->set('deploy_path', '.')
;

option('force', null, InputOption::VALUE_NONE, 'Force operation');

task('confirm', function () {
    if (false === input()->getOption('force') && false === askConfirmation('Are you sure?')) exit;
})->desc('Confirm operation')->once();

// Composer
task('composer:install', function () {
    run('{{ bin/composer }} install');
})->desc('Composer install');

// Database
task('database:setup', function () {
    run('{{ bin/console }} doctrine:database:drop --force');
    run('{{ bin/console }} doctrine:database:create');
    run('{{ bin/console }} doctrine:schema:create');
})->desc('Setup database schema')->addBefore('confirm');

task('database:update', function () {
    run('{{ bin/console }} doctrine:migration:migrate --no-interaction');
})->desc('Update database schema')->addBefore('confirm');

task('database:fixtures', function (){
    run('{{ bin/console }} doctrine:fixtures:load --no-interaction');
})->desc('Load fixtures');
after('database:setup', 'database:fixtures');

// Assets
task('assets:install', function () {
    run('{{ bin/console }} assets:install --env=prod --symlink');
    run('{{ bin/console }} assets:install --env=dev --symlink');
})->desc('Install assets');

// Cache
task('cache:clear', function() {
    run('{{ bin/console }} cache:clear --env=prod');
    run('{{ bin/console }} cache:clear --env=dev');
})->desc('Clear application cache');

// CS
task('cs:fix', function () {
    run('bin/php-cs-fixer fix');
})->desc('Run cs-fix command');

// Build
task('build:prod', [
    'confirm',
    'composer:install',
    'cache:clear',
    'assets:install',
    'database:update'
])->desc('Build prod environment')->addBefore('confirm');

task('build:dev', [
    'confirm',
    'composer:install',
    'cache:clear',
    'assets:install',
    'database:setup'
])->desc('Build dev environment')->addBefore('confirm');
