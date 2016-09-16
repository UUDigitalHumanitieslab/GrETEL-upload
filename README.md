# GrETEL-upload

GrETEL-upload is an extension package for [GrETEL](https://github.com/UiL-OTS-labs/GrETEL) that allows to upload your own corpus or dataset.
In GrETEL, the uploaded corpora are then accisible via a REST API. 

## Demo

A working version is available on http://gretel.hum.uu.nl.

## Requirements

GrETEL-upload is written in PHP and created with [CodeIgniter 3.1.0](https://www.codeigniter.com/).
On top of a default LAMP installation, the following packages are required:

* basex: Stored processed treebanks.
* php-zip: Required to process .zip-files.
* php-ldap: Authentication via LDAP.

## Configuration

* `application/database.php`: Settings for your database connection to both the relational database (MySQL, PostgreSQL, SQLite) and the XML-database (basex). An example configuration can be found in `application/database_default.php`.
* `application/ldap.php`: Settings for LDAP authentication.
