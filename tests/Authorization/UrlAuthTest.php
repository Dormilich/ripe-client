<?php

namespace Dormilich\RipeClient\Tests\Authorization;

use Dormilich\RipeClient\Authorization\UrlAuth;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

#[CoversClass(UrlAuth::class)]
class UrlAuthTest extends TestCase
{
    #[Test, TestDox('adds a password to the URL')]
    public function add_password()
    {
        $pass = $this->createStub(UriInterface::class);
        $uri = $this->createConfiguredMock(UriInterface::class, [
            'getQuery' => '',
        ]);
        $uri
            ->expects($this->once())
            ->method('withQuery')
            ->with(
                $this->identicalTo('password=emptypassword')
            )
            ->willReturn($pass);

        $hot = $this->createStub(RequestInterface::class);
        $request = $this->createConfiguredMock(RequestInterface::class, [
            'getUri' => $uri,
        ]);
        $request
            ->expects($this->once())
            ->method('withUri')
            ->with(
                $this->identicalTo($pass)
            )
            ->willReturn($hot);

        $auth = new UrlAuth('emptypassword');
        $result = $auth->authorize($request);

        $this->assertSame($hot, $result);
    }

    #[Test, TestDox('adds multiple passwords to the URL')]
    public function multi_password()
    {
        $pass = $this->createStub(UriInterface::class);
        $uri = $this->createConfiguredMock(UriInterface::class, [
            'getQuery' => '',
        ]);
        $uri
            ->expects($this->once())
            ->method('withQuery')
            ->with(
                $this->identicalTo('password=foo&password=bar')
            )
            ->willReturn($pass);

        $hot = $this->createStub(RequestInterface::class);
        $request = $this->createConfiguredMock(RequestInterface::class, [
            'getUri' => $uri,
        ]);
        $request
            ->expects($this->once())
            ->method('withUri')
            ->with(
                $this->identicalTo($pass)
            )
            ->willReturn($hot);

        $auth = new UrlAuth('foo');
        $auth->addPassword('bar');
        $result = $auth->authorize($request);

        $this->assertSame($hot, $result);
    }

    #[Test, TestDox('retains existing URL parameters')]
    public function append()
    {
        $pass = $this->createStub(UriInterface::class);
        $uri = $this->createConfiguredMock(UriInterface::class, [
            'getQuery' => 'dry-run',
        ]);
        $uri
            ->expects($this->once())
            ->method('withQuery')
            ->with(
                $this->identicalTo('dry-run&password=emptypassword')
            )
            ->willReturn($pass);

        $hot = $this->createStub(RequestInterface::class);
        $request = $this->createConfiguredMock(RequestInterface::class, [
            'getUri' => $uri,
        ]);
        $request
            ->expects($this->once())
            ->method('withUri')
            ->with(
                $this->identicalTo($pass)
            )
            ->willReturn($hot);

        $auth = new UrlAuth('emptypassword');
        $result = $auth->authorize($request);

        $this->assertSame($hot, $result);
    }
}
