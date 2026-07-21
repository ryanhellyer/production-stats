<?php

declare(strict_types=1);

namespace RyanHellyer\ProductionStats\Symfony\EventSubscriber;

use RyanHellyer\ProductionStats\Core\HtmlResponseInjector;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class InjectLoadTimeSubscriber implements EventSubscriberInterface
{
    private HtmlResponseInjector $injector;
    private ?float $startTime = null;

    public function __construct(?HtmlResponseInjector $injector = null)
    {
        $this->injector = $injector ?? new HtmlResponseInjector();
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->startTime = microtime(true);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $elapsedMs = $this->startTime !== null
            ? round((microtime(true) - $this->startTime) * 1000, 2)
            : 0;

        $response = $this->injector->inject($event->getResponse(), $elapsedMs);
        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST  => ['onKernelRequest', 2048],
            KernelEvents::RESPONSE => ['onKernelResponse', -256],
        ];
    }
}
