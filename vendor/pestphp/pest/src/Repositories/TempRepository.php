<?php

declare(strict_types=1);

namespace Pest\Repositories;

/**
 * @internal
 */
final class TempRepository
{
    private const FOLDER = __DIR__ . '/../../.temp';

    /**
     * Creates a new Temp Repository instance.
     */
    public function __construct(private string $filename)
    {
        // ..
    }

    /**
     * Adds a new element.
     */
    public function add(string $element): void
    {
        $this->save(array_merge(
            $this->all(),
            [$element]
        ));
    }

    /**
     * Clears the existing file, if any, and re-creates it.
     */
    public function boot(): void
    {
        @unlink(self::FOLDER . '/' . $this->filename . '.json'); // @phpstan-ignore-line

        $this->save([]);
    }

    /**
     * Checks if the given element exists.
     */
    public function exists(string $element): bool
    {
        return in_array($element, $this->all(), true);
    }

    /**
     * Gets all elements.
     *
     * @return array<int, string>
     */
    private function all(): array
    {
        $contents = file_get_contents(self::FOLDER . '/' . $this->filename . '.json');

        assert(is_string($contents));

        $all = json_decode($contents, true);

        return is_array($all) ? $all : [];
    }

    /**
     * Save the given elements.
     *
     * @param array<int, string> $elements
     */
    private function save(array $elements): void
    {
        $contents = json_encode($elements);

        file_put_contents(self::FOLDER . '/' . $this->filename . '.json', $contents);
    }
}
