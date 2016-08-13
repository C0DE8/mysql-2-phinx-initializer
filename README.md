# MySQL2PhinxInitializer
A command line PHP script to generate a phinx initial migration from an existing MySQL database.

## Usage
```
Usage: php mysql2phinx-initializer.php [options]
 -d | --database=<database>
 -u | --user=<user>
 -p | --pass=<password>
 -h | --host=<host> (optional)
 -t | --port=<port> (optional)
 -n | --name=<migration classname> (optional) [default: InitialMigration]
 -s | --skip=<tables to be skipped> (optional) [default: phinxlog; comma separated]
 -f | --format=<format of file prefix> (optional) [default: "YmdHis"; php date format; result in YYYYMMDDHHMMSS)
 -o | --output=<output file> (optional) [default: {format_prefix}_initial_migration.php]

Example:
$ php mysql2phinx-initializer.php -d="mydatabase" -u="username" -p="secure-password" -h"=1.2.3.4" -t="3307" -n="InitialMigration" -f="YmdHis" -o="initial_migration.php"

```

Will create an initial migration class in the file `YYYYMMDDHHMMSS_initial_migration.php` for all tables in the database passed. The classname is by default "InitialMigration". (configurable)
