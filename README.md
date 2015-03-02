# PHP RFC Digestor

[![Build Status](https://travis-ci.org/mikeymike/php-rfc-digestor.svg?branch=master)](https://travis-ci.org/mikeymike/php-rfc-digestor)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mikeymike/php-rfc-digestor/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mikeymike/php-rfc-digestor/)

This project started out as a playground to learn Symfony CLI application development and just improve my PHP skills. 

Now it's something I use on a daily basis to quickly look at the status of PHP RFCs.

## Commands

### Rfc Summary

Loops around all active (In Voting) RFC votes listing the counts for each. 

```
Usage:
 rfc:summary

Aliases: rfc:active
```

#### Example Output

```
$ bin/php-rfc-digestor rfc:summary
+-------------------------------------------------------------------+----+
| Context Sensitive Lexer                                           |    |
+-------------------------------------------------------------------+----+
| Should PHP7 have a context sensitive lexer?                       |    |
| Yes                                                               | 14 |
| No                                                                | 2  |
+-------------------------------------------------------------------+----+
| Exceptions in the engine                                          |    |
+-------------------------------------------------------------------+----+
| Allow exceptions in the engine and conversion of existing fatals? |    |
| Yes                                                               | 55 |
| No                                                                | 2  |
| Introduce and use BaseException?                                  |    |
| Yes                                                               | 35 |
| No                                                                | 18 |
+-------------------------------------------------------------------+----+
| Remove PHP 4 Constructors                                         |    |
+-------------------------------------------------------------------+----+
| remove_php4_constructors                                          |    |
| Yes                                                               | 45 |
| No                                                                | 3  |
+-------------------------------------------------------------------+----+
| Improve array to string conversion                                |    |
+-------------------------------------------------------------------+----+
| array-to-string                                                   |    |
| Yes                                                               | 30 |
| No                                                                | 5  |
+-------------------------------------------------------------------+----+
| Scalar Type Hints v0.5                                            |    |
+-------------------------------------------------------------------+----+
| Accept Scalar Type Declarations With Optional Strict Mode?        |    |
| Yes                                                               | 37 |
| No                                                                | 12 |
+-------------------------------------------------------------------+----+
```

### Rfc List

Lists RFCs by section including their "code" required for the RFC Digest command

```
Usage:
 rfc:list [--voting] [--discussion] [--draft] [--accepted] [--declined] [--withdrawn] [--inactive] [--all]
```

#### Example Output

```
$ bin/php-rfc-digestor rfc:list --voting --discussion
+---------------------------------------------------------+-----------------------------------+
| RFC                                                     | RFC Code                          |
+---------------------------------------------------------+-----------------------------------+
| In voting phase                                         |                                   |
+---------------------------------------------------------+-----------------------------------+
| Context Sensitive Lexer                                 | context_sensitive_lexer           |
| Exceptions in the engine                                | engine_exceptions_for_php7        |
| Remove PHP 4 Constructors                               | remove_php4_constructors          |
| Improve array to string conversion                      | array-to-string                   |
| Scalar Type Hints v0.5                                  | scalar_type_hints_v5              |
+---------------------------------------------------------+-----------------------------------+
| Under Discussion                                        |                                   |
+---------------------------------------------------------+-----------------------------------+
| Generator Delegation                                    | generator-delegation              |
| Introduce consistent function names                     | consistent_function_names         |
| Strict Argument Count On Function Calls                 | strict_argcount                   |
| Constructor behaviour of internal classes               | internal_constructor_behaviour    |
| Introduce Design by Contract                            | introduce_design_by_contract      |
| Native Design by Contract support as annotation         | dbc                               |
| Native Design by Contract support as definition         | dbc2                              |
| Introduce script only require/include                   | script_only_include               |
| Precise URL include control                             | allow_url_include                 |
| Anonymous Class Support                                 | anonymous_classes                 |
| Reliable User-land CSPRNG                               | easy_userland_csprng              |
| Coercive Scalar Type Hints                              | coercive_sth                      |
| Make empty() a Variadic                                 | variadic_empty                    |
| Reserve More Types in PHP 7                             | reserve_more_types_in_php_7       |
| Comparable                                              | comparable                        |
| Generator Return Expressions                            | generator-return-expressions      |
| Continue output buffering                               | continue_ob                       |
|  Deprecate INI set/get aliases                          | deprecate_ini_set_get_aliases     |
| Additional splat operator usage                         | additional-splat-usage            |
| GitHub Pull Requests Triage Team                        | github-pr                         |
| Change checkdnsrr() $type argument behavior             | checkdnsrr-default-type           |
| Loosening heredoc/nowdoc scanner                        | heredoc-scanner-loosening         |
| Binary String Comparison                                | binary_string_comparison          |
| password_hash function behavior                         | password_hash_spec                |
| Use php_mt_rand() where php_rand() is used              | use-php_mt_rand                   |
| ReflectionParameter Typehint accessors                  | reflectionparameter.typehint      |
| Secure Session Options                                  | secure-session-options-by-default |
| Unify crypto source INI setting                         | unified-crypto-source             |
| Make PHP open tag optional for better security          | nophptags                         |
| Build OpenSSL module by default                         | build-openssl-by-default          |
| Module API introspection                                | moduleapi-inspection              |
| Add support for GMP floating point numbers              | gmp-floating-point                |
| Normalizing increment and decrement operators           | normalize_inc_dec                 |
| Secure session_regenerate_id()                          | session_regenerate_id             |
| Named Parameters                                        | named_params                      |
| Internal Serialize API                                  | internal_serialize_api            |
| unset(): return bool if the variable has existed        | unset_bool                        |
| Integrate voting polls in PHP.net                       | site_voting_poll                  |
| Escaping RFC for PHP Core                               | escaper                           |
| Add is_cacheable() stream-wrapper operation             | streams-is-cacheable              |
| Add cyclic string replace to str_[i]replace() functions | cyclic-replace                    |
| In Operator                                             | in_operator                       |
+---------------------------------------------------------+-----------------------------------+
```


### RFC Digest

Digest a specific RFC getting a summarised view. You can emit sections by using the options such as `--votes` to see only the votes for that RFC. 

`-d|--detailed` will list all the votes rather than a summarised table. 

```
bin/php-rfc-digestor rfc:digest --help
Usage:
 rfc:digest [--details] [--changelog] [--votes] [-d|--detailed] rfc
```

#### Example Output

```
$ bin/php-rfc-digestor rfc:digest scalar_type_hints_v5
  
RFC Details

Scalar Type Declarations
+--------------------+-------------------------------------------------------------------------+
| Version            |  0.5.3                                                                  |
| Date               |  2015-02-18                                                             |
| Author             |  Anthony Ferrara ircmaxell@php.net (original Andrea Faulds, ajf@ajf.me) |
| Status             |  Vote                                                                   |
| First Published at |  http://wiki.php.net/rfc/scalar_type_hints_v5                           |
| Forked From        |  http://wiki.php.net/rfc/scalar_type_hints                              |
+--------------------+-------------------------------------------------------------------------+

RFC ChangeLog
+----------------------------------------------------------------------------------------------------------------+
| v0.5.3 Change version target back and add line about bypassing function execution on type error in strict mode |
| v0.5.2 Change version target                                                                                   |
| v0.5.1 Remove aliases from proposal                                                                            |
| v0.5 Fork from Andrea's original proposal. Change declare behavior. Add int→float (primitive type widening).   |
+----------------------------------------------------------------------------------------------------------------+

RFC Votes

As this is a language change, this RFC requires a 2/3 majority to pass.

Accept Scalar Type Declarations With Optional Strict Mode?
+-----------+-----+----+
| Real name | Yes | No |
+-----------+-----+----+
| Count:    | 37  | 12 |
+-----------+-----+----+
```

### Notify RFC

Notify of changes to a specific RFC

*__Coming Soon__*

### Notify RFC List

Notify of changes in the RFC listings, e.g. new RFCs, accepted RFCs, RFC moved to In Voting etc

*__Coming Soon__*

### Notify RFC Summary

Notify of changes shown in `rfc:summary` command

*__Coming Soon__*

## TODO

- [ ] Finish DiffService for other commands
- [ ] Send email notifications
- [ ] Twig email templates
- [ ] Override through config
- [ ] Tests, a lot of tests!
