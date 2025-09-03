<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\ServiceManager\ServiceManager;
use Asgrim\MiniMezzio\AppFactory;
use Mezzio\Router\FastRouteRouter;
use Mezzio\Router\Middleware\DispatchMiddleware;
use Mezzio\Router\Middleware\RouteMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Mezzio\Twig\ConfigProvider as MezzioTwigConfigProvider;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Twig\Environment;
use Twig\Extra\Intl\IntlExtension;

require __DIR__ . '/../vendor/autoload.php';

$config                                       = new ConfigAggregator([
    MezzioTwigConfigProvider::class,
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
                    ]
                ],
            ];
        }
    },
])->getMergedConfig();
$dependencies                                 = $config['dependencies'];
$dependencies['services']['config']           = $config;
$container                                    = new ServiceManager($dependencies);
$router = new FastRouteRouter();
$app = AppFactory::create($container, $router);
$app->pipe(new RouteMiddleware($router));
$app->pipe(new DispatchMiddleware());

readonly class BaseRequest
{
    protected Environment $twig;
    protected TemplateRendererInterface $view;

    public function __construct(protected ContainerInterface $container)
    {
        $this->twig = $this->container->get(Environment::class);
        $this->view = $this->container->get(TemplateRendererInterface::class);
    }
}

// View all notes paginated (sorted and filtered if desired)
$app->get('/[{page:\d+}[/{sort:\d+}[/{category:\d+}]]]',
    new readonly class($container) extends BaseRequest implements RequestHandlerInterface {
        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            $this->twig->addGlobal('show_create_button', true);
            return new HtmlResponse($this->view->render('app::view-notes', [

            ]));
        }
    }
);

// Display the form to create a new note
$app->get('/create',
    new readonly class($container) extends BaseRequest implements RequestHandlerInterface {
        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            return new HtmlResponse($this->view->render('app::manage-note', [

            ]));
        }
    }
);

// Create a new note
$app->post('/create', new class implements RequestHandlerInterface {
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new TextResponse('Hello world!');
    }
});

// Display the form to edit an existing note
$app->get('/edit/{id:\d+}',
    new readonly class($container) extends BaseRequest implements RequestHandlerInterface {
        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            return new HtmlResponse($this->view->render('app::manage-note', [

            ]));
        }
    }
);

// Edit an existing note
$app->post('/edit/{id:\d+}', new class implements RequestHandlerInterface {
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new TextResponse('Hello world!');
    }
});

// Delete an existing note
$app->post('/delete/{id:\d+}', new class implements RequestHandlerInterface {
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new TextResponse('Hello world!');
    }
});

$app->run();
