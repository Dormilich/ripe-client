<?php declare(strict_types=1);

namespace Dormilich\RipeClient\Decoder;

use Dormilich\RPSL\Attribute\Value;
use IteratorAggregate;
use SplFixedArray;
use Traversable;

/**
 * Container object for the information parsed from the response.
 */
class Result implements IteratorAggregate
{
    /**
     * @var Value[]
     */
    private array $values = [];

    /**
     * @var string[]
     */
    private array $errors = [];

    /**
     * @var string|null RIPE Database version.
     */
    private ?string $version = null;

    /**
     * @param string $type
     */
    public function __construct(
        private readonly string $type
    ) {
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param Value $value
     * @return void
     */
    public function addValue(Value $value): void
    {
        $this->values[] = $value;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param string $message
     * @return void
     */
    public function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    /**
     * @return string|null RIPE Database version.
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * @param string|null $version
     */
    public function setVersion(?string $version): void
    {
        $this->version = $version ?: null;
    }

    /**
     * @return Traversable<int, Value>
     */
    public function getIterator(): Traversable
    {
        return SplFixedArray::fromArray($this->values);
    }
}
