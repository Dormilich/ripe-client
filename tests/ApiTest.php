<?php

namespace Dormilich\RipeClient\Tests;

use Dormilich\RIPE\Factory\EntityFactoryInterface;
use Dormilich\RIPE\RipeInterface;
use Dormilich\RipeClient\Api;
use Dormilich\RipeClient\Client;
use Dormilich\RipeClient\Decoder\Result;
use Dormilich\RPSL\Attribute\Value;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

#[CoversClass(Api::class)]
class ApiTest extends TestCase
{
    #[Test, TestDox('submits new object for creation')]
    public function create()
    {
        $uri = $this->createStub(UriInterface::class);
        $host = $this->createMock(UriInterface::class);
        $host
            ->expects($this->once())
            ->method('withPath')
            ->with(
                $this->identicalTo('/TEST/poem')
            )
            ->willReturn($uri);

        $object = $this->createConfiguredMock(RipeInterface::class, [
            'getType' => 'poem',
            'getHandle' => null,
        ]);
        $object
            ->method('get')
            ->willReturnMap([
                ['source', 'TEST']
            ]);

        $value = $this->createConfiguredStub(Value::class, [
            'getName' => 'poem',
        ]);
        $result = $this->createConfiguredStub(Result::class, [
            'getType' => 'poem',
            'getIterator' => new \ArrayIterator([$value])
        ]);

        $poem = $this->createConfiguredMock(RipeInterface::class, [
            'has' => true,
        ]);
        $poem
            ->expects($this->once())
            ->method('add')
            ->with(
                $this->identicalTo('poem'),
                $this->identicalTo($value)
            )
            ->willReturnSelf();

        $client = $this->createMock(Client::class);
        $client
            ->expects($this->once())
            ->method('submit')
            ->with(
                $this->identicalTo('POST'),
                $this->identicalTo($uri),
                $this->identicalTo($object)
            )
            ->willReturn($result);

        $factory = $this->createMock(EntityFactoryInterface::class);
        $factory
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->identicalTo('poem')
            )
            ->willReturn($poem);
        $factory
            ->expects($this->once())
            ->method('addTransformers')
            ->willReturnArgument(0);

        $api = new Api($host, $client, $factory);
        $return = $api->create($object);

