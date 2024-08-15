<?php

namespace Dormilich\RipeClient\Authorization;

use Psr\Http\Message\RequestInterface;

interface AuthorizationInterface
{
    /**
     * Add user authorization to the request.
     *
     * @param RequestInterface $request
     * @return RequestInterface
     */
    public function authorize(RequestInterface $request): RequestInterface;
}
