<?php

/*
 * This file is a part of dflydev/identity-generator-dbal.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dflydev\IdentityGenerator\DataStore\Dbal;

use Dflydev\IdentityGenerator\DataStore\DataStoreInterface;
use Dflydev\IdentityGenerator\Exception\DataStoreException;
use Dflydev\IdentityGenerator\Exception\MobsUnsupportedException;
use Dflydev\IdentityGenerator\Exception\NonUniqueIdentityException;
use Doctrine\DBAL\Connection;

/**
 * Doctrine DBAL Data Store implementation
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class DataStore implements DataStoreInterface
{
    /**
     * Doctrine DBAL Connectcion
     *
     * @var Connection
     */
    private $connection;

    /**
     * Table
     *
     * @var string
     */
    private $table;

    /**
     * Column containing the identities
     *
     * @var string
     */
    private $identityColumn;

    /**
     * Column containing the mobs
     *
     * @var string
     */
    private $mobColumn;

    /**
     * Constructor
     *
     * @param Connection $connection     Doctrine DBAL Connection
     * @param string     $table          Table
     * @param string     $identityColumn Column containing the identities
     * @param string     $mobColumn      Column containing the mob
     */
    public function __construct(Connection $connection, $table, $identityColumn, $mobColumn = null)
    {
        $this->connection = $connection;
        $this->table = $table;
        $this->identityColumn = $identityColumn;
        $this->mobColumn = $mobColumn;
    }

    /**
     * {@inheritdoc}
     */
    public function storeIdentity($identity, $mob = null)
    {
        if (null !== $mob and null === $this->mobColumn) {
            throw new MobsUnsupportedException($identity, $mob, "Mob column is not defined.");
        }
        try {
            $map = array($this->identityColumn => $identity);
            if (null !== $mob) {
                $map[$this->mobColumn] = $mob;
            }

            $this->connection->insert($this->table, $map);
        } catch (\PDOException $e) {
            if ('23000' === $e->errorInfo[0]) {
                throw new NonUniqueIdentityException($identity, $mob);
            }

            throw new DataStoreException($e, $identity, $mob);
        }
    }
}
