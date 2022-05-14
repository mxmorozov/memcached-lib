<?php

/**
 * Copyright (c) 2001-2021, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *  * Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 *
 *  * Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in
 *    the documentation and/or other materials provided with the
 *    distribution.
 *
 *  * Neither the name of Sebastian Bergmann nor the names of his
 *    contributors may be used to endorse or promote products derived
 *    from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace PHPUnit\Runner;

use function array_diff;
use function array_values;
use function basename;
use function class_exists;
use function get_declared_classes;
use Pest\TestCases\IgnorableTestCase;
use Pest\TestSuite;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use function stripos;
use function strlen;
use function substr;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestSuiteLoader
{
    /**
     * @psalm-var list<class-string>
     */
    private static array $loadedClasses = [];

    /**
     * @psalm-var list<class-string>
     */
    private static array $declaredClasses = [];

    public function __construct()
    {
        if (empty(self::$declaredClasses)) {
            self::$declaredClasses = get_declared_classes();
        }
    }

    /**
     * @throws Exception
     */
    public function load(string $suiteClassFile): ReflectionClass
    {
        $suiteClassName = $this->classNameFromFileName($suiteClassFile);

        if (!class_exists($suiteClassName, false)) {
            (static function () use ($suiteClassFile) {
                include_once $suiteClassFile;

                TestSuite::getInstance()->tests->makeIfNeeded($suiteClassFile);
            })();

            $loadedClasses = array_values(
                array_diff(
                    get_declared_classes(),
                    array_merge(
                        self::$declaredClasses,
                        self::$loadedClasses
                    )
                )
            );

            self::$loadedClasses = array_merge($loadedClasses, self::$loadedClasses);

            if (empty(self::$loadedClasses)) {
                return $this->exceptionFor($suiteClassName, $suiteClassFile);
            }
        }

        if (!class_exists($suiteClassName, false)) {
            // this block will handle namespaced classes
            $offset = 0 - strlen($suiteClassName);

            foreach (self::$loadedClasses as $loadedClass) {
                if (stripos(substr($loadedClass, $offset - 1), '\\' . $suiteClassName) === 0) {
                    $suiteClassName = $loadedClass;

                    break;
                }
            }
        }

        if (!class_exists($suiteClassName, false)) {
            return $this->exceptionFor($suiteClassName, $suiteClassFile);
        }

        try {
            $class = new ReflectionClass($suiteClassName);
            // @codeCoverageIgnoreStart
        } catch (ReflectionException $e) {
            throw new Exception($e->getMessage(), (int) $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd

        if ($class->isSubclassOf(TestCase::class) && !$class->isAbstract()) {
            return $class;
        }

        if ($class->hasMethod('suite')) {
            try {
                $method = $class->getMethod('suite');
                // @codeCoverageIgnoreStart
            } catch (ReflectionException $e) {
                throw new Exception($e->getMessage(), (int) $e->getCode(), $e);
            }
            // @codeCoverageIgnoreEnd

            if (!$method->isAbstract() && $method->isPublic() && $method->isStatic()) {
                return $class;
            }
        }

        return $this->exceptionFor($suiteClassName, $suiteClassFile);
    }

    public function reload(ReflectionClass $aClass): ReflectionClass
    {
        return $aClass;
    }

    private function classNameFromFileName(string $suiteClassFile): string
    {
        $className = basename($suiteClassFile, '.php');
        $dotPos    = strpos($className, '.');

        if ($dotPos !== false) {
            $className = substr($className, 0, $dotPos);
        }

        return $className;
    }

    private function exceptionFor(string $className, string $filename): ReflectionClass
    {
        return new ReflectionClass(IgnorableTestCase::class);
    }
}
