# Guide — Github Actions + Deployer PHP + AutoPilot

**Tutorial: Automating Magento Deployment with GitHub Actions and PHP Deployer**

**Prerequisites:**

* A working Magento installation
* A GitHub account
* Familiarity with PHP and its dependencies
* Knowledge of Git and version control
* We assume you already have a self-hosted runner somewhere else

**Step 1: Create a New GitHub Repository for Your Magento Project**

Please create a new repository on GitHub and initialize it with your Magento project. Make sure to create a `.gitignore` file to ignore any unnecessary files.

**Step 2: Install and Configure PHP Deployer**

On AutoPilot servers deployer is installed and can be run with `dep` alias. 

Configure PHP Deployer by creating a new file named `deploy.php` in the root of your Magento project directory with the following content. 

We recommend using our AutoPilot deployer recipe. Please visit the documentation at https://github.com/jetrails/deployer-autopilot

**Step 3: Configure Self-Hosted GitHub Actions Runner (AutoPilot Recommended way)**

In your Github repository navigate to Settings --> Actions --> Runners and click on the New Self-Hosted Runner button
https://docs.github.com/actions/hosting-your-own-runners/about-self-hosted-runners

Select ARM64 Architecture and then execute the following commands seen on the same page on the Jump Host machine. We recommend running it under the/var/www/{Website-name} directory. Once the commands are installed, you can simply run the following command to start the service:
```
sudo ./svc.sh start
```

You can review all available commands to restart Self-Hosted runner or temporarily stop it to prevent deployments:
```
$ sudo ./svc.sh 

Usage:
./svc.sh [install, start, stop, status, uninstall]
Commands:
   install [user]: Install runner service as Root or specified user.
   start: Manually start the runner service.
   stop: Manually stop the runner service.
   status: Display status of runner service.
   uninstall: Uninstall runner service.
```

To confirm, go back to Settings --> Actions --> Runners and refresh it.

**Step 3: Create a GitHub Actions Workflow**

It is recommended to use the official actions for deployer. You can find the documentation by visiting https://github.com/deployphp/action

Navigate to Actions --> New Flow --> PHP configure and create the following file.

Create a new file named `.github/workflows/magento.yml` in your repository with the following content:
```yaml
name: deploy

on:
  push:
    branches:
      - master

concurrency: production_environment

jobs:
  deploy:
    runs-on: self-hosted

    steps:
      - name: Checkout code
        uses: actions/checkout@v2

      - name: Set up PHP 8.4 with required Magento 2 extensions
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: >
            bcmath
            bz2
            calendar
            ctype
            curl
            dom
            fileinfo
            gd
            gettext
            hash
            iconv
            intl
            mbstring
            opcache
            pdo
            pdo_mysql
            simplexml
            soap
            sockets
            sodium
            sysvsem
            tokenizer
            xsl
            zip
          tools: composer:v2
          coverage: none

      - name: Install Composer dependencies
        run: composer install --no-interaction --prefer-dist --no-progress --optimize-autoloader

      - name: Install AutoPilot Deployer dependencies
        run: composer require jetrails/deployer-autopilot --dev

      - name: Run Deployer
        uses: deployphp/action@v1
        with:
          dep: deploy
          private-key: ${{ secrets.PRIVATE_KEY }}
          skip-ssh-setup: true
```

**Step 4: Push Your Changes to GitHub**

Commit and push your changes to your working branch of your repository.

This will trigger your deployment workflow and deploy your Magento project to a remote server using PHP Deployer.

**Conclusion:**

By following this tutorial, you have successfully automated the deployment of your Magento project using GitHub Actions and PHP Deployer. This setup provides a scalable and reliable way to deploy your Magento 
projects, making it ideal for professional use cases.
