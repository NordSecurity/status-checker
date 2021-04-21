<?php

declare(strict_types=1);

namespace Nordsec\StatusChecker\Controllers;

use Nordsec\StatusChecker\Services\StatusCheckerService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class StatusController
{
    private $statusCheckerService;

    public function __construct(StatusCheckerService $statusCheckerService)
    {
        $this->statusCheckerService = $statusCheckerService;
    }

    public function index(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $status = $this->statusCheckerService->checkGlobalStatus();

        return $this->toJson($response, ['status' => $status]);
    }

    public function details(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->toJson($response, $this->statusCheckerService->getDetails());
    }

    protected function toJson(ResponseInterface $response, $content): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $encodedContent = json_encode($content, JSON_THROW_ON_ERROR);
        $response->getBody()->write($encodedContent);
        $response = $response->withAddedHeader('Content-Type', 'application/json');

        return $response;
    }
}
