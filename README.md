# PHP RFC Digester

[![Build Status](https://travis-ci.org/mikeymike/php-rfc-digestor.svg?branch=master)](https://travis-ci.org/mikeymike/php-rfc-digestor)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mikeymike/php-rfc-digestor/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mikeymike/php-rfc-digestor/)

Easily track and view PHP RFCs. Get notified on the go when changes occur on specific a specific RFC or on all currently voting.

## Installation

```
$ git clone https://github.com/mikeymike/php-rfc-digestor.git
$ cd php-rfc-digestor
$ composer install
```

After cloning the project you will want to set up your configuration to send emails.

```
# Copy default config to the home dir
$ cp config.json ~/.rfcdigestor.json
```

Once you have the `.rfcdigestor.json` config in your home dir open it with your favourite editor and change the config to your requirements. I personally use my Gmail SMTP settings to send emails.


## Usage

There are not many commands to this application and detailed usage of them can be found in the [wiki](https://github.com/mikeymike/php-rfc-digestor/wiki) if needed.

To run a command use the executable in the bin dir `bin/php-rfc-digestor`

### Commands

```
Available commands:
   help            Displays help for a command
   list            Lists commands
notify
   notify:list     Get notifications of RFC list changes
   notify:rfc      Get notifications of RFC changes
   notify:voting   Get notifications of RFC vote changes for actively voting RFCs
rfc
   rfc:digest      Digest an RFCs contents
   rfc:list        List RFC, split by sections
   rfc:summary     List the vote totals for each active RFC
test
   test:email      Test application SMTP settings
```

## Running Tests

Run tests using PHPUnit from the project root.

```
$ vendor/bin/phpunit
```

## TODO

- [x] Finish DiffService for other commands
- [x] Send email notifications
- [x] Twig email templates
- [ ] Override twig template dir through config
- [ ] Tests, a lot of tests!
- [x] Tidy up README
- [ ] Finish WIKI
