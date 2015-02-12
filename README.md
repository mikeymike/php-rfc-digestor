BasePHPRepo
===========
[![Build Status](https://travis-ci.org/Vendor/Lib.png?branch=master)](https://travis-ci.org/Vendor/Lib)
[![Coverage Status](https://coveralls.io/repos/Vendor/Lib/badge.png)](https://coveralls.io/r/WeareJH/Flexitime)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Vendor/Lib/badges/quality-score.png)](https://scrutinizer-ci.com/g/Vendor/Lib/)
[![Dependency Status](https://www.versioneye.com/user/projects/hash/badge.png)](https://www.versioneye.com/user/projects/hash)
[![Latest Stable Version](https://poser.pugx.org/vendor/lib/version.png)](https://packagist.org/packages/aydin-hassan/magento-core-mapper)
[![Latest Untable Version](https://poser.pugx.org/vendor/lib/v/unstable.png)](https://packagist.org/packages/aydin-hassan/magento-core-mapper)

A Starting point for PHP Libraries/Projects. Includes Composer config, Travis &amp; Scrutinzer CI. Coveralls for Code Coverage. PHPUnit Setup. Also includes various linting tools.

Includes
--------

* __PSR4 Directory Structure__. Library code should go in `src` and test code should go in `test`. If the code is correctly namespaced and the namespace is set in `composer.json` then all code will be autoloaded automatically, including tests, without the need of a boostrap file. For example: You place a file, `File.php` in `src`. If you set it's namespace to `Ah\Transformer`, and update `composer.json` autoload section to be:

```
"autoload" : {
    "psr-4" : {
        "Ah\\Transformer\\": "src"
    }
},
```

As long as your file has a class named `File` it can be used like: `$fileTrabsformer = new \Ah\Transformer\File;` as long as the root poject includes the composer autoload file.

* __PSR1 & PSR2 Liniting__. After installing the project via `Composer` you can run the linting process to ensure your code adhears to the PSR1 & 2 coding standards: 

```
$ ./vendor/bin/phpcs --standard=PSR2 ./test/
$ ./vendor/bin/phpcs --standard=PSR2 ./src/
```

* __Travis CI config__. The project comes with a default `.travis.yml` file which will run your tests, PSR1 & PSR2 code linting, generate code-coverage and upload to `Scrutinizer` and `Coveralls`. The default config tests against `PHP5.4`, `PHP5.5`, `PHP5.6` & `HHVM`, `HHVM` tests are allowed to fail.

* __Scrutinizer CI config__: The project comes with a default `.scrutinizer.yml` file which performs a number of checks on your code and provides quality based metrics.

* Deafult packaged license is `MIT`

* __PHPUnit config__: Running `./vendor/bin/phpunit` in your project root will run all your tests, providing they are located in the `test` directory.

* __README with Various badges__. Various badges are included in the readme so everyone knows how cool your project is. Badges included are (The URL's will need updated, and the services will need activating);
    * [Travis CI Build Status](https://travis-ci.org/)
    * [Coveralls Test Coverage](https://coveralls.io/)
    * [Scrutinizer Code Quality](https://scrutinizer-ci.com/)
    * [Version Eye Dependency Watcher](https://www.versioneye.com/)
    * [Packgist Stable Version](https://poser.pugx.org/)
    * [Packgist Un-stable Version](https://poser.pugx.org/)

Usage
-----
1. Make sure you have [composer](https://getcomposer.org/doc/00-intro.md#globally) in your path.
2. Git clone: `git clone git@github.com:AydinHassan/BasePHPRepo.git`
3. Delete VCS History: `rm -rf .git`
4. Update `composer.json` Add in your vendor/package name, description and update your autoload config, along with anything else you might want to change. This may include, license, author and dependencies.
5. Run Composer `composer install`
6. Configure services such as Travis, Scrutinizer & Coveralls. 
7. Update badge links with real links which you got when enabling the services in the step above.
8. Add your git remote with: `git remote add origin url`
8. Build your tool & push!
