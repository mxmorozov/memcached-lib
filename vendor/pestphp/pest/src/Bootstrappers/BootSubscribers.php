<?php

declare(strict_types=1);

namespace Pest\Bootstrappers;

use Pest\Subscribers;
use PHPUnit\Event;

/**
 * @internal
 */
final class BootSubscribers
{
    /**
     * The Kernel subscribers.
     *
     * @var array<int, class-string<\PHPUnit\Event\Subscriber>>
     */
    private static array $subscribers = [
        Subscribers\EnsureConfigurationIsValid::class,
        Subscribers\EnsureConfigurationDefaults::class,
        Subscribers\EnsureRetryRepositoryExists::class,
        Subscribers\EnsureFailedTestsAreStoredForRetry::class,
    ];

    /**
     * Boots the Subscribers.
     */
    public function __invoke(): void
    {
        foreach (self::$subscribers as $subscriber) {
            Event\Facade::registerSubscriber(
                new $subscriber()
            );
        }
    }
}
