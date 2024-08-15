<?php

namespace Dormilich\RipeClient\Tests\Decoder;

use Dormilich\RipeClient\Decoder\Result;
use Dormilich\RipeClient\Decoder\XmlDecoder;
use Dormilich\RipeClient\Exception\DecoderException;
use Dormilich\RPSL\Attribute\Value;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(XmlDecoder::class)]
#[UsesClass(Result::class)]
class XmlDecoderTest extends TestCase
{
    #[Test, TestDox('has XML content type')]
    public function content_type()
    {
        $decoder = new XmlDecoder();

        $this->assertSame('application/xml', $decoder->getContentType());
    }

    #[Test, TestDox('gets object attributes from XML')]
    public function decode_object()
    {
        $xml = file_get_contents(__DIR__ . '/decode.poem.xml');

        $decoder = new XmlDecoder();
        $result = $decoder->decode($xml);

        $this->assertSame('poem', $result->getType());
        $this->assertSame('1.113.2', $result->getVersion());
        $this->assertCount(0, $result->getErrors());
        $this->assertCount(11, $result);
        $this->assertContainsOnlyInstancesOf(Value::class, $result);
    }

    #[Test, TestDox('gets error for failed request')]
    public function decode_error()
    {
        $xml = file_get_contents(__DIR__ . '/decode.error.xml');

        $decoder = new XmlDecoder();
        $result = $decoder->decode($xml);

        $this->assertSame('person', $result->getType());
        $this->assertCount(1, $result->getErrors());
        $this->assertSame(['"admin-c" is not valid for this object type'], $result->getErrors());
    }

    #[Test, TestDox('cannot parse non-XML content')]
    public function response_failure()
    {
        $this->expectException(DecoderException::class);
        $this->expectExceptionMessage('Failed to parse content as XML');

        $json = file_get_contents(__DIR__ . '/decode.poem.json');

        $decoder = new XmlDecoder();
        $decoder->decode($json);
    }
}
