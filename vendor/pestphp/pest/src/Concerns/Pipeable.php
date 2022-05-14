<?php

declare(strict_types=1);

namespace Pest\Concerns;

use Closure;
use Pest\Expectation;

/**
 * @internal
 */
trait Pipeable
{
    /**
     * The list of pipes.
     *
     * @var array<string, array<Closure(Closure, mixed ...$arguments): void>>
     */
    private static array $pipes = [];

    /**
     * Register a pipe to be applied before an expectation is checked.
     */
    public function pipe(string $name, Closure $pipe): void
    {
        self::$pipes[$name][] = $pipe;
    }

    /**
     * Register an interceptor that should replace an existing expectation.
     *
     * @param string|Closure(mixed $value, mixed ...$arguments):bool $filter
     */
    public function intercept(string $name, string|Closure $filter, Closure $handler): void
    {
        if (is_string($filter)) {
            $filter = function ($value) use ($filter): bool {
                return $value instanceof $filter;
            };
        }

        $this->pipe($name, function ($next, ...$arguments) use ($handler, $filter) {
            /* @phpstan-ignore-next-line */
            if ($filter($this->value, ...$arguments)) {
                // @phpstan-ignore-next-line
                $handler->bindTo($this, get_class($this))(...$arguments);

                return;
            }

            $next();
        });
    }

    /**
     * Get th list of pipes by the given name.
     *
     * @return array<int, Closure>
     */
    private function pipes(string $name, object $context, string $scope): array
    {
        return array_map(fn (Closure $pipe) => $pipe->bindTo($context, $scope), self::$pipes[$name] ?? []);
    }
}
