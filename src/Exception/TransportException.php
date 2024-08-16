<?php

namespace Dormilich\RipeClient\Exception;

use Exception;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Throwable;

use function sprintf;

/**
 * Thrown when there is a problem retrieving the response. Usually wraps an
 * exception from the HTTP client.
 */
class TransportException extends Exception implements ExceptionInterface
{
    private RequestInterface $request;

    /**
     * @param NetworkExceptionInterface|RequestExceptionInterface $exception
     * @return self
     */
    public static function from(NetworkExceptionInterface|RequestExceptionInterface $exception): self
    {
        $request = $exception->getRequest();
        $message = sprintf('Failed to get response for "%s".', $request->getUri()->getPath());

        return new self($request, $message, $exception);
    }

    /**
     * @param RequestInterface $request
     * @param string $message
     * @param Throwable|null $previous
     */
    public function __construct(RequestInterface $request, string $message = '', Throwable $previous = null)
    {
        $this->request = $request;
        parent::__construct($message, 0, $previous);
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
