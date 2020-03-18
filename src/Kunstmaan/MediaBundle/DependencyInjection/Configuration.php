<?php

namespace Kunstmaan\MediaBundle\DependencyInjection;

use Kunstmaan\MediaBundle\Utils\SymfonyVersion;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('kunstmaan_media');
        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('kunstmaan_media');
        }

        $rootNode
            ->children()
                ->scalarNode('soundcloud_api_key')->defaultValue('YOUR_CLIENT_ID')->end()
                ->scalarNode('aviary_api_key')->setDeprecated('The child node "%node%" at path "%path%" is deprecated. Because the aviary service is discontinued.')->defaultNull()->end()
                ->arrayNode('remote_video')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('vimeo')->defaultTrue()->end()
                        ->booleanNode('youtube')->defaultTrue()->end()
                        ->booleanNode('dailymotion')->defaultTrue()->end()
                    ->end()
                ->end()
                ->booleanNode('enable_pdf_preview')->defaultFalse()->end()
                ->arrayNode('blacklisted_extensions')
                    ->defaultValue(['php', 'htaccess'])
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('web_root')
                    ->defaultValue(SymfonyVersion::getRootWebPath())
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('cropping_views')
                    ->children()
                        ->arrayNode('default')
                            ->arrayPrototype()
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('name')->end()
                                    ->integerNode('width')->end()
                                    ->integerNode('height')->end()
                                    ->booleanNode('lockRatio')->defaultTrue()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('focus_point_classes')
                            ->defaultValue(['top-left' ,'top-center', 'top-right', 'center-left', 'center', 'center-right', 'bottom-left', 'bottom-center', 'bottom-right'])
                            ->prototype('array')->end()
                        ->end()
                        ->arrayNode('custom_views')
                            ->useAttributeAsKey('groupName')
                            ->arrayPrototype()
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->booleanNode('useFocusPoint')->defaultFalse()->end()
                                    ->arrayNode('views')
                                        ->arrayPrototype()
                                            ->addDefaultsIfNotSet()
                                            ->children()
                                                ->scalarNode('name')->end()
                                                ->integerNode('width')->end()
                                                ->integerNode('height')->end()
                                                ->booleanNode('lockRatio')->defaultTrue()->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
