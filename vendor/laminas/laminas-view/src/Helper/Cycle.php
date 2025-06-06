<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Iterator;
use ReturnTypeWillChange; // phpcs:ignore

use function count;

/**
 * Helper for alternating between set of values
 *
 * @final
 */
class Cycle extends AbstractHelper implements Iterator
{
    /**
     * Default name
     *
     * @var string
     */
    public const DEFAULT_NAME = 'default';

    /**
     * Array of values
     *
     * @var array
     */
    protected $data = [self::DEFAULT_NAME => []];

    /**
     * Actual name of cycle
     *
     * @var string
     */
    protected $name = self::DEFAULT_NAME;

    /**
     * Pointers
     *
     * @var array
     */
    protected $pointers = [self::DEFAULT_NAME => -1];

    /**
     * Add elements to alternate
     *
     * @param  string $name
     * @return Cycle
     */
    public function __invoke(array $data = [], $name = self::DEFAULT_NAME)
    {
        if (! empty($data)) {
            $this->data[$name] = $data;
        }

        $this->setName($name);
        return $this;
    }

    /**
     * Cast to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Turn helper into string
     *
     * @return string
     */
    public function toString()
    {
        return (string) $this->data[$this->name][$this->key()];
    }

    /**
     * Add elements to alternate
     *
     * @param  string $name
     * @return Cycle
     */
    public function assign(array $data, $name = self::DEFAULT_NAME)
    {
        $this->setName($name);
        $this->data[$name] = $data;
        $this->rewind();
        return $this;
    }

    /**
     * Sets actual name of cycle
     *
     * @param  string $name
     * @return Cycle
     */
    public function setName($name = self::DEFAULT_NAME)
    {
        $this->name = $name;

        if (! isset($this->data[$this->name])) {
            $this->data[$this->name] = [];
        }

        if (! isset($this->pointers[$this->name])) {
            $this->rewind();
        }

        return $this;
    }

    /**
     * Gets actual name of cycle
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return all elements
     *
     * @return array
     */
    public function getAll()
    {
        return $this->data[$this->name];
    }

    /**
     * Move to next value
     *
     * @return Cycle
     */
    #[ReturnTypeWillChange]
    public function next()
    {
        $count = count($this->data[$this->name]);

        if ($this->pointers[$this->name] === $count - 1) {
            $this->pointers[$this->name] = 0;
        } else {
            $this->pointers[$this->name] = ++$this->pointers[$this->name];
        }

        return $this;
    }

    /**
     * Move to previous value
     *
     * @return Cycle
     */
    public function prev()
    {
        $count = count($this->data[$this->name]);

        if ($this->pointers[$this->name] <= 0) {
            $this->pointers[$this->name] = $count - 1;
        } else {
            $this->pointers[$this->name] = --$this->pointers[$this->name];
        }

        return $this;
    }

    /**
     * Return iteration number
     *
     * @return int
     */
    #[ReturnTypeWillChange]
    public function key()
    {
        if ($this->pointers[$this->name] < 0) {
            return 0;
        }

        return $this->pointers[$this->name];
    }

    /**
     * Rewind pointer
     *
     * @return Cycle
     */
    #[ReturnTypeWillChange]
    public function rewind()
    {
        $this->pointers[$this->name] = -1;
        return $this;
    }

    /**
     * Check if element is valid
     *
     * @return bool
     */
    #[ReturnTypeWillChange]
    public function valid()
    {
        return isset($this->data[$this->name][$this->key()]);
    }

    /**
     * Return  current element
     *
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function current()
    {
        return $this->data[$this->name][$this->key()];
    }
}
