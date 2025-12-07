<?php

declare(strict_types=1);

namespace RyanHellyer\ProductionStats\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectLoadTime
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only process HTML responses
        if ($this->isHtmlResponse($response)) {
            $content = $response->getContent();

            if ($content !== false) {
 
                $loadTime = $this->calculateLoadTime();

                $loadTimeHtml = $this->formatLoadTimeHtml($loadTime);

                // Inject before closing </body> tag
                $content = str_replace('</body>', $loadTimeHtml . '</body>', $content);

                $response->setContent($content);
            }
        }

        return $response;
    }

    protected function isHtmlResponse(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');
        
        // Only process actual HTML responses to avoid breaking JSON, XML, etc.
        return str_contains($contentType, 'text/html');
    }

    protected function calculateLoadTime(): float
    {
        if (defined('LARAVEL_START')) {
            return round((microtime(true) - LARAVEL_START) * 1000, 2);
        }
        
        return 0;
    }

    protected function formatLoadTimeHtml(float $loadTime): string
    {
        $timestamp = date('Y-m-d H:i:s');

        return sprintf(
            "\n<!-- Page generated in %d ms at %s -->\n",
            (int)$loadTime,
            $timestamp
        );
    }
}
