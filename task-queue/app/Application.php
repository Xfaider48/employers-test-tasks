<?php


namespace App;

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Illuminate\Support\Arr;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class Application
{
    /**
     *
     */
    protected static Application $instance;

    /**
     * @var string
     */
    protected string $basePath;

    /**
     * @var \Illuminate\Database\Capsule\Manager
     */
    protected CapsuleManager $dbManager;

    /**
     * @var \PhpAmqpLib\Connection\AMQPStreamConnection
     */
    protected AMQPStreamConnection $ampqConnection;

    /**
     * Application constructor.
     *
     * @param string $basePath
     *
     * @throws \Exception
     */
    public function __construct(string $basePath)
    {
        static::$instance = $this;
        $this->basePath = $basePath;
        $this->onBooting();
    }

    /**
     * @return static
     */
    public static function getInstance(): self
    {
        return static::$instance;
    }

    /**
     * @param string $option
     * @param mixed  $default
     *
     * @return mixed
     */
    public function env(string $option, $default = null)
    {
        return Arr::get($_ENV, $option, $default);
    }

    /**
     * @return \Illuminate\Database\Capsule\Manager
     */
    public function db(): CapsuleManager
    {
        return $this->dbManager;
    }

    /**
     * @return \PhpAmqpLib\Connection\AMQPStreamConnection
     */
    public function ampq(): AMQPStreamConnection
    {
        return $this->ampqConnection;
    }

    /**
     *
     * @throws \Exception
     */
    protected function onBooting(): void
    {
        $this->loadEnv();
        $this->bootDbManager();
        $this->bootAmpqConnection();
    }


    /**
     *
     */
    protected function loadEnv()
    {
        $dotEnv = Dotenv::createImmutable($this->basePath);
        $dotEnv->load();
    }

    /**
     *
     */
    protected function bootDbManager(): void
    {
        $this->dbManager = new CapsuleManager();
        $this->dbManager ->addConnection([
            'driver' => $this->env('DB_DRIVER', 'pgsql'),
            'host' => $this->env('DB_HOST', 'localhost'),
            'port' => $this->env('DB_PORT', '5432'),
            'database' => $this->env('DB_DATABASE', 'database'),
            'username' => $this->env('DB_USERNAME', 'root'),
            'password' => $this->env('DB_PASSWORD', 'password'),
            'charset' => $this->env('DB_CHARSET', 'utf8'),
            'collation' => $this->env('DB_COLLATION', 'utf8_unicode_ci')
        ]);
    }

    /**
     *
     */
    protected function bootAmpqConnection(): void
    {
        $this->ampqConnection = new \PhpAmqpLib\Connection\AMQPStreamConnection(
            $this->env('RABBITMQ_HOST', '127.0.0.1'),
            $this->env('RABBITMQ_PORT', '5672'),
            $this->env('RABBITMQ_USERNAME', 'guest'),
            $this->env('RABBITMQ_PASSWORD', 'guest'),
            $this->env('RABBITMQ_VHOST', '/'),
        );
    }
}