<?php

declare(strict_types=1);

namespace RyanHellyer\ProductionStats\Tests\Unit\Symfony;

use PHPUnit\Framework\TestCase;
use RyanHellyer\ProductionStats\Core\HtmlResponseInjector;
use RyanHellyer\ProductionStats\Symfony\EventSubscriber\InjectLoadTimeSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class InjectLoadTimeSubscriberTest extends TestCase
{
    private HtmlResponseInjector $injector;
    private InjectLoadTimeSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();
        $this->injector = new HtmlResponseInjector();
        $this->subscriber = new InjectLoadTimeSubscriber($this->injector);
    }

    public function testDelegatesToCoreInjector(): void
    {
        $response = new Response('<html><body>Test</body></html>', 200, [
            'Content-Type' => 'text/html',
        ]);

        $this->subscriber->onKernelRequest($this->createRequestEvent());
        $this->subscriber->onKernelResponse($this->createResponseEvent($response));

        $content = (string) $response->getContent();
        $this->assertStringContainsString('<!-- Page generated in', $content);
        $this->assertStringContainsString('ms at', $content);
        $this->assertStringContainsString('-->', $content);
    }

    public function testSkipsSubRequest(): void
    {
        $response = new Response('<html><body>Test</body></html>', 200, [
            'Content-Type' => 'text/html',
        ]);

        $this->subscriber->onKernelRequest($this->createRequestEvent(HttpKernelInterface::SUB_REQUEST));
        $this->subscriber->onKernelResponse($this->createResponseEvent($response, HttpKernelInterface::SUB_REQUEST));

        $this->assertStringNotContainsString('<!-- Page generated in', (string) $response->getContent());
    }

    public function testZeroElapsedWhenNoRequestEventFired(): void
    {
        $response = new Response('<html><body>Test</body></html>', 200, [
            'Content-Type' => 'text/html',
        ]);

        $this->subscriber->onKernelResponse($this->createResponseEvent($response));

        $content = (string) $response->getContent();
        $this->assertStringContainsString('<!-- Page generated in 0 ms at', $content);
    }

    public function testSubscribedEvents(): void
    {
        $events = InjectLoadTimeSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::REQUEST, $events);
        $this->assertArrayHasKey(KernelEvents::RESPONSE, $events);
        $this->assertSame(2048, $events[KernelEvents::REQUEST][1]);
        $this->assertSame(-256, $events[KernelEvents::RESPONSE][1]);
    }

    private function createRequestEvent(int $requestType = HttpKernelInterface::MAIN_REQUEST): RequestEvent
    {
        $kernel = $this->createStub(HttpKernelInterface::class);

        return new RequestEvent($kernel, new Request(), $requestType);
    }

    private function createResponseEvent(
        Response $response,
        int $requestType = HttpKernelInterface::MAIN_REQUEST
    ): ResponseEvent {
        $kernel = $this->createStub(HttpKernelInterface::class);

        return new ResponseEvent($kernel, new Request(), $requestType, $response);
    }
}