        $this->assertSame($poem, $return);
    }

    #[Test, TestDox('fetches an object')]
    public function read()
    {
        $uri = $this->createStub(UriInterface::class);
        $uri
            ->expects($this->once())
            ->method('withPath')
            ->with(
                $this->identicalTo('/TEST/poem/poem-summer-time')
            )
            ->willReturnSelf();
        $uri
            ->expects($this->once())
            ->method('withQuery')
            ->with(
                $this->identicalTo('unfiltered')
            )
            ->willReturnSelf();

        $object = $this->createConfiguredMock(RipeInterface::class, [
            'getType' => 'poem',
            'getHandle' => 'poem-summer-time',
        ]);
        $object
            ->method('get')
            ->willReturnMap([
                ['source', 'TEST']
            ]);

        $value = $this->createConfiguredStub(Value::class, [
            'getName' => 'poem',
            'getValue' => 'poem-summer-time',
        ]);
        $result = $this->createConfiguredStub(Result::class, [
            'getType' => 'poem',
            'getIterator' => new \ArrayIterator([$value])
        ]);

        $poem = $this->createConfiguredMock(RipeInterface::class, [
            'has' => true,
        ]);
        $poem
            ->expects($this->once())
            ->method('add')
            ->with(
                $this->identicalTo('poem'),
                $this->identicalTo($value)
            )
            ->willReturnSelf();

        $client = $this->createMock(Client::class);
        $client
            ->expects($this->once())
            ->method('submit')
            ->with(
                $this->identicalTo('GET'),
                $this->identicalTo($uri),
                $this->isNull()
            )
            ->willReturn($result);

        $factory = $this->createMock(EntityFactoryInterface::class);
        $factory
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->identicalTo('poem')
            )
            ->willReturn($poem);
        $factory
            ->expects($this->once())
            ->method('addTransformers')
            ->willReturnArgument(0);

        $api = new Api($uri, $client, $factory);
        $return = $api->read($object);

        $this->assertSame($poem, $return);
    }

    #[Test, TestDox('submits new object for update')]
    public function update()
    {
        $uri = $this->createStub(UriInterface::class);
        $host = $this->createMock(UriInterface::class);
        $host
            ->expects($this->once())
            ->method('withPath')
            ->with(
                $this->identicalTo('/TEST/poem/poem-summer-time')
            )
            ->willReturn($uri);

        $object = $this->createConfiguredMock(RipeInterface::class, [
            'getType' => 'poem',
            'getHandle' => 'poem-summer-time',
        ]);
        $object
            ->method('get')
            ->willReturnMap([
                ['source', 'TEST']
            ]);

        $value = $this->createConfiguredStub(Value::class, [
            'getName' => 'poem',
            'getValue' => 'poem-summer-time',
        ]);
        $result = $this->createConfiguredStub(Result::class, [
            'getType' => 'poem',
            'getIterator' => new \ArrayIterator([$value])
        ]);

        $poem = $this->createConfiguredMock(RipeInterface::class, [
            'has' => true,
        ]);
        $poem
            ->expects($this->once())
            ->method('add')
            ->with(
                $this->identicalTo('poem'),
                $this->identicalTo($value)
            )
            ->willReturnSelf();

        $client = $this->createMock(Client::class);
        $client
            ->expects($this->once())
            ->method('submit')
            ->with(
                $this->identicalTo('PUT'),
                $this->identicalTo($uri),
                $this->identicalTo($object)
            )
            ->willReturn($result);

        $factory = $this->createMock(EntityFactoryInterface::class);
        $factory
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->identicalTo('poem')
            )
            ->willReturn($poem);
        $factory
            ->expects($this->once())
            ->method('addTransformers')
            ->willReturnArgument(0);

        $api = new Api($host, $client, $factory);
        $return = $api->update($object);

        $this->assertSame($poem, $return);
    }

    #[Test, TestDox('submits an object for removal')]
    public function delete()
    {
        $uri = $this->createStub(UriInterface::class);
        $host = $this->createMock(UriInterface::class);
        $host
            ->expects($this->once())
            ->method('withPath')
            ->with(
                $this->identicalTo('/TEST/poem/poem-summer-time')
            )
            ->willReturn($uri);

        $object = $this->createConfiguredMock(RipeInterface::class, [
            'getType' => 'poem',
            'getHandle' => 'poem-summer-time',
        ]);
        $object
            ->method('get')
            ->willReturnMap([
                ['source', 'TEST']
            ]);

        $value = $this->createConfiguredStub(Value::class, [
            'getName' => 'poem',
            'getValue' => 'poem-summer-time',
        ]);
        $result = $this->createConfiguredStub(Result::class, [
            'getType' => 'poem',
            'getIterator' => new \ArrayIterator([$value])
        ]);

        $poem = $this->createConfiguredMock(RipeInterface::class, [
            'has' => true,
        ]);
        $poem
            ->expects($this->once())
            ->method('add')
            ->with(
                $this->identicalTo('poem'),
                $this->identicalTo($value)
            )
            ->willReturnSelf();

        $client = $this->createMock(Client::class);
        $client
            ->expects($this->once())
            ->method('submit')
            ->with(
                $this->identicalTo('DELETE'),
                $this->identicalTo($uri),
                $this->isNull()
            )
            ->willReturn($result);

        $factory = $this->createMock(EntityFactoryInterface::class);
        $factory
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->identicalTo('poem')
            )
            ->willReturn($poem);
        $factory
            ->expects($this->once())
            ->method('addTransformers')
            ->willReturnArgument(0);

        $api = new Api($host, $client, $factory);
        $return = $api->delete($object);

        $this->assertSame($poem, $return);
    }

    #[Test, TestDox('searches for the inetnum object containing an IP')]
    public function find_ip()
    {
        $uri = $this->createStub(UriInterface::class);
        $uri
            ->expects($this->once())
            ->method('withPath')
            ->with(
                $this->identicalTo('/search')
            )
            ->willReturnSelf();
        $uri
            ->expects($this->once())
            ->method('withQuery')
            ->with(
                $this->identicalTo('flags=no-referenced&flags=no-filtering&query-string=203.0.113.7&type-filter=inetnum')
            )
            ->willReturnSelf();

        $value = $this->createConfiguredStub(Value::class, [
            'getName' => 'inetnum',
            'getValue' => '203.0.113.4 - 203.0.113.7',
        ]);
        $result = $this->createConfiguredStub(Result::class, [
            'getType' => 'inetnum',
            'getIterator' => new \ArrayIterator([$value])
        ]);

        $inetnum = $this->createConfiguredMock(RipeInterface::class, [
            'has' => true,
        ]);
        $inetnum
            ->expects($this->once())
            ->method('add')
            ->with(
                $this->identicalTo('inetnum'),
                $this->identicalTo($value)
            )
            ->willReturnSelf();

        $client = $this->createMock(Client::class);
        $client
            ->expects($this->once())
            ->method('submit')
            ->with(
                $this->identicalTo('GET'),
                $this->identicalTo($uri),
                $this->isNull()
            )
            ->willReturn($result);

        $factory = $this->createMock(EntityFactoryInterface::class);
        $factory
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->identicalTo('inetnum')
            )
            ->willReturn($inetnum);
        $factory
            ->expects($this->once())
            ->method('addTransformers')
            ->willReturnArgument(0);

        $api = new Api($uri, $client, $factory);
        $return = $api->inetnum('203.0.113.7');

        $this->assertSame($inetnum, $return);
    }

    #[Test, TestDox('searches for the correct contact object for a handle')]
    public function find_contact()
    {
        $uri = $this->createStub(UriInterface::class);
        $uri
            ->expects($this->once())
            ->method('withPath')
            ->with(
                $this->identicalTo('/search')
            )
            ->willReturnSelf();
        $uri
            ->expects($this->once())
            ->method('withQuery')
            ->with(
                $this->identicalTo('flags=no-referenced&flags=no-filtering&query-string=JD1-TEST&type-filter=person&type-filter=role')
            )
            ->willReturnSelf();

        $value = $this->createConfiguredStub(Value::class, [
            'getName' => 'role',
            'getValue' => 'JD1-TEST',
        ]);
        $result = $this->createConfiguredStub(Result::class, [
            'getType' => 'role',
            'getIterator' => new \ArrayIterator([$value])
        ]);

        $role = $this->createConfiguredMock(RipeInterface::class, [
            'has' => true,
        ]);
        $role
            ->expects($this->once())
            ->method('add')
            ->with(
                $this->identicalTo('role'),
                $this->identicalTo($value)
            )
            ->willReturnSelf();

        $client = $this->createMock(Client::class);
        $client
            ->expects($this->once())
            ->method('submit')
            ->with(
                $this->identicalTo('GET'),
                $this->identicalTo($uri),
                $this->isNull()
            )
            ->willReturn($result);

        $factory = $this->createMock(EntityFactoryInterface::class);
        $factory
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->identicalTo('role')
            )
            ->willReturn($role);
        $factory
            ->expects($this->once())
            ->method('addTransformers')
            ->willReturnArgument(0);

        $api = new Api($uri, $client, $factory);
        $return = $api->contact('JD1-TEST');

        $this->assertSame($role, $return);
    }
}
