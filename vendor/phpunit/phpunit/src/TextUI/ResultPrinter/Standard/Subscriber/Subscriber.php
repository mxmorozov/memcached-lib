<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\ResultPrinter\Standard;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class Subscriber
{
    private ResultPrinter $printer;

    public function __construct(ResultPrinter $printer)
    {
        $this->printer = $printer;
    }

    protected function printer(): ResultPrinter
    {
        return $this->printer;
    }
}
