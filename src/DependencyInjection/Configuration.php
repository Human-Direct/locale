<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('human_direct_locale');

        $treeBuilder->getRootNode()
            ->fixXmlConfig('allowed_locale')
            ->children()
                ->scalarNode('strict_mode')
                ->defaultFalse()
            ->end()
            ->scalarNode('strict_match')
                ->defaultFalse()
            ->end()
            ->booleanNode('disable_vary_header')
                ->defaultFalse()
            ->end()
            ->arrayNode('allowed_locales')
                ->requiresAtLeastOneElement()
                ->prototype('scalar')->end()
            ->end()
            ->scalarNode('guessing_excluded_pattern')
                ->defaultNull()
            ->end()
            ->arrayNode('guessing_order')
                ->isRequired()
                ->beforeNormalization()
                    ->ifString()
                    ->then(fn ($v): array => [$v])
                ->end()
                ->isRequired()
                ->requiresAtLeastOneElement()
                    ->prototype('scalar')->end()
                ->end()
            ->arrayNode('cookie')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('set_on_change')->defaultTrue()->end()
                    ->scalarNode('name')->defaultValue('human_direct_locale')->end()
                    ->scalarNode('ttl')->defaultValue(86400)->end()
                    ->scalarNode('path')->defaultValue('/')->end()
                    ->scalarNode('domain')->defaultValue(null)->end()
                    ->scalarNode('secure')->defaultFalse()->end()
                    ->scalarNode('httpOnly')->defaultTrue()->end()
                ->end()
            ->end()
            ->arrayNode('session')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('variable')->defaultValue('human_direct_locale')->end()
                ->end()
            ->end()
            ->arrayNode('query')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('parameter_name')->defaultValue('_locale')->end()
                ->end()
            ->end()
            ->arrayNode('subdomain')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('region_separator')->defaultValue('_')->end()
                ->end()
            ->end()
            ->arrayNode('topleveldomain')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('locale_map')
                        ->useAttributeAsKey('name')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('domain')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('locale_map')
                        ->normalizeKeys(false)
                        ->useAttributeAsKey('name')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('switcher')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('show_current_locale')->defaultFalse()->end()
                ->end()
            ->end()
        ->end()
        ;

        return $treeBuilder;
    }
}
