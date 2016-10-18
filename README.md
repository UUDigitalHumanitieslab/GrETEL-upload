# GrETEL-upload

GrETEL-upload is an extension package for [GrETEL](https://github.com/UiL-OTS-labs/GrETEL) that allows to upload your own corpus or dataset.
The application will then automatically transform your corpus in an Alpino XML-treebank. 
After processing, the treebanks are searchable in GrETEL.

## API

* treebank/get: Returns all publicly available treebanks.
* treebank/show/[title]: Returns information on the treebank given by title.
* treebank/user/[user_id]: Returns all treebanks available to the currently logged in user. This might include private treebanks.

## Demo

A working version is available on http://gretel.hum.uu.nl.

## Local installation

### Requirements

On top of a default LAMP installation, the following packages are required:

* [basex](https://packages.debian.org/stable/basex): Stored processed treebanks.
* [php-zip](https://packages.debian.org/sid/php-zip): Required to process .zip-files.
* [php-ldap](https://packages.debian.org/sid/php-ldap): Authentication via LDAP.

### Configuration

* `application/database.php`: Settings for your database connection to both the relational database (MySQL, PostgreSQL, SQLite) and the XML-database (basex). An example configuration can be found in `application/database_default.php`.
* `application/ldap.php`: Settings for LDAP authentication.

### Database schema

You can use the command `php index.php migrate` in the source directory to create/migrate the database.

### Permissions

Make sure the `uploads` directory is writable for the user running the Apache daemon (usually `www-data`).

## Libraries

### PHP

GrETEL-upload is written in PHP and created with [CodeIgniter 3.1.0](https://www.codeigniter.com/).
The application uses the following libraries:

* `application/libraries/Alpino.php`: Wrapper around Alpino's dependency parser and tokenisation scripts.
* `application/libraries/BaseX.php`: BaseX PHP script. Slightly modified to work in CodeIgniter.
* `application/libraries/Format.php`: Helper to convert between various formats such as XML, JSON, CSV, etc.
* `application/libraries/Ldap.php`: Authentication via LDAP. Inspired by auth_ldap.
* `application/libraries/REST_Controller.php`: Turns controllers into REST APIs. 

### Javascript

GrETEL-upload uses the following libraries:

* [jQuery](https://jquery.com/)
* [qTip2](http://qtip2.com/)

### CSS

GrETEL-upload is created with [Pure CSS](http://purecss.io/).
