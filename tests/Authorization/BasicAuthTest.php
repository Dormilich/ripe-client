<?php

namespace Dormilich\RipeClient\Tests\Authorization;

use Dormilich\RIPE\Entity\Mntner;
use Dormilich\RipeClient\Authorization\BasicAuth;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

#[CoversClass(BasicAuth::class)]
class BasicAuthTest extends TestCase
{
    #[Test, TestDox('adds an authorisation header')]
    public function add_header()
    {
        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('withHeader')
            ->with(
                $this->identicalTo('Authorization'),
                $this->identicalTo('Basic VEVTVC1EQk0tTU5UOmVtcHR5cGFzc3dvcmQ=')
            )
            ->willReturn($this->createStub(RequestInterface::class));

        $mnt = new Mntner('TEST-DBM-MNT');
        $auth = new BasicAuth($mnt, 'emptypassword');
        $result = $auth->authorize($request);

        $this->assertNotSame($result, $request);
    }
}
