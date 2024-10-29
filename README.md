# Deployer — AutoPilot Recipe
> AutoPilot deployer recipe, guides, and examples

## About

Deployer is a deployment tool written in PHP with support for popular frameworks out of the box.
Deployer is used by hundreds of thousands of projects worldwide, performing over a million monthly deploys.
Deployer comes with more than 50 ready-to-use recipes for frameworks and third-party services.
You can find more information about Deployer at https://deployer.org/.

This repository is created to help you adjust your Deployer deployment configuration and deploy applications on the AutoPilot platform.

## AutoPilot Recipe

We have created a recipe that you can use to deploy your PHP applications on the AutoPilot platform.
You can install the recipe using Composer by running the following command:

```shell
composer require jetrails/deployer-autopilot
```

After installing the recipe, you can add the following code to the top of your `deploy.php` file:

```
require __DIR__ . "/vendor/jetrails/deployer-autopilot/recipe/autopilot.php";
```

This recipe, includes helpful tasks and configurations that are relevant to deploying applications on the AutoPilot platform.
Here are the tasks that are included in the recipe:

| Command                         | Description         |
|---------------------------------|---------------------|
| `autopilot:restart:php-fpm`     | Restart php-fpm     |
| `autopilot:restart:nginx`       | Restart nginx       |
| `autopilot:restart:mysql`       | Restart mysql       |
| `autopilot:restart:rabbitmq`    | Restart rabbitmq    | 
| `autopilot:restart:opensearch`  | Restart opensearch  |
| `autopilot:restart:varnish`     | Restart varnish     |
| `autopilot:flush:redis-cache`   | Flush redis-cache   |
| `autopilot:flush:redis-session` | Flush redis-session |

We also include date based releases, which can be optionally enabled in your `deploy.php` file by adding the following line:

```php
set("release_name", "{{autopilot_release_name}}");
```

This will prefix the current date to the deployer release number, for example `2024-10-04-002`.

## Examples & Guides

You can find example `deploy.php` files in the `examples` directory that integrate the AutoPilot recipe with the official application recipes.

| Application | Example                                          |
|-------------|--------------------------------------------------|
| Magento 2   | [examples/magento2.php](examples/magento2.php)   |
| Shopware    | [examples/shopware.php](examples/shopware.php)   |
| WordPress   | [examples/wordpress.php](examples/wordpress.php) |

You can find additional guides on how to setup a deployment pipeline with popular continuous deployment tools here:

| Platform       | Guide                                                        |
|----------------|--------------------------------------------------------------|
| Drone CI       | [docs/guide-drone-ci.md](docs/guide-drone-ci.md)             |
| GitHub Actions | [docs/guide-github-actions.md](docs/guide-github-actions.md) |
