# dexcom-share-is-down
PHP project to update Nightscout with SGVs from Loop's predicted BG values

## Disclaimer
DO NOT USE UNLESS YOU UNDERSTAND WHAT THIS PROJECT IS DOING. USE AT YOUR OWN RISK.

The most likely issues will be associated with getting composer and Symfony working,
followed by timezone issues (I suspect). I have used this code since around 12:30AM
December 1st and have not found any issues, but I'm sure they're out there. If you
run into a bug, this code could upload bad entries in your Nightscout database. All
entries are marked with "Loop Hack" and are easy to remove if you're familiar with
MongoDB.

Pull requests are welcomed. Someone can likely make this much more usable - this was
quick and dirty for my needs. Hopefully it's useful to someone else.

## Background
When Dexcom's share servers are down, NightScout is unable to get and display SGVs.
Fortunately, Loop has already sent a good proxy for SGV in the form of predicted BG
values. In most cases, the next predicted BG value is equal to the current SGV.

This project simply leverages the Nightscout API to get the next predicted SGV and
uploads it as a SGV. This is a hack and should be used with caution. It would be
better if Loop simply uploaded the SGV when Dexcom is down, but this was faster for
me. The second best option: this general functionality could exist within Nightscout.
It would be better to have code running in Nightscout to do this same thing rather
than setting up a separate cron job to run external to Nightscout (as this project
does).

The project uses Symfony as a framework, but all meaningful code is in a couple of
files. It's beyond the scope of this project to explain how PHP or Symfony work. You
will also need to
[install Composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos)
in order to install this project.

## Installation and Setup
### Heroku Deployment (the easy way)
* Fork this repo by clicking on the "Fork" button on [this page](https://github.com/ryanpacker/dexcom-share-is-down)
* Create a developer account with Heroku if you don't already have one
* Login to your [Heroku dashboard](https://dashboard.heroku.com/)
* Click on the "New" button and then "Create New App"
* Give your app a name - doesn't matter what it is - and click "Create App"
* Under the "Deploy" tab in the "Deployment Method" section, select "Github"
* If your Github account is already connected to your Heroku account, you can just click the "Search" button without entering any text. All of your Github repos will show up. If you have a bunch, then you probably don't need me to tell you how to find the one you're looking for :-)
* Find your fork of "dexcom-share-is-down" and click "Connect"
* Toward the bottom of the screen in the "Manual deploy" section, select the branch you want (if you don't know what this means, you want master) and then click "Deploy Branch"
* It will take a few seconds during which you'll see scrolling text. At the end you should see "Your app was successfully deployed"
* Go to the "Settings" tab of the dashboard and click "Reveal Config Vars"
* Add NIGHTSCOUT_URL and NIGHTSCOUT_SECRET to your config vars (make sure you don't include a trailing slash on your URL)
* Go to the resources tab to add two "Add-ons"
* Search for "papertrail" - click on Papertrail and then click "Provision"
* Search for and add "Heroku Scheduler" the same way
* Click on "Heroku Scheduler" to open a new tab
* Click "Create Job" and then type "php bin/console go -d5" under "Run Command" - then click "Save Job"
* Go back to your original tab and click on Papertrail to open a new tab - agree to the terms of service
* If you've done everything correctly, every 10 minutes you should see the output from the script in Papertrail
* To turn off the service, just remove the job from the Heroku Scheduler

### Local Deployment (slightly harder way)

* [Install Composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos)
* Download [dexcom-share-is-down](https://github.com/ryanpacker/dexcom-share-is-down/archive/master.zip) from Github
* Move the zip file into the directory where you plan to use the project (`~/Documents` for these instructions)
* Open Terminal, cd into the project and run `composer install`
```
$ cd ~/Documents/dexcom-share-is-down-master
$ composer install
```
* Edit `~/Documents/dexcom-share-is-down-master/.env` file to add your Nightscout credentials
* Run the following command to see more info about the tool
```
$ php bin/console go --help
```
* Run command with the `-d5` flag to test backfilling any data found in the last 5 devicestatus entries
```
$ php bin/console go -d5
```
* If successful thus far, run with a large number to backfill
```
$ php bin/console go -d100
```
* Edit the crontab to get this job to run every minute
```
$ crontab -e
```
* add the following line to crontab (modified to reflect the location of your project):
```
 * * * * * php ~/Documents/dexcom-share-is-down-master/bin/console go -d3 >> ~/Documents/dexcom-share-is-down-master/activity_log.txt
```
* In case this isn't obvious, this needs to run on a computer that is always on and doesn't go to sleep. If the computer isn't running or can't connect to the internet, this tool won't work. 
