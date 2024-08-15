<?php

namespace Dormilich\RipeClient\Tests\Encoder;

use Dormilich\RIPE\Entity\Mntner;
use Dormilich\RIPE\Entity\Person;
use Dormilich\RIPE\Entity\Poem;
use Dormilich\RIPE\Entity\PoeticForm;
use Dormilich\RIPE\RipeInterface;
use Dormilich\RipeClient\Encoder\JsonEncoder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonEncoder::class)]
class JsonEncoderTest extends TestCase
{
    #[Test, TestDox('has JSON content type')]
    public function content_type()
    {
        $encoder = new JsonEncoder();

        $this->assertSame('application/json', $encoder->getContentType());
    }

    #[Test, TestDox('serialises object to JSON')]
    public function encode_object()
    {
        $encoder = new JsonEncoder();
        $content = $encoder->encode($this->poem());

        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/encode.poem.json', $content);
    }

    private function poem(): RipeInterface
    {
        $entity = new Poem('POEM-3-14');

        return $entity
            ->set('descr', 'Happy Pi Day!')
            ->set('form', new PoeticForm('FORM-PROSE'))
            ->set('text', 'How I wish I could recollect pi easily today!')
            ->set('author', new Person('LIM1-RIPE # actually anonymous'))
            ->set('mnt-by', new Mntner('LIM-MNT'))
            ->set('created', '2007-03-14T08:51:01Z')
            ->set('last-modified', '2007-03-14T08:51:01Z')
            ->set('source', 'RIPE')
        ;
    }
}
