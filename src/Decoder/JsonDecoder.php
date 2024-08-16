<?php declare(strict_types=1);

namespace Dormilich\RipeClient\Decoder;

use Dormilich\RipeClient\Exception\DecoderException;
use Dormilich\RPSL\Attribute\Attribute;
use Dormilich\RPSL\Attribute\Value;
use JsonException;
use Psr\Http\Message\ResponseInterface;

use const JSON_THROW_ON_ERROR;

use function array_map;
use function json_decode;
use function str_contains;
use function vsprintf;

class JsonDecoder implements DecoderInterface
{
    /**
     * @inheritDoc
     */
    public function getContentType(): string
    {
        return 'application/json';
    }

    /**
     * @inheritDoc
     */
    public function supports(ResponseInterface $response): bool
    {
        $type = $response->getHeaderLine('Content-Type');

        return str_contains($type, $this->getContentType());
    }

    /**
     * @inheritDoc
     */
    public function decode(string $content): Result
    {
        $json = $this->getJson($content);
        $data = $json->objects->object[0];

        $result = new Result($data->type);
        $result->setVersion($json->version->version ?? null);

        $this->addAttributes($data, $result);
        $this->addErrors($json, $result);


        return $result;
    }

    /**
     * @param string $content
     * @return object
     * @throws DecoderException
     */
    private function getJson(string $content): object
    {
        try {
            return json_decode($content, false, 64, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new DecoderException('Failed to parse content as JSON.', 0, $e);
        }
    }

    /**
     * @param object $data
     * @param Result $result
     * @return void
     */
    private function addAttributes(object $data, Result $result): void
    {
        foreach ($data->attributes->attribute as $attribute) {
            $value = $this->getValue($attribute);
            $result->addValue($value);
        }
    }

    /**
     * @param object $data
     * @param Result $result
     * @return void
     */
    private function addErrors(object $data, Result $result): void
    {
        $errors = $data->errormessages->errormessage ?? [];

        foreach ($errors as $error) {
            $message = $this->getMessage($error);
            $result->addError($message);
        }
    }

    /**
     * @param object $attribute
     * @return Value
     */
    private function getValue(object $attribute): Value
    {
        $attr = new Attribute($attribute->name);

        $comment = $attribute->comment ?? null;
        $type = $attribute->{'referenced-type'} ?? null;

        return new Value($attr, $attribute->value, $comment, $type);
    }

    /**
     * @param object $error
     * @return string
     */
    private function getMessage(object $error): string
    {
        $args = array_map(fn(object $arg) => $arg->value, $error->args ?? []);

        return vsprintf($error->text, $args);
    }
}
