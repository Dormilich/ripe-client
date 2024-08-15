<?php

namespace Dormilich\RipeClient\Tests\Authorization;

use Dormilich\RipeClient\Authorization\NoAuth;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

#[CoversClass(NoAuth::class)]
class NoAuthTest extends TestCase
{
    #[Test, TestDox('does not modify the request')]
    public function noop()
    {
        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->never())
            ->method('withHeader');
        $request
            ->expects($this->never())
            ->method('withAddedHeader');
        $request
            ->expects($this->never())
            ->method('withUri');

        $auth = new NoAuth();
        $result = $auth->authorize($request);

        $this->assertSame($request, $result);
    }
}
