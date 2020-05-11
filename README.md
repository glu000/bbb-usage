# bbb-usage

## Overview

bbb-usage collects and shows usage statistics of a BigBlueButton server.

It consists of 2 parts:
- data collector (`collect.php`)
- web GUI to show collected statistics (`index.php`)

Data is stored in a mySQL database. bbb-usage uses the BBB API to collect usage data.

## Features

- Collect BBB usage statistics
    - number of active meetings (rooms)
    - number of participants
    - number of voice participants (with & without microphone)
    - number of video participants
    - number of breakout-rooms
- Show live-data and historical data
- Support for unlimited frontends
- Filtering of data
    - starting date
    - end date
    - frontend
- Access protection

## Setup

You can install bbb-usage on the BBB server or on a different server. 

### Prerequisites

- LINUX Server
- Webserver (eg Apache, NGINX)
- PHP 7.2 (or above)
- mySQL Server

### Installation steps

#### 1. Clone Github Repository to your local machine

Let's assume `/var/www/html/` is the root directory of your webserver:

`cd /var/www/html/`
`git clone https://github.com/glu000/bbb-usage`

#### 2. Create mySQL database

Creata a new database `bbb-usage` and grant access to a user

#### 3. Adopt bbb-usage configuration

Copy `conf-example.php` to `conf.php` and adjust settings

#### 4. Add crontab job

It's recommended to collect data every 5 minutes:

`*/5 * * * * cd /var/www/html/bbb-usage && /usr/bin/php /var/www/html/bbb-usage/collect.php`


## GUI

Have a look at `bbb-usage-gui.jpg`

## Contribute

Feel free to fork the repo an send pull-requests