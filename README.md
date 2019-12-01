# dexcom-share-is-down
PHP project to update Nightscout with SGVs from Loop's predicted BG values

DO NOT USE UNLESS YOU UNDERSTAND WHAT THIS PROJECT IS DOING. USE AT YOUR OWN RISK.

When Dexcom's share servers are down, NightScout is unable to get and display SGVs. 
Fortunately, Loop has already sent a good proxy for SGV in the form of predicted BG 
values. In most cases, the next predicted BG value is equal to the current SGV. 

This project simply leverages the Nightscout API to get the next predicted SGV and 
uploads it as a SGV. This is a hack and should be used with caution. It would be 
better if Loop simply uploaded the SGV when Dexcom is down, but this was faster for 
me. The second best option: this general functionality could exist within Nightscout. 
It would be better to have code running in Nightscout to do this same thing rather
than setting up a seperate cron job to run external to Nightscout (as this projec
does).

The project uses Symfony as a framework, but all meaningful code is in a couple of 
files. It's beyond the scope of this project to explain how PHP or Symfony work. You
will also need to install Composer before this project will work.

## Installation and Setup
* download project
* cd into project
* `composer install`
* edit crontab
* `crontab -e`
* add the following line (modified to reflect the location of your project):
* ``
* edit [preferences file] to include your Nightscout URL and Nightscout secret
