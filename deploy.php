<?php
namespace Deployer;

require 'recipe/symfony4.php';

// Project name
set('application', 'habit-bot');

// Project repository
set('repository', 'git@github.com:bifidokk/habit-bot.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

set('shared_files', [
    '.env',
]);

// Shared files/dirs between deploys
add('shared_dirs', ['vendor', 'var/log']);

// Writable dirs by web server
set('writable_dirs', ['var']);


// Hosts
host('bifidokk.ru')
    ->hostname('116.203.83.139')
    ->user('deployer')
    ->set('deploy_path', '/var/www/habit-bot');

set('http_user', 'www-data');

// Tasks
task('build', function () {
    run('cd {{release_path}} && build');
});

task('deploy:assets:install', function () {
    run('{{bin/console}} assets:install {{release_path}}/public');
})->desc('Install bundle assets');

desc('Deploy project');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:vendors',
    'deploy:writable',
    'deploy:cache:clear',
    'deploy:assets:install',
    'deploy:cache:warmup',
    'database:migrate',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

