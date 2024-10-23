<?php

// >jetrails_
// https://github.com/jetrails/deployer-autopilot

declare(strict_types=1);

namespace Deployer;

require "recipe/magento2.php";
require __DIR__ . "/vendor/jetrails/deployer-autopilot/recipe/autopilot.php";

// Config (Replace with your own)

set("repository", "git@github.com:example/example.git");
set("primary_domain", "howtospeedupmagento.com");
set("cluster_user", "jrc-4d91-kn39");
set("elastic_ip", "0.0.0.0");

// Config (Standard)

set("deploy_path", "/var/www/{{primary_domain}}");
set("current_path", "/var/www/{{primary_domain}}/live");
set("writable_mode", "acl");
set("writable_recursive", true);
set("http_user", "www-data");
set("http_group", "www-data");
set("keep_releases", 5);

add("shared_files", []);
add("shared_dirs", []);
add("writable_dirs", []);

// OPTIONAL: Date based release names

set("release_name", "{{autopilot_release_name}}");

// Hosts

host("production")
    ->set("remote_user", "{{cluster_user}}")
    ->set("hostname", "{{elastic_ip}}")
    ->set("port", "22");

// Temporarily disable cron

after("magento:maintenance:enable-if-needed", "magento:cron:stop");
after("magento:upgrade:db", "magento:cron:install");

// Restart services and flush cache

after("deploy:success", [
    "autopilot:restart:nginx",
    "autopilot:restart:php-fpm",
    "autopilot:restart:varnish",
    "autopilot:flush:redis-cache",
]);

// Unlock on failure

after("deploy:failed", "deploy:unlock");
