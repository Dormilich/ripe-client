<?php declare(strict_types=1);

namespace Dormilich\RipeClient\Decoder;

use Dormilich\RipeClient\Exception\DecoderException;
use Dormilich\RPSL\Attribute\Attribute;
use Dormilich\RPSL\Attribute\Value;
use Psr\Http\Message\ResponseInterface;
use SimpleXMLElement;

use function libxml_clear_errors;
use function libxml_use_internal_errors;
use function str_contains;
use function vsprintf;

class XmlDecoder implements DecoderInterface
{
    /**
     * @inheritDoc
     */
    public function getContentType(): string
    {
        return 'application/xml';
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
        $xml = $this->getXml($content);
        $data = $xml->objects->object;

        $result = new Result((string) $data['type']);
        $result->setVersion((string) $xml->version['version']);

        $this->getAttributes($data, $result);
        $this->getErrors($xml, $result);

        return $result;
    }

    /**
     * @param string $content
     * @return SimpleXMLElement
     * @throws DecoderException
     */
    private function getXml(string $content): SimpleXMLElement
    {
        // suppress PHP warnings when reading the content
        $prev = libxml_use_internal_errors(true);
        try {
            return new SimpleXMLElement($content);
        } catch (\Exception $e) {
            throw new DecoderException('Failed to parse content as XML.', 0, $e);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($prev);
        }
    }

    /**
     * @param SimpleXMLElement $data
     * @param Result $result
     * @return void
     */
    private function getAttributes(SimpleXMLElement $data, Result $result): void
    {
        foreach ($data->attributes->attribute as $attribute) {
            $value = $this->getValue($attribute);
            $result->addValue($value);
        }
    }

    /**
     * @param SimpleXMLElement $data
     * @param Result $result
     * @return void
     */
    private function getErrors(SimpleXMLElement $data, Result $result): void
    {
        $errors = $data->errormessages->errormessage ?? [];

        foreach ($errors as $error) {
            $message = $this->getMessage($error);
            $result->addError($message);
        }
    }

    /**
     * @param SimpleXMLElement $attribute
     * @return Value
     */
    private function getValue(SimpleXMLElement $attribute): Value
    {
        $name = (string) $attribute['name'];
        $value = (string) $attribute['value'];
        $comment = (string) $attribute['comment'];
        $type = (string) $attribute['referenced-type'];

        $attr = new Attribute($name);

        return new Value($attr, $value, $comment ?: null, $type ?: null);
    }

    /**
     * @param SimpleXMLElement $error
     * @return string
     */
    private function getMessage(SimpleXMLElement $error): string
    {
        $list['args'] = [];

        foreach ($error as $node) {
            $list[$node->getName()][] = (string) $node['value'];
        }

        return vsprintf((string) $error['text'], $list['args']);
    }
}
