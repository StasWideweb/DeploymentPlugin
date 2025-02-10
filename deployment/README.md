# Deployment

This plugin created for deployment management via DeployHQ

## Requirements

This plugin requires Craft CMS 5.5.0 or later, and PHP 8.2 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “Deployment”. Then press “Install”.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require wideweb/craft-deployment

# tell Craft to install the plugin
./craft plugin/install deployment
```

## Short instruction:

After plugin installation you can see plugin in your settings tab. Open to manage it.
### 1. General tab
   -  You can choose the way of DeployHQ integration. API allow you to see deployment steps in real time, and information about last deployment. Webhook - use just for start deploy. 

### 2. API tab
   -  Here you can see all fields that you need to fill for use API integration.
### 3. Webhook tab
   -  Just past web hook link.
   
##   How to start Deployment:
   - Proceed to the Utilities > Deployment.
   
This page is depend on what integration type you have chosen

   - If it’s API

   You will see Last Deploy Information and start button, after deployment starting, you will se all steps and statuses

   - If it’s Webhook

   You will see button for start deployment process.

### All information about from where take data for plugin setup you can find in:
DOCUMENTATION.md