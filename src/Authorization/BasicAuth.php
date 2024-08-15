<?php declare(strict_types=1);

namespace Dormilich\RipeClient\Authorization;

use Dormilich\RIPE\Entity\Mntner;
use Psr\Http\Message\RequestInterface;

use function base64_encode;

/**
 * Authorise request using HTTP Basic Authorization (RFC 7617).
 */
class BasicAuth implements AuthorizationInterface
{
    /**
     * @var string BASE64 encoded credentials.
     */
    private string $credential;

    /**
     * @param Mntner $mnt
     * @param string $password
     */
    public function __construct(Mntner $mnt, string $password)
    {
        $secret = $mnt->getHandle() . ':' . $password;
        $this->credential = base64_encode($secret);
    }

    /**
     * @inheritDoc
     */
    public function authorize(RequestInterface $request): RequestInterface
    {
        return $request->withHeader('Authorization', 'Basic ' . $this->credential);
    }
}
