<?php

namespace Dormilich\RipeClient\Encoder;

use Dormilich\RIPE\RipeInterface;

interface EncoderInterface
{
    /**
     * The HTTP Content-Type that the encoder provides.
     *
     * @return string
     */
    public function getContentType(): string;

    /**
     * The request body content based on the content type.
     *
     * @param RipeInterface $object
     * @return string
     */
    public function encode(RipeInterface $object): string;
}
