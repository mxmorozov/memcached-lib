<?php

/**
 * This file is part of graze/telnet-client.
 *
 * Copyright (c) 2016 Nature Delivered Ltd. <https://www.graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license https://github.com/graze/telnet-client/blob/master/LICENSE
 * @link https://github.com/graze/telnet-client
 */

namespace Graze\TelnetClient;

class PromptMatcher implements PromptMatcherInterface
{
    /**
     * @var array
     */
    protected $matches = [];

    /**
     * @var string
     */
    protected $responseText = '';

    /**
     * @param string $prompt
     * @param string $subject
     * @param string $lineEnding
     *
     * @return bool
     */
    public function isMatch($prompt, $subject, $lineEnding = null)
    {
        // cheap line ending check before expensive regex
        if (!is_null($lineEnding) && substr($subject, -1 * strlen($lineEnding)) != $lineEnding) {
            return false;
        }

        $matches = [];
        $callback = function ($matchesCallback) use (&$matches) {
            $matches = $matchesCallback;
            // replace matches with an empty string (remove prompt from $subject)
            return '';
        };
        $pattern = sprintf('/%s%s$/', $prompt, $lineEnding);

        $responseText = preg_replace_callback($pattern, $callback, $subject);

        if (empty($matches)) {
            return false;
        }

        // trim line endings
        $trimmable = [&$matches, &$responseText];
        array_walk_recursive($trimmable, function (&$trimee) use ($lineEnding) {
            $trimee = trim($trimee, $lineEnding);
        });

        $this->matches = $matches;
        $this->responseText = $responseText;

        return true;
    }

    /**
     * @return array
     */
    public function getMatches()
    {
        return $this->matches;
    }

    /**
     * @return string
     */
    public function getResponseText()
    {
        return $this->responseText;
    }
}
