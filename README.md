# autopilot-deployer-example
AutoPilot Deployment example repo using Deployer.org application

# WORK IN PROGRESS

What is Deployer? <BR>
A deployment tool written in PHP with support for popular frameworks out of the box. Deployer is used by hundreds of thousands of projects worldwide, performing over a million monthly deploys. Deployer comes with more than 50 ready-to-use recipes for frameworks and third-party services.
  
This repository is created to help you adjust your Deployer.org deployment processes and deploy applications on the AutoPilot platform.
  
To get started, first, we need to install the Deployer application as per https://deployer.org/docs/7.x/installation guidelines.
```
curl -LO https://deployer.org/deployer.phar
mv deployer.phar /usr/local/bin/dep
chmod +x /usr/local/bin/dep
```

Now, we can cd into the project and run the following command:
```
dep init
```

Deployer will ask you a few questions, and after finishing, you will have a deploy.php or deploy.yaml file. This is our deployment recipe. It contains hosts and tasks and requires other recipes. All framework recipes that come with Deployer are based on this common recipe.
https://deployer.org/docs/7.x/recipe/common

Deployer.org already covered Magento 2 recipe, and it can be downloaded/reviewed here:
https://github.com/deployphp/deployer/blob/master/recipe/magento2.php
  
How to install this package:
```
composer require jetrails/autopilot-deployer-example --dev
```

How to use it?
After installing it, you can add the line below after the namespace and run dep to check:

```
// AutoPilot Deployer recipe addon
require __DIR__ . '/vendor/jetrails/autopilot-deployer-example/autopilot.php';
```

This recipe, when installed automatically, will clean all caches after the deployment success, but if you want to restart all services, add these into the bottom:

```
// Extra commands definition goes here.
```

For example:
```
<?php

namespace Deployer;
// AutoPilot Recipe addon
require __DIR__ . '/vendor/jetrails/autopilot-deployer-example/autopilot.php';

// Application name
set('application', 'MyMagentoApp');

// Project repository
set('repository', 'git@github.com:yourusername/yourmagento2repo.git');
set('default_stage', 'production');

// Set the default branch to deploy
set('branch', 'master');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Shared files/dirs between releases
// grab from https://github.com/deployphp/deployer/blob/master/recipe/magento2.php anything more useful.
add('shared_files', ['app/etc/env.php']);
add('shared_dirs', [
    'var/',
    'pub/media',
    'pub/static'
]);

// Writable dirs by a web server
add('writable_dirs', [
    'var',
    'pub/static',
    'pub/media'
]);

set('allow_anonymous_stats', false);

// Project Configurations
host('production')
    ->hostname('<Hostname/IP-address/from AutoPilot Panel')
    ->user('<username defined in the AutoPilot panel>')
    ->port(22)
    ->set('deploy_path', '/var/www/')
    ->set('branch', 'master')
    ->stage('production');

// We can define after commands that will be pushed
// Main deployment task sequence
desc('Deploy Magento 2 application');
task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'deploy:clear_paths',
    'magento:setup:upgrade',
    'magento:compile',
    'magento:deploy:static-content',
    'magento:cache:flush',
    'deploy:publish',
]);

// [Optional] If deploy fails, automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Custom Magento tasks

desc('Run Magento setup upgrade');
task('magento:setup:upgrade', function () {
    run('php {{release_path}}/bin/magento setup:upgrade');
});

desc('Compile Magento code');
task('magento:compile', function () {
    run('php {{release_path}}/bin/magento setup:di:compile');
});

desc('Deploy Magento static content');
task('magento:deploy:static-content', function () {
    run('php {{release_path}}/bin/magento setup:static-content:deploy');
});

desc('Flush Magento cache');
task('magento:cache:flush', function () {
    run('php {{release_path}}/bin/magento cache:flush');
});
```

Summary of all available commands:

| Command | Description |
|----------|-------------|
(we have to explain each command and what it does)

Useful commands for deploy.php file:
```
// AutoPilot requires absolute symlinks
set('use_relative_symlink', false);
```
