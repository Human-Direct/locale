<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\DependencyInjection;

use HumanDirect\LocaleBundle\Matcher\BestLocaleMatcher;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class HumanDirectLocaleExtension extends Extension
{
    /**
     * {@inheritdoc}
     *
     * @param array<string, mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $this->bindParameters($container, $this->getAlias(), $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(
            __DIR__ . '/../Resources/config'
        ));
        $loader->load('services.yaml');

        if (!$config['strict_match']) {
            $container->removeDefinition(BestLocaleMatcher::class);
        }
    }

    /**
     * Binds the params from config.
     *
     * @param string $name Alias name
     * @param bool|int|string|array<string|int, bool|int|string|null>|null $config Configuration Array
     */
    public function bindParameters(ContainerBuilder $container, string $name, array|bool|int|string|null $config): void
    {
        if (\is_array($config) && empty($config[0])) {
            foreach ($config as $key => $value) {
                if ('locale_map' === $key) {
                    // need an associative array here
                    $container->setParameter($name . '.' . $key, $value);
                } else {
                    $this->bindParameters($container, $name . '.' . $key, $value);
                }
            }
        } else {
            $container->setParameter($name, $config);
        }
    }
}
