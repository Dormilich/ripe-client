<?php declare(strict_types=1);

namespace Dormilich\RipeClient\Authorization;

use Psr\Http\Message\RequestInterface;

/**
 * Do not add authorization to the request.
 */
class NoAuth implements AuthorizationInterface
{
    /**
     * @inheritDoc
     */
    public function authorize(RequestInterface $request): RequestInterface
    {
        return $request;
    }
}
