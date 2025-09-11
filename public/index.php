<?php

declare(strict_types=1);

use App\InputFilter\NoteInputFilter;
use App\Service\DatabaseService;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Filter\ConfigProvider as FilterConfigProvider;
use Laminas\InputFilter\ConfigProvider as InputFilterConfigProvider;
use Laminas\InputFilter\Factory;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Asgrim\MiniMezzio\AppFactory;
use Laminas\Validator\ConfigProvider as ValidatorConfigProvider;
use Mezzio\ConfigProvider as MezzioConfigProvider;
use Mezzio\Handler\NotFoundHandler;
use Mezzio\Router\FastRouteRouter;
use Mezzio\Router\Middleware\DispatchMiddleware;
use Mezzio\Router\Middleware\RouteMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Mezzio\Twig\ConfigProvider as MezzioTwigConfigProvider;
use PhpDb\Adapter\AdapterAbstractServiceFactory;
use PhpDb\Adapter\Sqlite\ConfigProvider as PhpDbSqliteConfigProvider;
use PhpDb\Container\AdapterManager;
use PhpDb\Container\ConfigProvider as PhpDbConfigProvider;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Twig\Environment;
use Twig\Extra\Intl\IntlExtension;
use Twig\Extra\Markdown\DefaultMarkdown;
use Twig\Extra\Markdown\MarkdownExtension;
use Twig\Extra\Markdown\MarkdownRuntime;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

require __DIR__ . '/../vendor/autoload.php';

$config                                       = new ConfigAggregator([
    MezzioConfigProvider::class,
    MezzioTwigConfigProvider::class,
    FilterConfigProvider::class,
    InputFilterConfigProvider::class,
    PhpDbConfigProvider::class,
    PhpDbSqliteConfigProvider::class,
    ValidatorConfigProvider::class,
    \App\ConfigProvider::class,
    new class ()
    {
        public function __invoke(): array
        {
            return [
                'templates' => [
                    'paths' => [
                        'app'    => [__DIR__ . '/../templates/app'],
                        'error'  => [__DIR__ . '/../templates/error'],
                        'layout' => [__DIR__ . '/../templates/layout'],
                        '__main__'    => [__DIR__ . '/../templates/layout'],
                    ],
                ],
                'twig' => [
                    'extensions' => [
                        new IntlExtension(),
                        new MarkdownExtension(),
                    ],
                    'runtime_loaders' => [
                        new class implements RuntimeLoaderInterface {
                            public function load($class) {
                                if (MarkdownRuntime::class === $class) {
                                    return new MarkdownRuntime(new DefaultMarkdown());
                                }
                            }
                        },
                    ],
                ],
                'db' => [
                    'driver'       => 'sqlite',
                    'connection' => [
                        'dsn'      => __DIR__ . '/../data/database/database.sqlite3',
                        'charset'  => 'utf8',
                        'driver_options' => [],
                    ],
                ],
            ];
        }
    },
])->getMergedConfig();
$dependencies                                 = $config['dependencies'];
$dependencies['services']['config']           = $config;
$container                                    = new ServiceManager($dependencies);
$container->setService(
    DatabaseService::class,
    new DatabaseService(
        $container->get(\PhpDb\Adapter\AdapterInterface::class)
    )
);

/** @var InputFilterPluginManager $pluginManager */
$pluginManager = $container->get(InputFilterPluginManager::class);
$container->setService(
    NoteInputFilter::class,
    $pluginManager->get(NoteInputFilter::class)
);
$router = new FastRouteRouter();
$app = AppFactory::create($container, $router);
$app->pipe(new RouteMiddleware($router));
$app->pipe(new DispatchMiddleware());

readonly class BaseRequest
{
    protected DatabaseService $databaseService;
    protected Environment $twig;
    protected NoteInputFilter $noteInputFilter;
    protected TemplateRendererInterface $view;

    public function __construct(protected ContainerInterface $container)
    {
        $this->databaseService = $this->container->get(DatabaseService::class);
        $this->noteInputFilter = $this->container->get(NoteInputFilter::class);
        $this->twig            = $this->container->get(Environment::class);
        $this->view            = $this->container->get(TemplateRendererInterface::class);
    }
}

$app->get('/404', NotFoundHandler::class);

// View all notes paginated (sorted and filtered if desired)
$app->get('/[{page:\d+}[/{sort:\d+}[/{category:\d+}]]]',
    new readonly class($container) extends BaseRequest implements RequestHandlerInterface {
        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            $this->twig->addGlobal('show_create_button', true);
            return new HtmlResponse(
                $this->view->render(
                    'app::view-notes',
                    [
                        'notes' => $this->databaseService->select(),
                    ]
                )
            );
        }
    }
);

// Display the form to create a new note
$app->get('/manage[/{id:\d+}]',
    new readonly class($container) extends BaseRequest implements RequestHandlerInterface
    {
        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            $data = [];

            if ($request->getAttribute('id') !== null) {
                if (! $this->databaseService->noteExists((int) $request->getAttribute('id'))) {
                    return new RedirectResponse('/404');
                }

                $data['note'] = $this->databaseService
                    ->select(
                        [
                            'id' => $request->getAttribute('id')
                        ]
                    )->current();
            }

            return new HtmlResponse($this->view->render('app::manage-note', $data));
        }
    }
);

// Create a new note
$app->post('/manage[/{id:\d+}]',
    new readonly class($container) extends BaseRequest implements RequestHandlerInterface
    {
        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            $this->noteInputFilter->setData($request->getParsedBody());
            if (! $this->noteInputFilter->isValid()) {
                return new RedirectResponse('/manage');
            }

            $noteId = $this->noteInputFilter->get('id')->getValue();

            if ($noteId !== null) {
                if (! $this->databaseService->noteExists((int) $noteId)) {
                    return new RedirectResponse('/404');
                }

                $this->databaseService->update(
                    $this->noteInputFilter->getValues(),
                    [
                        'id' => $this->noteInputFilter->getValue('id')
                    ]
                );
                return new RedirectResponse(
                    sprintf(
                        '/manage/%d',
                        $this->noteInputFilter->getValues()['id']
                    )
                );
            }

            $this->databaseService->insert($this->noteInputFilter->getValues());
            return new RedirectResponse(
                sprintf(
                    '/manage/%d',
                    $this->databaseService->getLastInsertValue()
                )
            );
        }
    }
);

// Delete an existing note
$app->post('/delete', new readonly class($container) extends BaseRequest implements RequestHandlerInterface {
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $noteId = $request->getParsedBody()['id'] ?? null;
        if ($noteId === null || ! $this->databaseService->noteExists((int) $noteId)) {
            return new RedirectResponse('/404');
        }

        $this->databaseService->delete([
            'id' => $noteId,
        ]);
        return new RedirectResponse('/');
    }
});

$app->run();
