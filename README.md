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
