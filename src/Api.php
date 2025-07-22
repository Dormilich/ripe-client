<?php declare(strict_types=1);

namespace Dormilich\RipeClient;

use Dormilich\RIPE\ContactInterface;
use Dormilich\RIPE\Entity\Inet6num;
use Dormilich\RIPE\Entity\Inetnum;
use Dormilich\RIPE\Entity\Person;
use Dormilich\RIPE\Factory\EntityFactoryInterface;
use Dormilich\RIPE\InetnumInterface;
use Dormilich\RIPE\RipeInterface;
use Dormilich\RipeClient\Decoder\Result;
use Dormilich\RipeClient\Exception\DecoderException;
use Dormilich\RipeClient\Exception\EntityException;
use Dormilich\RipeClient\Exception\RequestException;
use Dormilich\RipeClient\Exception\TransportException;
use Dormilich\RPSL\Exception\AttributeException;
use Dormilich\RPSL\Exception\TransformerException;
use Psr\Http\Message\UriInterface;

use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_IPV6;
use const FILTER_VALIDATE_IP;

use function filter_var;
use function sprintf;

class Api
{
    /**
     * @param UriInterface $host
     * @param Client $client
     * @param EntityFactoryInterface $objectFactory
     */
    public function __construct(
        private readonly UriInterface $host,
        private readonly Client $client,
        private readonly EntityFactoryInterface $objectFactory
    ) {
    }

    /**
     * Create an object in the RIPE database.
     *
     * @psalm-template R of RipeInterface
     * @psalm-param R $object
     * @psalm-return R
     * @throws AttributeException Missing "source" attribute.
     * @throws EntityException Object data structure from response does not match expected structure.
     * @throws DecoderException Response content-type cannot be decoded.
     * @throws RequestException Unsuccessful response.
     * @throws TransportException Unable to get a response.
     */
    public function create(RipeInterface $object): RipeInterface
    {
        $path = sprintf('/%s/%s', $object->get('source'), $object->getType());
        $uri = $this->host->withPath($path);

        $result = $this->client->submit('POST', $uri, $object);

        return $this->getObject($result);
    }

    /**
     * Fetch an object from the RIPE database.
     *
     * @psalm-template R of RipeInterface
     * @psalm-param R $object
     * @psalm-return R
     * @throws AttributeException Missing "source" attribute.
     * @throws EntityException Object data structure from response does not match expected structure.
     * @throws DecoderException Response content-type cannot be decoded.
     * @throws RequestException Unsuccessful response.
     * @throws TransportException Unable to get a response.
     */
    public function read(RipeInterface $object): RipeInterface
    {
        $path = $this->getPath($object);
        $uri = $this->host->withPath($path)->withQuery('unfiltered');

        $result = $this->client->submit('GET', $uri);

        return $this->getObject($result);
    }

    /**
     * Updates an existing object in the RIPE Database.
     *
     * @psalm-template R of RipeInterface
     * @psalm-param R $object
     * @psalm-return R
     * @throws AttributeException Missing "source" attribute.
     * @throws EntityException Object data structure from response does not match expected structure.
     * @throws DecoderException Response content-type cannot be decoded.
     * @throws RequestException Unsuccessful response.
     * @throws TransportException Unable to get a response.
     */
    public function update(RipeInterface $object): RipeInterface
    {
        $path = $this->getPath($object);
        $uri = $this->host->withPath($path);

        $result = $this->client->submit('PUT', $uri, $object);

        return $this->getObject($result);
    }

    /**
     * Deletes an object from the Database.
     *
     * @psalm-template R of RipeInterface
     * @psalm-param R $object
     * @psalm-return R
     * @throws AttributeException Missing "source" attribute.
     * @throws EntityException Object data structure from response does not match expected structure.
     * @throws DecoderException Response content-type cannot be decoded.
     * @throws RequestException Unsuccessful response.
     * @throws TransportException Unable to get a response.
     */
    public function delete(RipeInterface $object): RipeInterface
    {
        $path = $this->getPath($object);
        $uri = $this->host->withPath($path);

        $result = $this->client->submit('DELETE', $uri);

        return $this->getObject($result);
    }

    /**
     * Find the closest network that contains the specified IP.
     *
     * @param string $ip IP address.
     * @return InetnumInterface
     * @throws EntityException Object data structure from response does not match expected structure.
     * @throws DecoderException Response content-type cannot be decoded.
     * @throws RequestException Unsuccessful response.
     * @throws TransportException Unable to get a response.
     */
    public function inetnum(string $ip): RipeInterface
    {
        $inet = $this->getInetnum($ip);
        $query = $this->getSearchQuery($inet);
        $uri = $this->host->withPath('/search')->withQuery($query);

        $result = $this->client->submit('GET', $uri);

        return $this->getObject($result);
    }

    /**
     * Get the RIPE object of a contact handle.
     *
     * @param string $handle Role or Person handle.
     * @return ContactInterface
     * @throws DecoderException
     * @throws EntityException
     * @throws RequestException
     * @throws TransportException
     */
    public function contact(string $handle): RipeInterface
    {
        $query = $this->getSearchQuery(new Person($handle)) . '&type-filter=role';
        $uri = $this->host->withPath('/search')->withQuery($query);

        $result = $this->client->submit('GET', $uri);

        return $this->getObject($result);
    }

    /**
     * Create resource locator for the object.
     *
     * @param RipeInterface $object
     * @return string
     * @throws AttributeException Missing "source" attribute in object configuration.
     */
    private function getPath(RipeInterface $object): string
    {
        $path  = '/' . $object->get('source');
        $path .= '/' . $object->getType();
        $path .= '/' . $object->getHandle();

        return $path;
    }

    /**
     * Create URL query to look up an object (e.g. inetnum based on a single IP).
     *
     * @param RipeInterface $object
     * @return string
     */
    private function getSearchQuery(RipeInterface $object): string
    {
        $query = 'flags=no-referenced&flags=no-filtering';
        $query .= '&query-string=' . $object->getHandle();
        $query .= '&type-filter=' . $object->getType();

        return $query;
    }

    /**
     * Create result object from the parsed response.
     *
     * @param Result $result
     * @return RipeInterface
     * @throws EntityException
     */
    private function getObject(Result $result): RipeInterface
    {
        try {
            $object = $this->objectFactory->create($result->getType());

            $this->addAttributes($object, $result);

            return $this->objectFactory->addTransformers($object);
        } catch (\Exception $e) {
            throw new EntityException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Add attribute values to the RIPE object.
     *
     * @param RipeInterface $object
     * @param Result $result
     * @return void
     * @throws AttributeException
     * @throws TransformerException
     */
    private function addAttributes(RipeInterface $object, Result $result): void
    {
        foreach ($result as $value) {
            if ($object->has($value->getName())) {
                $object->add($value->getName(), $value);
            }
        }
    }

    /**
     * Convert IP address into an inetnum object.
     *
     * @param string $ip IP address.
     * @return RipeInterface
     * @throws EntityException Argument is not an IP.
     */
    private function getInetnum(string $ip): RipeInterface
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return new Inetnum($ip);
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return new Inet6num($ip);
        }

        $message = sprintf('"%s" is not a valid IP address.', $ip);
        throw new EntityException($message);
    }
}
