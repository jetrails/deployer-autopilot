<?php

declare(strict_types=1);

namespace Deployer;

use Deployer\Task\Context;

// General Config

set("bin/cluster", "/opt/jrc/bin/cluster");

// Date Based Release Name
// Usage: set("release_name", "{{autopilot_release_name}}");

set("autopilot_release_name", function () {
    return within("{{deploy_path}}", function () {
        $today = run("date '+%Y-%m-%d'");
        $latest = run("ls {{deploy_path}}/releases | sort | tail -n1 || $today-000");
        $parts = explode("-", $latest);
        $last_build = array_pop($parts);
        $last_date = implode("-", $parts);
        $next_build = 1;
        if ($today == $last_date) {
            $next_build = intval($last_build) + 1;
        }
        return sprintf("%s-%03d", $today, $next_build);
    });
});

// Wrap tasks to run as web user

function becomeHttpUser()
{
    if (currentHost() instanceof Localhost) {
        set("shell", "cd /var/www && sudo -u {{http_user}} bash -s");
    } else {
        set("shell", "cd /var/www && sudo -u {{http_user}} bash -ls");
    }
}

function unbecomeHttpUser()
{
    if (currentHost() instanceof Localhost) {
        set("shell", "bash -s");
    } else {
        set("shell", "bash -ls");
    }
}

function runAsWebUser($patterns = [])
{
    foreach (Deployer::get()->tasks as &$task) {
        foreach ($patterns as &$pattern) {
            $new_pattern = preg_replace("/:[*]{2}.*/", ":.+", $pattern);
            $new_pattern = "^" . preg_replace("/:[*]{1}/", ":[^:]+", $new_pattern) . "$";
            if (preg_match("/$new_pattern/", $task->getName())) {
                Deployer::get()->tasks->remove($task->getName());
                task($task->getName(), function () use (&$task) {
                    $name = $task->getName();
                    $http_user = get("http_user");
                    Deployer::get()->output->writeln("<info>user</info> $http_user");
                    becomeHttpUser();
                    $task->run(Context::get());
                    unbecomeHttpUser();
                });
            }
        }
    }
}

// Restart Tasks

desc("Restart php-fpm");
task("autopilot:restart:php-fpm", function () {
    writeln("<info>info</info> restarting php-fpm");
    run("{{bin/cluster}} exec --quiet --role web -- sudo systemctl restart php-fpm");
});

desc("Restart nginx");
task("autopilot:restart:nginx", function () {
    writeln("<info>info</info> restarting nginx");
    run("{{bin/cluster}} exec --quiet --role web -- sudo systemctl restart nginx");
});

desc("Restart mysql");
task("autopilot:restart:mysql", function () {
    writeln("<info>info</info> restarting mysql");
    run("{{bin/cluster}} exec --quiet --role database -- sudo systemctl restart mysql");
});

desc("Restart rabbitmq");
task("autopilot:restart:rabbitmq", function () {
    writeln("<info>info</info> restarting rabbitmq");
    run("{{bin/cluster}} exec --quiet --role queue -- sudo systemctl restart rabbitmq");
});

desc("Restart opensearch");
task("autopilot:restart:opensearch", function () {
    writeln("<info>info</info> restarting opensearch");
    run("{{bin/cluster}} exec --quiet --role search -- sudo systemctl restart opensearch");
});

desc("Restart varnish");
task("autopilot:restart:varnish", function () {
    writeln("<info>info</info> restarting varnish");
    run("{{bin/cluster}} exec --quiet --role varnish -- sudo systemctl restart varnish");
});

// Flush Tasks

desc("Flush redis-cache");
task("autopilot:flush:redis-cache", function () {
    writeln("<info>info</info> flushing redis-cache");
    run("bash /opt/jrc/bin/redis-cache FLUSHALL");
});

desc("Flush redis-session");
task("autopilot:flush:redis-session", function () {
    writeln("<info>info</info> flushing redis-session");
    run("bash /opt/jrc/bin/redis-session FLUSHALL");
});
