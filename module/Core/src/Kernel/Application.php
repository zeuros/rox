<?php

namespace Rox\Core\Kernel;

use EnvironmentExplorer;
use Rox\Core\Loader\ConfigLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Debug\Debug;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class Application
 */
class Application extends Kernel
{
    public function boot()
    {
        if ($this->isDebug()) {
            $this->disableHoaAutoload();

            Debug::enable();
        }

        $environmentExplorer = new EnvironmentExplorer();

        $environmentExplorer->initializeGlobalState();

        parent::boot();
    }

    public function registerBundles()
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) {
            call_user_func(new ConfigLoader(), $container);
        });
    }

    public function getRootDir()
    {
        return getcwd();
    }

    /**
     * @return HttpKernelInterface
     */
    protected function getLegacyHttpKernel()
    {
        return $this->getContainer()->get(LegacyHttpKernel::class);
    }

    /**
     * PhpMetrics has a sub-dependency Hoa\Core, which registers an autoloader
     * that prevents xdebug breakpoints from working with Laravel packages.
     */
    protected function disableHoaAutoload()
    {
        if (!is_array($functions = spl_autoload_functions())) {
            return;
        }

        foreach ($functions as $function) {
            if ($function[0] === \Hoa\Core\Consistency\Consistency::class) {
                spl_autoload_unregister('\Hoa\Core\Consistency::autoload');
            }
        }
    }
}
