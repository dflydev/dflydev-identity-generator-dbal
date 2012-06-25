Doctrine DBAL Identity Generator Data Store
===========================================

Provides a Doctrine DBAL implementation of the identity generator
data store. For more information see:
[dflydev/identity-generator](https://github.com/dflydev/dflydev-identity-generator)


Requirements
------------

 * PHP 5.3+
 * Doctrine DBAL 2.2.*


Implementation Details
----------------------

This simple dflydev/identity-generator Data Store implementation relies on
blindly inserting records into a database.

It works under the assumption that if the underlying database has a unique
constraint configured for either an identity column or a unique constraint
configured for both and identity and mob column an exception will be thrown.

It attempts to capture this exception and determine whether or not there is a fault
in the underlying connection or if it was due to a constraint violation. It
does this by way of looking for ANSI SQL Error State Code `23000`.


Schemas
-------

The following are very naive examples of database schemas that will work with
this data store. They may work for you for testing but please do not blindly
use them for production.


### SQLite

If mobs are not required simply create a table with one column marked as `unique`.

    CREATE TABLE identity (
        identity string(64) unique
    );

If mobs are required create a table with two columns and create a unique constraint
across both columns.

    CREATE TABLE identityWithMob (
        identity string(64),
        mob string(64),
        constraint id unique (identity, mob)
    );


### Mysql

If mobs are not required simply create a table with one column marked as `unique`.

    CREATE TABLE identity (
        identity varchar(64) unique
    );

If mobs are required create a table with two columns and create a unique index
across both columns.

    CREATE TABLE identityWithMob (
        identity varchar(64),
        mob varchar(64),
        unique index (identity, mob)
    );


Usage
-----

    use Dflydev\IdentityGenerator\DataStore\Dbal\DataStore;
    use Doctrine\DBAL\Configuration;
    use Doctrine\DBAL\DriverManager;
    
    $config = new Configuration();
    $connectionParams = array(); // driver specific connection configuration
    $connection = DriverManager::getConnection($connectionParams, $config);

    // Create a Data Store that does not support a mob.
    $dataStore = new DataStore(
        $connection,
        'tableName',
        'identityColumnName'
    );

    // Create a Data Store that supports a mob.
    $dataStoreWithMob = new DataStore(
        $connection,
        'tableName',
        'identityColumnName',
        'mobColumnName'
    );


License
-------

This library is licensed under the New BSD License - see the LICENSE file
for details.


Community
---------

If you have questions or want to help out, join us in the
[#dflydev](irc://irc.freenode.net/#dflydev) channel on irc.freenode.net.
