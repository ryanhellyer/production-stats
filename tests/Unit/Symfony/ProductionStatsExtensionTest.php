<?php

declare(strict_types=1);

namespace RyanHellyer\ProductionStats\Tests\Unit\Symfony;

use PHPUnit\Framework\TestCase;
use RyanHellyer\ProductionStats\Core\HtmlResponseInjector;
use RyanHellyer\ProductionStats\Symfony\DependencyInjection\ProductionStatsExtension;
use RyanHellyer\ProductionStats\Symfony\EventSubscriber\InjectLoadTimeSubscriber;
use RyanHellyer\ProductionStats\Symfony\ProductionStatsBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ProductionStatsExtensionTest extends TestCase
{
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new ContainerBuilder();
        (new ProductionStatsExtension())->load([], $this->container);
    }

    public function testBundleExposesExtensionWithConventionAlias(): void
    {
        $bundle = new ProductionStatsBundle();
        $extension = $bundle->getContainerExtension();

        $this->assertInstanceOf(ProductionStatsExtension::class, $extension);
        $this->assertSame('production_stats', $extension->getAlias());
    }

    public function testSubscriberIsRegisteredAutowiredAndAutoconfigured(): void
    {
        $this->assertTrue($this->container->hasDefinition(InjectLoadTimeSubscriber::class));

        $definition = $this->container->getDefinition(InjectLoadTimeSubscriber::class);

        $this->assertTrue($definition->isAutowired());
        $this->assertTrue($definition->isAutoconfigured());
    }

    public function testInjectorIsRegisteredForAutowiring(): void
    {
        $this->assertTrue($this->container->hasDefinition(HtmlResponseInjector::class));
    }
}
