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
                $this->identicalTo('Basic NkhEVEtIUURNUEdVQTlUWklCNVJEVUI6VzZHYUV0MlNrVHJGTzh4aDJFcUZVNFBo')
            )
            ->willReturn($this->createStub(RequestInterface::class));

        $user = '6HDTKHQDMPGUA9TZIB5RDUB';
        $pass = 'W6GaEt2SkTrFO8xh2EqFU4Ph';
        $auth = new BasicAuth($user, $pass);
        $result = $auth->authorize($request);

        $this->assertNotSame($result, $request);
    }
}
