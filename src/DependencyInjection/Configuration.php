<?php

namespace Starfruit\EcommerceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('starfruit_ecommerce');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('view_templates')
                    ->children()
                        ->arrayNode('cart')
                            ->children()
                                ->scalarNode('default')
                                    ->info('default view for cart')
                                    ->defaultValue('cart/cart.html.twig')
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('checkout')
                            ->children()
                                ->scalarNode('address')
                                    ->info('view for checkout address step')
                                    ->defaultValue('checkout/address.html.twig')
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
