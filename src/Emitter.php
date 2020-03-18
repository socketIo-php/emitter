<?php

namespace Emitter;

/**
 * Class Emitter
 *
 * @package Emitter
 */
class Emitter
{
    private $callbacks = [];

    /**
     * @param string   $event
     * @param callable $fn
     *
     * @return Emitter
     */
    public function on(string $event, callable $fn)
    {
        return $this->addEventListener($event, $fn);
    }

    /**
     * @param string   $event
     * @param callable $fn
     *
     * @return $this
     */
    public function addEventListener(string $event, callable $fn)
    {
        if (!isset($this->callbacks['$' . $event])) {
            $this->callbacks['$' . $event] = [];
        }

        array_push($this->callbacks['$' . $event], $fn);

        return $this;
    }

    /**
     * @param string   $event
     * @param callable $fn
     *
     * @return $this
     */
    public function once(string $event, callable $fn)
    {
        $on = function ($retFn = false) use ($event, $fn) {
            $arguments = func_get_args();
            if (is_bool(current($arguments))) {
                $arguments = array_slice($arguments, 1);
            }else{
                $retFn = false;
            }
            if (!$retFn) {
                $this->off($event, $fn);
                $fn(...$arguments);

                return null;
            } else {
                return $fn;
            }
        };

        $this->on($event, $on);

        return $this;
    }

    /**
     * @param string        $event
     * @param callable|null $fn
     *
     * @return Emitter
     */
    public function off(string $event = '', ?callable $fn = null)
    {
        return $this->removeListener($event, $fn);
    }

    /**
     * @param string        $event
     * @param callable|null $fn
     *
     * @return Emitter
     */
    public function removeListener(string $event = '', ?callable $fn = null)
    {
        return $this->removeAllListeners($event, $fn);
    }

    /**
     * @param string        $event
     * @param callable|null $fn
     *
     * @return Emitter
     */
    public function removeAllListeners(string $event = '', ?callable $fn = null)
    {
        return $this->removeEventListener($event, $fn);
    }

    /**
     * @param string        $event
     * @param callable|null $fn
     *
     * @return $this
     */
    public function removeEventListener(string $event = '', ?callable $fn = null)
    {
        // all
        $arguments = func_get_args();
        $arguments = array_filter($arguments);
        if (0 === count($arguments)) {
            $this->callbacks = [];

            return $this;
        }


        if (!isset($this->callbacks['$' . $event])) return $this;

        // specific event
        $callbacks = $this->callbacks['$' . $event];

        // remove all handlers
        if (1 == count($arguments)) {
            unset($this->callbacks['$' . $event]);

            return $this;
        }

        // remove specific handler
        $cb = null;
        for ($i = 0; $i < count($callbacks); $i++) {
            $cb = $callbacks[$i];
            if ($cb === $fn || $cb(true) === $fn) {
                unset($callbacks[$i]);
                unset($this->callbacks['$' . $event]);
                break;
            }
        }

        return $this;
    }

    /**
     * @param string $event
     */
    public function emit(string $event)
    {
        $arguments = func_get_args();
        $args = array_slice($arguments, 1);
        $callbacks = $this->callbacks['$' . $event];

        if (!empty($callbacks)) {
            for ($i = 0, $len = count($callbacks); $i < $len; ++$i) {
                $callbacks[$i](...$args);
            }
        }
    }

    /**
     * @param string $event
     *
     * @return array|mixed
     */
    public function listeners(string $event)
    {
        return $this->callbacks['$' . $event] ?? [];
    }

    /**
     * @param string $event
     *
     * @return bool
     */
    public function hasListeners(string $event)
    {
        return count($this->listeners($event)) ? true : false;
    }

}