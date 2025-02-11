# Deployment

This plugin created for deployment management via DeployHQ

## Requirements

This plugin requires Craft CMS 5.5.0 or later, and PHP 8.2 or later.

## Installation

- Download the plugin archive
- unzip the plugin archive
- place the plugin deployment folder in the plugins directory, if it does not exist,
create it so that the full path to the plugin looks like this: /path/to/my-project.test/plugins/deployment
- open your project composer.json, and add plugin directory to the repositories:

      {
       "type": "path",
       "url": "plugins/deployment"
      }
- example of the result;

      "repositories": [
        {
          "type": "composer",
          "url": "https://composer.craftcms.com",
          "canonical": false
        },
        {
          "type": "path",
          "url": "plugins/deployment"
        }
      ]

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