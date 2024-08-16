<?php

namespace Dormilich\RipeClient\Exception;

use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Thrown when the request does not complete successfully.
 */
class RequestException extends Exception implements ExceptionInterface
{
    private RequestInterface $request;

    private ResponseInterface $response;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param string $message
     */
    public function __construct(RequestInterface $request, ResponseInterface $response, string $message)
    {
        $this->request = $this->stripQuery($request);
        $this->response = $response;

        parent::__construct($message);
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Remove URL query to avoid logging passwords.
     *
     * @param RequestInterface $request
     * @return RequestInterface
     */
    private function stripQuery(RequestInterface $request): RequestInterface
    {
        $uri = $request->getUri()->withQuery('');

        return $request->withUri($uri);
    }
}
