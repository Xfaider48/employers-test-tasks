<?php


namespace App;


use App\Http\Controllers\Controller;
use App\Services\Models\Order\CreateOrderInterface;
use Bezhanov\Faker\ProviderCollectionHelper;
use Dotenv\Dotenv;
use Faker\Factory as Faker;
use Faker\Generator;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\TaggedContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

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
     * @var \Faker\Generator
     */
    private Generator $faker;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    private ContainerBuilder $container;

    /**
     * @var \Symfony\Component\HttpKernel\HttpKernel
     */
    private HttpKernel $kernel;

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
        $this->container = new ContainerBuilder();
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
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Exception
     */
    public function handle(Request $request): void
    {
        $response = $this->kernel->handle($request);
        $response->send();

        $this->kernel->terminate($request, $response);
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

    /**-
     * @param string $class
     *
     * @return object|null
     * @throws \Exception
     */
    public function get(string $class)
    {
        return $this->container->get($class);
    }

    /**
     * @return \Faker\Generator
     */
    public function getFaker(): Generator
    {
        return $this->faker;
    }

    /**
     * @param mixed ...$args
     *
     * @return string
     */
    public function basePath(...$args): string
    {
        return join(DIRECTORY_SEPARATOR, array_merge([$this->basePath], $args));
    }

    /**
     *
     * @throws \Exception
     */
    protected function onBooting(): void
    {
        $this->loadEnv();
        $this->bootHttpKernel();
        $this->bootEloquent();
        $this->initFaker();
        $this->applyProviders();
    }

    /**
     * @throws \Exception
     */
    protected function bootHttpKernel(): void
    {
        $routes = new RouteCollection();
        $routeFilePath = $this->basePath('routes', 'api.php');
        require_once $routeFilePath;

        $logFilePath = $this->basePath('storage', 'logs', 'runtime.log');
        $rotatingLogger = new RotatingFileHandler($logFilePath, 7);
        $logger = new Logger('runtime', [$rotatingLogger]);

        $matcher = new UrlMatcher($routes, new RequestContext());

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new RouterListener($matcher, new RequestStack(), null, null, $this->basePath, $this->env('APP_DEBUG')));
        $dispatcher->addSubscriber(new ErrorListener([Controller::class, 'exception'], $logger));

        $controllerResolver = new ControllerResolver();
        $argumentResolver = new ArgumentResolver();

        $this->kernel = new HttpKernel($dispatcher, $controllerResolver, new RequestStack(), $argumentResolver);
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
    protected function bootEloquent(): void
    {
        $capsule = new CapsuleManager();
        $capsule->addConnection([
            'driver' => $this->env('DB_DRIVER', 'pgsql'),
            'host' => $this->env('DB_HOST', 'localhost'),
            'port' => $this->env('DB_PORT', '5432'),
            'database' => $this->env('DB_DATABASE', 'database'),
            'username' => $this->env('DB_USERNAME', 'root'),
            'password' => $this->env('DB_PASSWORD', 'password'),
            'charset' => $this->env('DB_CHARSET', 'utf8'),
            'collation' => $this->env('DB_COLLATION', 'utf8_unicode_ci'),
        ]);

        $capsule->bootEloquent();
        $capsule->setAsGlobal();
    }

    /**
     *
     */
    protected function initFaker(): void
    {
        $this->faker = Faker::create();
        ProviderCollectionHelper::addAllProvidersTo($this->faker);
    }

    /**
     *
     */
    protected function applyProviders(): void
    {
        $finder = new Finder();
        $path = $this->basePath('app', 'Providers');
        $namespace = '\App\Providers\\';
        /**
         * @var \SplFileInfo $fileInfo
         */
        foreach ($finder->in($path)->files() as $fileInfo) {
            $className = $namespace . $fileInfo->getFilenameWithoutExtension();
            $object = new $className();
            $call = [$object, 'provide'];
            if (is_callable($call)) {
                call_user_func($call, $this->container);
            }
        }
    }
}