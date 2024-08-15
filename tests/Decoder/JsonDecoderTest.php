<?php

namespace Dormilich\RipeClient\Tests\Decoder;

use Dormilich\RipeClient\Decoder\JsonDecoder;
use Dormilich\RipeClient\Decoder\Result;
use Dormilich\RipeClient\Exception\DecoderException;
use Dormilich\RPSL\Attribute\Value;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonDecoder::class)]
#[UsesClass(Result::class)]
class JsonDecoderTest extends TestCase
{
    #[Test, TestDox('has JSON content type')]
    public function content_type()
    {
        $decoder = new JsonDecoder();

        $this->assertSame('application/json', $decoder->getContentType());
    }

    #[Test, TestDox('gets object attributes from JSON')]
    public function decode_object()
    {
        $json = file_get_contents(__DIR__ . '/decode.poem.json');

        $decoder = new JsonDecoder();
        $result = $decoder->decode($json);

        $this->assertSame('poem', $result->getType());
        $this->assertSame('1.113.2', $result->getVersion());
        $this->assertCount(0, $result->getErrors());
        $this->assertCount(11, $result);
        $this->assertContainsOnlyInstancesOf(Value::class, $result);
    }

    #[Test, TestDox('gets error for failed request')]
    public function decode_error()
    {
        $json = file_get_contents(__DIR__ . '/decode.error.json');

        $decoder = new JsonDecoder();
        $result = $decoder->decode($json);

        $this->assertSame('person', $result->getType());
        $this->assertCount(1, $result->getErrors());
        $this->assertSame(['Unrecognized source: INVALID_SOURCE'], $result->getErrors());
    }

    #[Test, TestDox('cannot parse non-JSON content')]
    public function response_failure()
    {
        $this->expectException(DecoderException::class);
        $this->expectExceptionMessage('Failed to parse content as JSON');

        $xml = file_get_contents(__DIR__ . '/decode.poem.xml');

        $decoder = new JsonDecoder();
        $decoder->decode($xml);
    }
}
