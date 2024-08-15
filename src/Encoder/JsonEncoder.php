<?php declare(strict_types=1);

namespace Dormilich\RipeClient\Encoder;

use Dormilich\RIPE\RipeInterface;
use Dormilich\RPSL\Attribute\Value;

use function json_encode;

class JsonEncoder implements EncoderInterface
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
    public function encode(RipeInterface $object): string
    {
        $json['source']['id'] = $object->get('source');

        foreach ($object->getAttributes() as $attribute) {
            if ($attribute->isGenerated()) {
                continue;
            }
            foreach ($attribute as $value) {
                $json['attributes']['attribute'][] = $this->getAttribute($value);
            }
        }

        $data['objects']['object'][] = $json;

        return json_encode($data);
    }

    /**
     * @param Value $value
     * @return array{name: string, value: string, comment: string}
     */
    private function getAttribute(Value $value): array
    {
        $attr['name'] = $value->getName();
        $attr['value'] = $value->getValue();

        if ($comment = $value->getComment()) {
            $attr['comment'] = $comment;
        }

        return $attr;
    }
}
