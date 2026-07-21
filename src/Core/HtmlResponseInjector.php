<?php

declare(strict_types=1);

namespace RyanHellyer\ProductionStats\Core;

use Symfony\Component\HttpFoundation\Response;

class HtmlResponseInjector
{
    public function inject(Response $response, float $elapsedMs): Response
    {
        if (!$this->isHtmlResponse($response)) {
            return $response;
        }

        $content = $response->getContent();

        if ($content === false || $content === '') {
            return $response;
        }

        $comment = $this->formatComment($elapsedMs);
        $content = str_replace('</body>', $comment . '</body>', $content);

        $response->setContent($content);

        return $response;
    }

    public function isHtmlResponse(Response $response): bool
    {
        $contentType = (string) $response->headers->get('Content-Type', '');

        return str_contains($contentType, 'text/html');
    }

    public function formatComment(float $elapsedMs): string
    {
        $timestamp = date('Y-m-d H:i:s');

        return sprintf(
            "\n<!-- Page generated in %d ms at %s -->\n",
            (int) $elapsedMs,
            $timestamp
        );
    }
}
