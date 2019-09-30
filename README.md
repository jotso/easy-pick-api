# Easy Pick Api
A backend tool for joining Easy Pick! In one of the Easy Pick frontend pages the visitor joins the competition by entering a special code and has the chance of winning some nice, free stuff. The tool is built in such a way that multiple frontends can be connected.

## Getting started 

This Easy Pick Api is built using Slim Framework: http://www.slimframework.com/


### Run locally
You can start a php server on your machine (requires php installed)
```
php -S localhost:8888 -t public public/index.php
```


### Configuration

Most configuration is done in one of the files in  __/src/config/__. You'll find the following files in here:

* __settings.php__: the general settings of the easy-pick-api application
* __easy_pick.yaml__: the configuration for the frontend pages running on the application. All possible configuration options are listed in project 0
* __phpunit_test_easy_pick.yaml__: frontend configuration with test data for running phpunit

### Composer
Composer is a little piece of software wich is enclosed in the **composer.phar** file in the root of the project. You can run it as follows:
```
php composer.phar --help
```
which shows the possible options. The command that is most important is:
```
php composer.phar update
```
which will update the vendors.

### Testing

To run phpunit:

```
vendor/bin/phpunit
```

You can find or add some additional configuration for phpunit in the phpunit.xml file.
