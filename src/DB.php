<?php

namespace Harp\Query;

use Harp\Query;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use PDO;
use PDOException;

/**
 * The core class holding the connections to the databases. All your queries should originate from this class.
 * Allows multiple database connections.
 *
 * By default PDO::ATTR_EMULATE_PREPARES is set to false. This allows connecting to mysql with mysqlnd,
 * for better performance, and native types. If you are using a different database, you could change this
 * to false if it provides better performance for you.
 *
 * Uses psr/log for logging. This allows you to set up any logging mechanism you want, e.g. monolog.
 *
 * @author     Ivan Kerin <ikerin@gmail.com>
 * @copyright  (c) 2014 Clippings Ltd.
 * @license    http://spdx.org/licenses/BSD-3-Clause
 */
class DB extends PDO
{
    const NAME = 'default';
    const ESCAPING_MYSQL = 1;
    const ESCAPING_STANDARD = 2;

    private static $escapeWrappers = array(
        self::ESCAPING_MYSQL => '`',
        self::ESCAPING_STANDARD => '"',
    );

    /**
     * @var array
     */
    protected static $configs;

    /**
     * @var DB[]
     */
    protected static $dbs;

    /**
     * @var array
     */
    public static $defaults = array(
        'dsn' => 'mysql:dbname=test;host=127.0.0.1',
        'username' => '',
        'password' => '',
        'driverOptions' => array(
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ),
        'logger' => null,
        'escaping' => null,
    );

    /**
     * @param string $name
     */
    public static function getConfig($name = DB::NAME)
    {
        if (! isset(self::$configs[$name])) {
            self::$configs[$name] = array();
        }

        return self::$configs[$name];
    }

    /**
     * Set configuration for a given instance name
     * Must be called before get
     *
     * @param string $name
     * @param array  $parameters
     */
    public static function setConfig(array $parameters, $name = DB::NAME)
    {
        self::$configs[$name] = $parameters;
    }

    /**
     * Get the DB object instance for a given name
     * Use "default" by default
     *
     * @param  string $name
     * @return DB
     */
    public static function get($name = DB::NAME)
    {
        if (! isset(self::$dbs[$name])) {
            $config = self::getConfig($name);
            self::$dbs[$name] = new DB($config, $name);
        }

        return static::$dbs[$name];
    }

    /**
     * @var string
     */
    protected $name;


    /**
     * @var string
     */
    protected $escapeWrapper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * new Select Query for this DB
     *
     * @return Query\Select
     */
    public static function select()
    {
        return new Query\Select();
    }

    /**
     * new Update Query for this DB
     *
     * @return Query\Update
     */
    public static function update()
    {
        return new Query\Update();
    }

    /**
     * new Delete Query for this DB
     *
     * @return Query\Delete
     */
    public static function delete()
    {
        return new Query\Delete();
    }

    /**
     * new Insert Query for this DB
     *
     * @return Query\Insert
     */
    public static function insert()
    {
        return new Query\Insert();
    }

    /**
     * @param array  $options
     * @param string $name
     */
    public function __construct(array $options = array(), $name = DB::NAME)
    {
        $options = array_replace_recursive(static::$defaults, $options);

        parent::__construct($options['dsn'], $options['username'], $options['password'], $options['driverOptions']);

        $this->setLogger($options['logger'] ?: new NullLogger());
        $this->setEscaping($options['escaping']);
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     * @return DB $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param int $escaping
     * @return DB $this
     */
    public function setEscaping($escaping)
    {
        $this->escapeWrapper = isset(self::$escapeWrappers[$escaping])
            ? self::$escapeWrappers[$escaping]
            : null;

        return $this;
    }

    /**
     * @param  string $name
     * @return string
     */
    public function escapeName($name)
    {
        if ($this->escapeWrapper) {
            return $this->escapeWrapper
                .str_replace($this->escapeWrapper, '\\'.$this->escapeWrapper, $name)
                .$this->escapeWrapper;
        } else {
            return $name;
        }
    }

    /**
     * Run "prepare" a statement and then execute it
     *
     * @param  string $sql
     * @param  array  $parameters
     * @return \PDOStatement
     */
    public function execute($sql, array $parameters = array())
    {
        $this->logger->info($sql, array('parameters' => $parameters));

        try {
            $statement = $this->prepare($sql);
            $statement->execute($parameters);
        } catch (PDOException $exception) {
            $this->logger->error($exception->getMessage());

            throw $exception;
        }

        return $statement;
    }
}
