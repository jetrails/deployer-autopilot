# Guide — GitHub Actions + Deployer PHP + AutoPilot

The purpose of this guide is to show you how to use GitHub Actions to deploy your PHP applications on JetRails AutoPilot using Deployer PHP.
This guide is meant to be a starting point and may require additional configuration to fit your specific use case.
For this example we will trigger whenever a push occurs on the master branch.
GitHub's hosted runners use a large and frequently changing pool of IP addresses, making it impractical to whitelist them all on your AutoPilot deployment.
The recommended approach is to use a self-hosted runner whose outbound IP address you control.
If you are using the JetRails AutoPilot K3s template, you can set up a self-hosted runner using Actions Runner Controller (ARC) by following [this guide](https://docs.autopilot.jetrails.com/guides/self-hosted-github-action-runners/).

The following assumptions have been made:

- A valid `deploy.php` file exists in the root of your repository, customized to fit your deployment
- You have a set of SSH keys that will be used to authenticate with your AutoPilot deployment
- You are using a self-hosted runner with a whitelisted outbound IP address on your AutoPilot deployment
- You whitelisted the ssh key on your AutoPilot deployment

There are several steps to this example workflow.
First we check out the repository and set up PHP and Node.js.
Then we install the PHP dependencies using Composer.
In the final step we deploy using the official [Deployer GitHub Action](https://github.com/deployphp/action).

First, make sure that Deployer and the AutoPilot recipe are installed as production dependencies. Since the workflow runs `composer install --no-dev`, these packages must be in `require` and not `require-dev`:

```bash
composer require deployer/deployer jetrails/deployer-autopilot
```

We will want to make sure that the `deploy.php` is reading the `SSH_USER` and `SSH_HOST` environment variables. Make sure your `deploy.php` file looks something like this when defining the `cluster_user` and `elastic_ip` variables:

```php
set("cluster_user", getenv("SSH_USER"));
set("elastic_ip", getenv("SSH_HOST"));
```

You may choose to skip this step and hardcode these values in your `deploy.php` file, but it is not recommended.

Next, we can save the following `.github/workflows/deploy.yml` file in your project's repository:

```yaml
name: Deploy

on:
  push:
    branches:
      - master

jobs:
  deploy:
    runs-on: autopilot-github-runner
    steps:
      - uses: actions/checkout@v6

      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          tools: composer

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '22'

      - name: Install PHP dependencies
        run: composer install --no-interaction --prefer-dist --no-dev

      - uses: deployphp/action@v1
        env:
          SSH_USER: ${{ secrets.SSH_USER }}
          SSH_HOST: ${{ secrets.SSH_HOST }}
        with:
          dep: deploy
          private-key: ${{ secrets.SSH_PRIVATE_KEY }}
```

> **Note**: If the deploy fails with an SSH connection error, it is probably because the IP address is not reachable.
> Remember to whitelist the outbound IP address of your GitHub Actions runner.
> If you are having issues finding the outbound IP address, you can always add a step that runs `curl ip.jetrails.com` and check the output.

> **Note**: You may also need to install additional system dependencies such as `rsync` depending on what is available on your runner.

The last step is to set the following secrets in your repository under **Settings > Secrets and variables > Actions**:

- `SSH_USER`: The cluster user of your AutoPilot deployment
- `SSH_HOST`: The elastic IP address of your AutoPilot deployment
- `SSH_PRIVATE_KEY`: The deploy key that will be used to authenticate with your AutoPilot deployment
