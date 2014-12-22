<?php

namespace Intaro\HStoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class IntaroHStoreExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);

        $dbalConfig = [
            'dbal' => [
                'types' => [
                    'hstore'        => 'Intaro\HStoreBundle\DBAL\Types\HStoreType'
                ],
                'mapping_types' => [
                    'hstore'        => 'hstore'
                ],
            ],
            'orm' => [
                'dql' => [
                    'string_functions' => [
                        'contains'          => 'Intaro\HStoreBundle\DQL\ContainsFunction',
                        'defined'           => 'Intaro\HStoreBundle\DQL\DefinedFunction',
                        'hstoreDifference'  => 'Intaro\HStoreBundle\DQL\HstoreDifferenceFunction',
                        'fetchval'          => 'Intaro\HStoreBundle\DQL\FetchvalFunction'
                    ]
                ]
            ]
        ];

        $container->prependExtensionConfig('doctrine', $dbalConfig);
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
