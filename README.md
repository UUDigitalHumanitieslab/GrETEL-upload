# GrETEL-upload

GrETEL-upload is an extension package for [GrETEL](http://gretel.ccl.kuleuven.be/gretel3/) that allows to upload your own corpus or dataset.
The application will then automatically transform your corpus in an Alpino XML-treebank. 
After processing, the treebanks are searchable in GrETEL, and if you supply metadata, you can use these for filtering and analysis.

## Local installation

### Requirements

On top of a default LAMP installation, the following packages are required:

* [basex](https://packages.debian.org/stable/basex): Storing processed treebanks into a XML-database.
* [php-zip](https://packages.debian.org/sid/php-zip): Required to process .zip-files.
* [php-ldap](https://packages.debian.org/sid/php-ldap): Authentication via LDAP.
* [php-sqlite3](https://packages.debian.org/sid/php-sqlite3): SQLite3 module for PHP, allows tests with in-memory database.

GrETEL-upload also requires the following external programs to be installed:

* [Alpino](http://www.let.rug.nl/vannoord/alp/Alpino/). Download and then unpack (preferably) into `/opt/Alpino/`. You can change the installation directory in the `application/database_default.php`.
* [CHAMD](https://github.com/JanOdijk/chamd). Download and then unpack (preferably) into `/opt/chamd/`. You can change the installation directory in the `application/database_default.php`.

### Configuration

You will have to provide configuration details in two files:

* `application/database.php`: Settings for your database connection to both the relational database (e.g. MySQL) and the XML-database (basex). An example configuration can be found in `application/database_default.php`.
* `application/ldap.php`: Settings for LDAP authentication. An example configuration can be found in `application/ldap_default.php`.

### Database schema

You can use the command `php index.php migrate` in the source directory to create/migrate the database schema.
See `docs/schema.png` for the current database schema (exported from [phpMyAdmin](https://www.phpmyadmin.net/)).

### Permissions

Make sure the `uploads` directory is writable for the user running the Apache daemon (usually `www-data`).

### Start-up

Start both Alpino and BaseX as server instances by running the following two commands:

	basexserver -S
	./alpino.sh

Then, navigate to the installation directory in your web browser (e.g. `localhost/gretel-upload/`) to start using GrETEL-upload.

## Uploading corpora

### Formats

Currently, three formats are supported: [LASSY-XML](https://www.let.rug.nl/vannoord/Lassy/), [CHAT](http://childes.talkbank.org/) and plain text (UTF-8 encoded).
When you upload a set of texts (always in a zipped folder, possibly consisting of multiple directories),
you can specify whether the text is already sentence- and/or word-tokenized.
If not, the application will do this for you.

### Metadata

GrETEL-upload allows metadata annotation using the [PaQu metadata format](http://zardoz.service.rug.nl:8067/info.html#cormeta).
This metadata will be converted to LASSY-XML during import.
For texts in the CHAT format, we use the program CHAMD to convert the file into the PaQu metadata format, and then run the same import scheme.

The GrETEL-upload interface then allows you to select which facet you would want to use to filter the data in GrETEL.
You can e.g. choose to display a metadata column called 'year' as a slider, dropdown list or set of checkboxes.
You can also choose to hide certain columns.

## Libraries

### PHP

GrETEL-upload is written in PHP and created with [CodeIgniter 3.1.4](https://www.codeigniter.com/).
The application uses the following libraries:

* `application/libraries/Alpino.php`: Wrapper around Alpino's dependency parser and tokenisation scripts.
* `application/libraries/BaseX.php`: [BaseX PHP connector](https://github.com/BaseXdb/basex/blob/master/basex-api/src/main/php/BaseXClient.php). Slightly modified to work in CodeIgniter.
* `application/libraries/Format.php`: Helper to convert between various formats such as XML, JSON, CSV, etc. Part of CodeIgniter Rest Server (see below).
* `application/libraries/Ldap.php`: Authentication via LDAP. Inspired by the [LDAP Authentication library](https://github.com/gwojtak/Auth_Ldap).
* `application/libraries/REST_Controller.php`: [CodeIgniter Rest Server](https://github.com/chriskacerguis/codeigniter-restserver), turns controllers into REST APIs. 

### Javascript

GrETEL-upload uses the following JavaScript libraries:

* [jQuery](https://jquery.com/)
* [qTip2](http://qtip2.com/)

### CSS

GrETEL-upload is created with [Pure CSS](http://purecss.io/).

### Images

GrETEL-upload uses the [FamFamFam silk icon set](http://www.famfamfam.com/). 

## API

GrETEL-upload has an API for retrieving data from the database:

* treebank/: Returns all publicly available treebanks.
* treebank/show/[title]: Returns the components of the treebank given by title.
* treebank/metadata/[title]: Returns the metadata of the treebank given by title.
* treebank/user/[user_id]: Returns all treebanks available to the currently logged in user. This might include private treebanks.

## Tests

The test suite is created using [ci-phpunit-test](https://github.com/kenjis/ci-phpunit-test).
This uses [PHPUnit](https://phpunit.de/).
You can run the tests by navigating to the `application/tests` directory and calling `phpunit`.

## Demo

A working version is available on http://gretel.hum.uu.nl.
