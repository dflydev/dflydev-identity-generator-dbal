<?php

/*
 * This file is a part of dflydev/identity-generator-dbal.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dflydev\Tests\IdentityGenerator\DataStore\Dbal;

use Dflydev\IdentityGenerator\DataStore\Dbal\DataStore;
use Dflydev\IdentityGenerator\Exception\DataStoreException;
use Dflydev\IdentityGenerator\Exception\MobsUnsupportedException;
use Dflydev\IdentityGenerator\Exception\NonUniqueIdentityException;
use Doctrine\DBAL\Connection;

/**
 * Test DBAL Data Store
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class DbalDataStoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test connection throws unexpected exception
     */
    public function testConnectionThrowsUnexpectedException()
    {
        $connection = $this
            ->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $connection
            ->expects($this->once())
            ->method('insert')
            ->with($this->equalTo('identityTableName'), $this->equalTo(array('identityColumnName' => 'testIdentity')))
            ->will($this->throwException(new \PDOException('Bogus Exception')));

        $dataStore = new DataStore($connection, 'identityTableName', 'identityColumnName');

        try {
            $dataStore->storeIdentity('testIdentity');

            $this->fail('Connection should have thrown an exception');
        } catch (DataStoreException $e) {
            $this->assertEquals('Bogus Exception', $e->getPrevious()->getMessage());
        }
    }

    /**
     * Test connection throws constraint exception
     */
    public function testConnectionThrowsConstraintException()
    {
        $connection = $this
            ->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $exception = new \PDOException('Bogus Exception');
        $exception->errorInfo = array('23000');

        $connection
            ->expects($this->once())
            ->method('insert')
            ->with($this->equalTo('identityTableName'), $this->equalTo(array('identityColumnName' => 'testIdentity')))
            ->will($this->throwException($exception));

        $dataStore = new DataStore($connection, 'identityTableName', 'identityColumnName');

        try {
            $dataStore->storeIdentity('testIdentity');

            $this->fail('Connection should have thrown an exception');
        } catch (NonUniqueIdentityException $e) {
            $this->assertEquals('Could not store generated identity as it is not unique: testIdentity', $e->getMessage());
        }
    }

    /**
     * Test can store with no mob
     */
    public function testCanStoreNoMob()
    {
        $connection = $this
            ->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $connection
            ->expects($this->once())
            ->method('insert')
            ->with($this->equalTo('identityTableName'), $this->equalTo(array('identityColumnName' => 'testIdentity')));

        $dataStore = new DataStore($connection, 'identityTableName', 'identityColumnName');

        $dataStore->storeIdentity('testIdentity');
    }

    /**
     * Test can store with mob
     */
    public function testCanStoreMob()
    {
        $connection = $this
            ->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $connection
            ->expects($this->once())
            ->method('insert')
            ->with(
                $this->equalTo('identityTableName'),
                $this->equalTo(array(
                    'identityColumnName' => 'testIdentity',
                    'mobColumnName' => 'testMob',
                ))
            );

        $dataStore = new DataStore($connection, 'identityTableName', 'identityColumnName', 'mobColumnName');

        $dataStore->storeIdentity('testIdentity', 'testMob');
    }

    /**
     * Test cannot store mob when no mob configured
     */
    public function testCannotStore()
    {
        $connection = $this
            ->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $dataStore = new DataStore($connection, 'identityTableName', 'identityColumnName');

        try {
            $dataStore->storeIdentity('testIdentity', 'testMob');

            $this->fail('Should not be able to store with a mob if no mob is configured');
        } catch (MobsUnsupportedException $e) {
            $this->assertEquals(
                'Mobs are unsupported under current configuration. Mob column is not defined. testIdentity (with mob testMob)',
                $e->getMessage()
            );
        }
    }
}
