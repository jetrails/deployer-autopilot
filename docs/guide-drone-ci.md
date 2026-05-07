# Guide — Drone CI + Deployer PHP + AutoPilot

The purpose of this guide is to show you how to use Drone CI to deploy your PHP applications on JetRails AutoPilot using Deployer PHP.
This guide is meant to be a starting point and may require additional configuration to fit your specific use case.
For this example we will trigger whenever a push occurs on the master branch.
The following assumptions have been made:

- A valid `deploy.php` file exists in the root of your repository, customized to fit your deployment
- You have a set of SSH keys that will be used to authenticate with your AutoPilot deployment
- You whitelisted the outbound IP address of your Drone CI server on your AutoPilot deployment
- You whitelisted the ssh key on your AutoPilot deployment

There are three steps to this example pipeline.
In the first step we take in the deploy key and set up the SSH configuration inside a temporary volume.
In the second step we install the PHP dependencies using Composer inside a temporary volume.
In the final step we mount both the temporary volumes that were created in the last two steps to your codebase and deploy using the `deploy` command.

First, make sure that Deployer and the AutoPilot recipe are installed as production dependencies. Since the pipeline runs `composer install --no-dev`, these packages must be in `require` and not `require-dev`:

```bash
composer require deployer/deployer jetrails/deployer-autopilot
```

We will want to make sure that the `deploy.php` is reading the `SSH_USER` and `SSH_HOST` environment variables. Make sure your `deploy.php` file looks something like this when defining the `cluster_user` and `elastic_ip` variables:

```php
set("cluster_user", getenv("SSH_USER"));
set("elastic_ip", getenv("SSH_HOST"));
```

You may choose to skip this step and hardcode these values in your `deploy.php` file, but it is not recommended.

Next, we can save the following `.drone.yml` file in the root of your project's repository:

```yaml
---

kind: pipeline
type: kubernetes
name: production-pipeline

platform:
  os: linux
  arch: amd64

trigger:
  branch:
    - master
  event:
    - push

volumes:
  - name: ssh
    temp: {}
  - name: vendor
    temp: {}

steps:

  - name: setup-ssh-key
    image: alpine:latest
    volumes:
      - name: ssh
        path: /root/.ssh
    environment:
      SSH_PRIVATE_KEY:
        from_secret: ssh_private_key
      SSH_HOST:
        from_secret: ssh_host
    commands:
      - apk add --no-cache --quiet openssh-client
      - echo "$SSH_PRIVATE_KEY" > /root/.ssh/id_rsa
      - ssh-keyscan -H "$SSH_HOST" >> /root/.ssh/known_hosts
      - chmod -R go-rwx /root/.ssh

  - name: install-php-dependencies
    image: composer:latest
    volumes:
      - name: vendor
        path: /drone/src/vendor
    commands:
      - composer install --no-interaction --prefer-dist --no-dev

  - name: deploy
    image: deployphp/deployer
    volumes:
      - name: vendor
        path: /drone/src/vendor
      - name: ssh
        path: /root/.ssh
    environment:
      SSH_USER:
        from_secret: ssh_user
      SSH_HOST:
        from_secret: ssh_host
    commands:
      - php /bin/deployer.phar deploy
```

> **Note**: If the "ssh-keyscan" command fails, it is probably because the IP address is not reachable.
> Remember to whitelist the outbound IP address of your Drone CI server.
> If you are having issues finding the outbound IP address, you can always run `curl ip.jetrails.com` as one of the commands and check the output.

The last step is to enable the repository in Drone CI and set the following secrets:

- `ssh_user`: The cluster user of your AutoPilot deployment
- `ssh_host`: The elastic IP address of your AutoPilot deployment
- `ssh_private_key`: The deploy key that will be used to authenticate with your AutoPilot deployment
