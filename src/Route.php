<?php

namespace Subapp\Router;

/**
 * Class Route
 * @package Subapp\Router
 */
class Route
{

    const PATTERN_LITERAL = '([\w\d-_]+)';
    const PATTERN_ANY = '(.*)';
    const PATTERN_INTEGER = '(\d+)';

    /**
     * @var array|Pattern[]
     */
    private $patterns = [];

    /**
     * @var array
     */
    private $compiled = [];

    /**
     * @var array
     */
    private $matches;

    /**
     * @var array
     */
    private $methods;

    /**
     * @var array
     */
    protected $replacements = [];

    /**
     * @var array
     */
    protected $names = [];

    /**
     * @var array
     */
    protected $positions = [];

    /**
     * @var bool
     */
    protected $regexable = false;

    /**
     * @var null
     */
    protected $routeId = null;

    /**
     * Route constructor.
     * @param array $matches
     * @param Pattern ...$patterns
     */
    public function __construct(array $matches = [], Pattern ...$patterns)
    {
        $this->matches = $matches;

        foreach ($patterns as $pattern) {
            $this->addPattern($pattern);
        }

        $this->setRouteId(spl_object_hash($this));
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'class' => static::class,
            'patterns' => $this->patterns,
            'compiled' => $this->compiled,
            'replacements' => $this->replacements,
            'names' => $this->names,
            'positions' => $this->positions,
            'methods' => $this->methods,
            'regexable' => $this->regexable,
            'routeId' => $this->routeId,
            'matches' => $this->matches,
        ];
    }

    /**
     * @return array
     */
    public function getMatches()
    {
        return $this->matches;
    }

    /**
     * @param array $matches
     * @return $this
     */
    public function setMatches(array $matches)
    {
        if (count($matches) > 0) {
            foreach ($matches as $name => $match) {
                $this->setMatch($name, $match);
            }
        }

        return $this;
    }

    /**
     * @param $name
     * @return array
     */
    public function getMatch($name)
    {
        return $this->hasMatch($name) ? $this->matches[$name] : [];
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasMatch($name)
    {
        return isset($this->matches[$name]);
    }

    /**
     * @return array
     */
    public function getPositions()
    {
        return $this->positions;
    }

    /**
     * @return null
     */
    public function getRouteId()
    {
        return $this->routeId;
    }

    /**
     * @param null $routeId
     * @return static
     */
    public function setRouteId($routeId)
    {
        $this->routeId = sha1($routeId);

        return $this;
    }

    /**
     * @param string $name
     * @return array|null
     */
    public function getRegex($name)
    {
        return $this->hasRegex($name) ? $this->replacements[$name] : null;
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function hasRegex($name)
    {
        return isset($this->replacements[$name]);
    }

    /**
     * @return array
     */
    public function getReplacements()
    {
        return $this->replacements;
    }

    /**
     * @param array $methods
     * @return $this
     */
    public function via($methods = [])
    {
        if (count($methods) > 0) {
            $methods = array_map('strtoupper', $methods);
            $this->setMethods($methods);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param array $methods
     * @return static
     */
    public function setMethods(array $methods = [])
    {
        $this->methods = $methods;

        return $this;
    }

    /**
     * @param Source ...$sources
     * @return boolean
     */
    public function match(Source ...$sources)
    {
        $this->createReplacements();
        $this->compile();

        $compiled = $this->getCompiled();
        $names = $this->getNames();
        $matched = false;

        foreach ($sources as $source) {
            foreach ($compiled as $type => $pattern) {

                if ($source->getName() != $type) {
                    continue;
                }

                if ($this->isRegexable()) {

                    $pattern = "~^$pattern$~Uus";

                    preg_match_all($pattern, $source->getValue(), $matches, PREG_SET_ORDER);

                    if (count($matches) > 0 && count($matches) !== count($matches, true)) {
                        $matches = $matches[0];
                        array_shift($matches);

                        foreach ($matches as $index => $foundValue) {
                            $this->setMatch($names[$type][$index], $foundValue);
                        }

                        $matched = true;
                        break(1);
                    }

                } else {
                    if ($source->getValue() == $pattern) {
                        $matched = true;
                        break(1);
                    }
                }
            }
        }

        return $matched;
    }

    /**
     * @return $this
     */
    public function createReplacements()
    {
        $placeholders = [
            ':module' => ['module', self::PATTERN_LITERAL],
            ':controller' => ['controller', self::PATTERN_LITERAL],
            ':action' => ['action', self::PATTERN_LITERAL],
            ':params' => ['params', self::PATTERN_ANY],
            ':id' => ['id', self::PATTERN_INTEGER],
        ];

        $patterns = $this->getPatterns();

        foreach ($patterns as $pattern) {
            foreach ($placeholders as $placeholder => list($name, $regex)) {
                if (strpos($pattern->getPattern(), $placeholder) !== false) {
                    $this->regex($name, $regex);
                }
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function compile()
    {
        $patterns = $this->getPatterns();

        foreach ($patterns as $pattern) {
            $this->setRegexable($this->isRegexable() || (strpos($pattern, ':') !== false));
        }

        if ($this->isRegexable()) {

            $compiled = array_map(function (Pattern $pattern) {
                return $pattern->getPattern();
            }, $patterns);

            foreach ($patterns as $type => $pattern) {

                $names = [];

                preg_match_all('~:([a-z_]+?)~Uusi', $pattern, $matches, PREG_PATTERN_ORDER);
                list($placeholders) = $matches;

                $names = array_merge($matches[1] ?? [], $names);

                foreach ($placeholders as $index => $placeholder) {
                    $name = $names[$index];
                    $regex = $this->replacements[$name]['regex'] ?? self::PATTERN_LITERAL;

                    if (!isset($this->replacements[$name]['regex'])) {
                        $this->regex($name, $regex);
                    }

                    $compiled = str_replace($placeholder, $regex, $compiled);
                }

                $this->setNames($names, $type);
            }

            $this->setCompiled($compiled);
        }

        return $this;
    }

    /**
     * @return array|Pattern[]
     */
    public function getPatterns()
    {
        return $this->patterns;
    }

    /**
     * @param Pattern $pattern
     */
    public function addPattern(Pattern $pattern)
    {
        $this->patterns[$pattern->getName()] = $pattern;
    }

    /**
     * @param $name
     * @param $regex
     * @return $this
     */
    public function regex($name, $regex)
    {
        $this->replacements[$name] = [
            'regex' => sprintf('(%s)', trim($regex, '()')),
            'replacement' => ":$name"
        ];

        return $this;
    }

    /**
     * @return boolean
     */
    public function isRegexable()
    {
        return $this->regexable;
    }

    /**
     * @param boolean $regexable
     * @return static
     */
    public function setRegexable($regexable)
    {
        $this->regexable = $regexable;

        return $this;
    }

    /**
     * @return array
     */
    public function getCompiled()
    {
        return $this->compiled;
    }

    /**
     * @param array $compiled
     * @return $this
     */
    public function setCompiled(array $compiled)
    {
        $this->compiled = $compiled;

        return $this;
    }

    /**
     * @return array
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * @param array $names
     * @param string $type
     * @return $this
     */
    public function setNames($names, $type)
    {
        $this->names[$type] = $names;
        $this->positions[$type] = array_flip($names);

        return $this;
    }

    /**
     * @param $name
     * @param $match
     * @return $this
     */
    public function setMatch($name, $match)
    {
        $this->matches[$name] = $match;

        return $this;
    }

}
