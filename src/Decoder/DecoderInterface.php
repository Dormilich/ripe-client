<?php

namespace Dormilich\RipeClient\Decoder;

use Dormilich\RipeClient\Exception\DecoderException;
use Psr\Http\Message\ResponseInterface;

interface DecoderInterface
{
    /**
     * The HTTP Content-Type that the decoder can parse.
     *
     * @return string
     */
    public function getContentType(): string;

    /**
     * Test whether the decoder can process the response.
     *
     * @param ResponseInterface $response
     * @return bool
     */
    public function supports(ResponseInterface $response): bool;

    /**
     * Returns the object type and the attribute values.
     *
     * @param string $content Response body.
     * @return Result
     * @throws DecoderException
     */
    public function decode(string $content): Result;
}
