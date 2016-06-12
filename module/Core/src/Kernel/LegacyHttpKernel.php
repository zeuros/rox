<?php

namespace Rox\Core\Kernel;

use RoxFrontRouter;
use SessionMemory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Fallback dispatcher for requests that Symfony couldn't match.
 */
class LegacyHttpKernel extends HttpKernel
{
    /**
     * @var EngineInterface
     */
    protected $engine;

    public function __construct(
        EngineInterface $engine,
        EventDispatcherInterface $dispatcher,
        ControllerResolverInterface $resolver,
        RequestStack $requestStack
    ) {
        $this->engine = $engine;

        parent::__construct($dispatcher, $resolver, $requestStack);
    }

    /**
     * @param Request $request
     * @param int     $type
     * @param bool    $catch
     *
     * @return Response
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $router = new RoxFrontRouter($this->engine);

        $router->session_memory = new SessionMemory('SessionMemory');

        ob_start();

        $router->route();

        $content = ob_get_clean();

        // RoxFrontRouter::route_normal() sends a redirect without setting 301/302
        // Here we can take such redirect and do it with RedirectResponse
        if ($content === '' && !headers_sent()) {
            foreach (headers_list() as $header) {
                if (preg_match('/^Location: (.*)$/', $header, $matches)) {
                    return new RedirectResponse($matches[1]);
                }
            }
        }

        return new Response($content);
    }
}
