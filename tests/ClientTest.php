<?php

namespace Dormilich\RipeClient\Tests;

use Dormilich\RIPE\RipeInterface;
use Dormilich\RipeClient\Authorization\AuthorizationInterface;
use Dormilich\RipeClient\Authorization\NoAuth;
use Dormilich\RipeClient\Client;
use Dormilich\RipeClient\Decoder\DecoderInterface;
use Dormilich\RipeClient\Decoder\Result;
use Dormilich\RipeClient\Encoder\EncoderInterface;
use Dormilich\RipeClient\Exception\DecoderException;
use Dormilich\RipeClient\Exception\RequestException;
use Dormilich\RipeClient\Exception\TransportException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

#[CoversClass(Client::class)]
#[UsesClass(NoAuth::class)]
class ClientTest extends TestCase
{
    #[Test, TestDox('makes a GET request')]
    public function read_request()
    {
        $uri = $this->createStub(UriInterface::class);

        $result = $this->createStub(Result::class);

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('withHeader')
            ->with(
                $this->identicalTo('Accept'),
                $this->identicalTo('application/octet-stream')
            )
            ->willReturnSelf();
        $request
            ->expects($this->never())
            ->method('withBody');

        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => 'test',
            ]),
            'getStatusCode' => 200,
        ]);

        $encoder = $this->createStub(EncoderInterface::class);

        $decoder = $this->createConfiguredMock(DecoderInterface::class, [
            'getContentType' => 'application/octet-stream',
            'supports' => true,
        ]);
        $decoder
            ->expects($this->once())
            ->method('decode')
            ->with(
                $this->identicalTo('test')
            )
            ->willReturn($result);

        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response);

        $requester = $this->createMock(RequestFactoryInterface::class);
        $requester
            ->expects($this->once())
            ->method('createRequest')
            ->with(
                $this->identicalTo('GET'),
                $this->identicalTo($uri)
            )
            ->willReturn($request);

        $streamer = $this->createStub(StreamFactoryInterface::class);

        $service = new Client($encoder, $decoder, $client, $requester, $streamer);
        $data = $service->submit('GET', $uri);

        $this->assertSame($result, $data);
    }

    #[Test, TestDox('makes a POST request')]
    public function object_request()
    {
        $uri = $this->createStub(UriInterface::class);
        $object = $this->createStub(RipeInterface::class);
        $stream = $this->createStub(StreamInterface::class);
        $result = $this->createStub(Result::class);

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->exactly(3))
            ->method('withHeader')
            ->willReturnSelf();
        $request
            ->expects($this->once())
            ->method('withBody')
            ->with(
                $this->identicalTo($stream)
            )
            ->willReturnSelf();

        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => 'test',
            ]),
            'getStatusCode' => 200,
        ]);

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth
            ->expects($this->once())
            ->method('authorize')
            ->willReturnArgument(0);

        $encoder = $this->createConfiguredMock(EncoderInterface::class, [
            'getContentType' => 'text/plain',
        ]);
        $encoder
            ->expects($this->once())
            ->method('encode')
            ->with(
                $this->identicalTo($object)
            )
            ->willReturn('test');

        $decoder = $this->createConfiguredMock(DecoderInterface::class, [
            'getContentType' => 'application/octet-stream',
            'supports' => true,
        ]);
        $decoder
            ->expects($this->once())
            ->method('decode')
            ->with(
                $this->identicalTo('test')
            )
            ->willReturn($result);

        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response);

        $requester = $this->createMock(RequestFactoryInterface::class);
        $requester
            ->expects($this->once())
            ->method('createRequest')
            ->with(
                $this->identicalTo('POST'),
                $this->identicalTo($uri)
            )
            ->willReturn($request);

        $streamer = $this->createMock(StreamFactoryInterface::class);
        $streamer
            ->expects($this->once())
            ->method('createStream')
            ->with(
                $this->identicalTo('test')
            )
            ->willReturn($stream);

        $service = new Client($encoder, $decoder, $client, $requester, $streamer);
        $service->setAuthorization($auth);
        $data = $service->submit('POST', $uri, $object);

        $this->assertSame($result, $data);
    }

    #[Test, TestDox('fails when the response is not a success')]
    public function bad_request()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('invalid request');

        $uri = $this->createStub(UriInterface::class);

        $result = $this->createConfiguredStub(Result::class, [
            'getErrors' => ['invalid request']
        ]);

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('withHeader')
            ->willReturnSelf();

        $response = $this->createConfiguredStub(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => 'error',
            ]),
            'getStatusCode' => 400,
            'getReasonPhrase' => 'Bad request',
        ]);

        $encoder = $this->createStub(EncoderInterface::class);

        $decoder = $this->createConfiguredMock(DecoderInterface::class, [
            'getContentType' => 'application/octet-stream',
            'supports' => true,
        ]);
        $decoder
            ->expects($this->once())
            ->method('decode')
            ->with(
                $this->identicalTo('error')
            )
            ->willReturn($result);

        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response);

        $requester = $this->createMock(RequestFactoryInterface::class);
        $requester
            ->expects($this->once())
            ->method('createRequest')
            ->willReturn($request);

        $streamer = $this->createStub(StreamFactoryInterface::class);

        $service = new Client($encoder, $decoder, $client, $requester, $streamer);
        $service->submit('GET', $uri);
    }

    #[Test, TestDox('fails when there is an HTTP problem')]
    public function http_error()
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('Failed to get response for "/ripe/person/TEST-RIPE".');

        $uri = $this->createConfiguredStub(UriInterface::class, [
            'getPath' => '/ripe/person/TEST-RIPE',
        ]);

        $request = $this->createConfiguredMock(RequestInterface::class, [
            'getUri' => $uri,
        ]);
        $request
            ->expects($this->once())
            ->method('withHeader')
            ->willReturnSelf();

        $exception = $this->createConfiguredStub(NetworkExceptionInterface::class, [
            'getRequest' => $request,
        ]);

        $encoder = $this->createStub(EncoderInterface::class);

        $decoder = $this->createStub(DecoderInterface::class);

        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects($this->once())
            ->method('sendRequest')
            ->willThrowException($exception);

        $requester = $this->createMock(RequestFactoryInterface::class);
        $requester
            ->expects($this->once())
            ->method('createRequest')
            ->willReturn($request);

        $streamer = $this->createStub(StreamFactoryInterface::class);

        $service = new Client($encoder, $decoder, $client, $requester, $streamer);
        $service->submit('GET', $uri);
    }

    #[Test, TestDox('fails on a Content-Type mismatch')]
    public function no_decoder()
    {
        $this->expectException(DecoderException::class);
        $this->expectExceptionMessage('No decoder was configured to handle response of type "text/plain".');

        $uri = $this->createStub(UriInterface::class);

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('withHeader')
            ->willReturnSelf();

        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getBody' => $this->createConfiguredStub(StreamInterface::class, [
                'getContents' => 'test',
            ]),
            'getStatusCode' => 200,
        ]);
        $response
            ->method('getHeaderLine')
            ->with(
                $this->identicalTo('Content-Type')
            )
            ->willReturn('text/plain');

        $encoder = $this->createStub(EncoderInterface::class);

        $decoder = $this->createConfiguredStub(DecoderInterface::class, [
            'getContentType' => 'application/octet-stream',
            'supports' => false,
        ]);

        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response);

        $requester = $this->createMock(RequestFactoryInterface::class);
        $requester
            ->expects($this->once())
            ->method('createRequest')
            ->willReturn($request);

        $streamer = $this->createStub(StreamFactoryInterface::class);

        $service = new Client($encoder, $decoder, $client, $requester, $streamer);
        $service->submit('GET', $uri);
    }
}
