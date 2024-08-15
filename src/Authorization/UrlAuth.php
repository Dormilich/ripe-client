<?php declare(strict_types=1);

namespace Dormilich\RipeClient\Authorization;

use Psr\Http\Message\RequestInterface;

use function array_unique;
use function trim;

/**
 * Authorise by passing the password(s) in the URL query.
 */
class UrlAuth implements AuthorizationInterface
{
    /**
     * @var string[]
     */
    private array $passwords = [];

    /**
     * @param string $password
     */
    public function __construct(string $password)
    {
        $this->addPassword($password);
    }

    /**
     * @param string $password
     * @return void
     */
    public function addPassword(string $password): void
    {
        $this->passwords[] = $password;
    }

    /**
     * @inheritDoc
     */
    public function authorize(RequestInterface $request): RequestInterface
    {
        $query = $request->getUri()->getQuery();
        $query = $this->addQuery($query);
        $uri = $request->getUri()->withQuery($query);

        return $request->withUri($uri);
    }

    /**
     * Build query string.
     *
     * @param string $query
     * @return string
     */
    private function addQuery(string $query): string
    {
        $passwords = array_unique($this->passwords);

        foreach ($passwords as $password) {
            $query .= '&password=' . $password;
        }

        return trim($query, '&');
    }
}
