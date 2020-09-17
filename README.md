# InnoDB Shrink

This package provides a command-line tool that can be used to shrink InnoDB tables in a PHP application running on Platform.sh.

## Installation

`composer require royallthefourth/psh-innodb-shrink`

## Usage

Add a cron to your `.platform.app.yaml` that invokes the program with a ratio of free space as its argument.

For example, this will only run on tables that are at least 75% empty:
```
vendor/bin/shrink 0.75
```

## Tests

This package has tests. Use this command to run the them:

```
vendor/bin/phpunit tests
```
