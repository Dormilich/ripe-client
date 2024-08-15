<?php declare(strict_types=1);

namespace Dormilich\RipeClient\Encoder;

use Dormilich\RIPE\RipeInterface;
use Dormilich\RPSL\Attribute\Value;
use SimpleXMLElement;

class XmlEncoder implements EncoderInterface
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
    public function encode(RipeInterface $object): string
    {
        $xml = $this->createXML();

        $o = $xml->objects->addChild('object');
        $o->addAttribute('type', $object->getType());
        $o->addChild('source')->addAttribute('id', $object->get('source'));

        $a = $o->addChild('attributes');
        foreach ($object->getAttributes() as $attribute) {
            if ($attribute->isGenerated()) {
                continue;
            }
            foreach ($attribute as $value) {
                $this->addAttribute($a, $value);
            }
        }

        return $xml->asXML();
    }

    /**
     * @return SimpleXMLElement
     * @throws \Exception
     */
    private function createXML(): SimpleXMLElement
    {
        $xml = '<whois-resources><objects></objects></whois-resources>';

        return new SimpleXMLElement($xml);
    }

    /**
     * @param SimpleXMLElement $attributes
     * @param Value $value
     * @return void
     */
    private function addAttribute(SimpleXMLElement $attributes, Value $value): void
    {
        $attribute = $attributes->addChild('attribute');

        $attribute->addAttribute('name', $value->getName());
        $attribute->addAttribute('value', $value->getValue());

        if ($comment = $value->getComment()) {
            $attribute->addAttribute('comment', $comment);
        }
    }
}
