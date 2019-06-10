<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Annotation;

/**
 * Php Parser.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PhpParser
{
    /**
     * Extract the class names.
     *
     * @param string $filename The filename
     *
     * @return string[]
     */
    public static function extractClasses(string $filename): array
    {
        $code = file_get_contents($filename);
        $tokens = @token_get_all($code);
        $namespace = null;
        $classes = [];

        while ($token = current($tokens)) {
            $tokenType = \is_array($token) ? $token[0] : $token;
            next($tokens);

            switch ($tokenType) {
                case T_NAMESPACE:
                    $namespace = ltrim(self::fetch($tokens, [T_STRING, T_NS_SEPARATOR]).'\\', '\\');

                    break;
                case T_CLASS:
                case T_INTERFACE:
                    if ($name = self::fetch($tokens, [T_STRING])) {
                        $classes[] = $namespace.$name;
                    }

                    break;
            }
        }

        return $classes;
    }

    /**
     * @param array    $tokens        The PHP source tokens
     * @param string[] $requiredTypes The required token types
     *
     * @return null|string
     */
    private static function fetch(array &$tokens, array $requiredTypes): ?string
    {
        $res = null;

        while ($token = current($tokens)) {
            list($token, $s) = \is_array($token) ? $token : [$token, $token];

            if (\in_array($token, $requiredTypes, true)) {
                $res .= $s;
            } elseif (!\in_array($token, [T_DOC_COMMENT, T_WHITESPACE, T_COMMENT], true)) {
                break;
            }

            next($tokens);
        }

        return $res;
    }
}
