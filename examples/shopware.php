<?php

// >jetrails_
// https://github.com/jetrails/deployer-autopilot

declare(strict_types=1);

namespace Deployer;

require "./vendor/autoload.php";
require "recipe/shopware.php";
require "recipe/autopilot.php";

// Config (Replace with your own)

set("primary_domain", "example.com");
set("cluster_user", "jrc-4d91-kn39");
set("elastic_ip", "0.0.0.0");

// Config (Standard)

set("deploy_path", "/var/www/{{primary_domain}}");
set("current_path", "/var/www/{{primary_domain}}/live");
set("writable_mode", "chmod");
set("writable_recursive", true);
set("http_user", "www-data");
set("http_group", "www-data");
set("become", "www-data");
set("keep_releases", 5);

/**
 * Writable directories.
 *
 * The following setting will enable updates and managing plugins from the
 * Shopware admin.
 *
 * ```php
 * add("writable_dirs", ["{{release_path}}"]);
 * ```
 */
add("writable_dirs", ["{{release_path}}"]);

/**
 * Release name.
 *
 * The following setting will enable date based release names.
 *
 * ```php
 * set("release_name", "{{autopilot_release_name}}");
 * ```
 *
 * The format for these release names is YYYY-MM-DD-NNN where NNN is the
 * release number.
 */
set("release_name", "{{autopilot_release_name}}");

// Hosts

host("production")
    ->set("remote_user", "{{cluster_user}}")
    ->set("hostname", "{{elastic_ip}}")
    ->set("port", "22");

// Restart services and flush cache

task("autopilot:restart:all", [
    "autopilot:restart:nginx",
    "autopilot:restart:php-fpm",
    "autopilot:restart:varnish",
    "autopilot:flush:redis-cache",
]);

// Unlock on failure and restart services on success

after("deploy:success", "autopilot:restart:all");
after("deploy:failed", "deploy:unlock");
