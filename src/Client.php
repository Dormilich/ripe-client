<?php declare(strict_types=1);

namespace Dormilich\RipeClient;

use Dormilich\RIPE\RipeInterface;
use Dormilich\RipeClient\Authorization\AuthorizationInterface;
use Dormilich\RipeClient\Authorization\NoAuth;
use Dormilich\RipeClient\Decoder\DecoderInterface;
use Dormilich\RipeClient\Decoder\Result;
use Dormilich\RipeClient\Encoder\EncoderInterface;
use Dormilich\RipeClient\Exception\DecoderException;
use Dormilich\RipeClient\Exception\RequestException;
use Dormilich\RipeClient\Exception\TransportException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriInterface;

use function implode;
use function sprintf;

class Client
{
    private AuthorizationInterface $auth;

    /**
     * @param EncoderInterface $encoder
     * @param DecoderInterface $decoder
     * @param ClientInterface $client
     * @param RequestFactoryInterface $requestFactory
     * @param StreamFactoryInterface $streamFactory
     */
    public function __construct(
        private EncoderInterface $encoder,
        private DecoderInterface $decoder,
        private readonly ClientInterface $client,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory
    ) {
        $this->auth = new NoAuth();
    }

    /**
     * Set the request encoder.
     *
     * @param EncoderInterface $encoder
     * @return void
     */
    public function setEncoder(EncoderInterface $encoder): void
    {
        $this->encoder = $encoder;
    }

    /**
     * Set the response decoder.
     *
     * @param DecoderInterface $decoder
     * @return void
     */
    public function setDecoder(DecoderInterface $decoder): void
    {
        $this->decoder = $decoder;
    }

    /**
     * Set the authorization method.
     *
     * @param AuthorizationInterface $auth
     * @return void
     */
    public function setAuthorization(AuthorizationInterface $auth): void
    {
        $this->auth = $auth;
    }

    /**
     * Submit API request and return the parsed response if it was successful.
     *
     * @param string $method HTTP method.
     * @param UriInterface $uri
     * @param RipeInterface|null $object
     * @return Result
     * @throws DecoderException Problem parsing the response.
     * @throws RequestException Unsuccessful response.
     * @throws TransportException Problem getting a response.
     */
    public function submit(string $method, UriInterface $uri, RipeInterface $object = null): Result
    {
        $request = $this->getRequest($method, $uri, $object);
        $response = $this->getResponse($request);
        $result = $this->getResult($response);

        if ($response->getStatusCode() < 300) {
            return $result;
        }

        $message = implode("\n", $result->getErrors()) ?: $response->getReasonPhrase();
        throw new RequestException($request, $response, $message);
    }

    /**
     * Create the request to submit. Add body and authorisation where necessary.
     *
     * @param string $method
     * @param UriInterface $uri
     * @param RipeInterface|null $object
     * @return RequestInterface
     */
    private function getRequest(string $method, UriInterface $uri, ?RipeInterface $object): RequestInterface
    {
        $request = $this->requestFactory->createRequest($method, $uri)
            ->withHeader('Accept', $this->decoder->getContentType())
        ;

        if ($object) {
            $request = $this->setBody($request, $object);
        }

        return $this->auth->authorize($request);
    }

    /**
     * Transform the RIPE object into the transfer format and append it to the request.
     *
     * @param RequestInterface $request
     * @param RipeInterface $object
     * @return RequestInterface
     */
    private function setBody(RequestInterface $request, RipeInterface $object): RequestInterface
    {
        $content = $this->encoder->encode($object);

        $body = $this->streamFactory->createStream($content);

        return $request
            ->withBody($body)
            ->withHeader('Content-Type', $this->encoder->getContentType())
            ->withHeader('Content-Length', $body->getSize())
        ;
    }

    /**
     * Make the API call.
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws TransportException
     */
    private function getResponse(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->client->sendRequest($request);
        } catch (NetworkExceptionInterface|RequestExceptionInterface $e) {
            throw TransportException::from($e);
        } catch (ClientExceptionInterface $e) {
            $message = sprintf('Failed to get response for "%s".', $request->getUri()->getPath());
            throw new TransportException($request, $message, $e);
        }
    }

    /**
     * Decode the response into a result object.
     *
     * @param ResponseInterface $response
     * @return Result
     * @throws DecoderException
     */
    private function getResult(ResponseInterface $response): Result
    {
        if ($this->decoder->supports($response)) {
            return $this->decoder->decode($response->getBody()->getContents());
        }

        $type = $response->getHeaderLine('Content-Type');
        $message = sprintf('No decoder was configured to handle response of type "%s".', $type);
        throw new DecoderException($message);
    }
}
