<?php

namespace EmitterTest;

use Emitter\Emitter;
use PHPUnit\Framework\TestCase;

final class EmitterTest extends TestCase
{
    public function testOn()
    {
        $emitter = new Emitter();
        $calls = [];

        $emitter->on('foo', function ($val) use (&$calls) {
            array_push($calls, 'one', $val);
        });

        $emitter->on('foo', function ($val) use (&$calls) {
            array_push($calls, 'two', $val);
        });

        $emitter->emit('foo', 1);
        $emitter->emit('bar', 1);
        $emitter->emit('foo', 2);

        $this->assertEquals(['one', 1, 'two', 1, 'one', 2, 'two', 2], $calls);
    }

    public function testOnce()
    {
        $emitter = new Emitter();
        $calls = [];

        $emitter->once('foo', function ($val) use (&$calls) {
            array_push($calls, 'one', $val);
        });


        $emitter->emit('foo', 1);
        $emitter->emit('foo', 2);
        $emitter->emit('foo', 3);
        $emitter->emit('bar', 1);

        $this->assertEquals(['one', 1], $calls);
    }

    public function testOff()
    {
        $emitter = new Emitter();
        $calls = [];

        $one = function () use (&$calls) {
            array_push($calls, 'one');
        };
        $two = function () use (&$calls) {
            array_push($calls, 'two');
        };

        $emitter->on('foo', $one);
        $emitter->on('foo', $two);
        $emitter->off('foo', $two);

        $emitter->emit('foo');

        $this->assertEquals(['one'], $calls);
    }

    public function testOffInOnce()
    {
        $emitter = new Emitter();
        $calls = [];

        $one = function () use (&$calls) {
            array_push($calls, 'one');
        };

        $emitter->once('foo', $one);
        $emitter->once('fee', $one);
        $emitter->off('foo', $one);

        $emitter->emit('foo');

        $this->assertEquals([], $calls);
    }

    public function testOffRemoveAllListenersForEvent()
    {
        $emitter = new Emitter();
        $calls = [];

        $one = function () use (&$calls) {
            array_push($calls, 'one');
        };
        $two = function () use (&$calls) {
            array_push($calls, 'two');
        };

        $emitter->on('foo', $one);
        $emitter->on('foo', $two);
        $emitter->off('foo');

        $emitter->emit('foo');
        $emitter->emit('foo');

        $this->assertEquals([], $calls);
    }

    public function testOffRemoveAllListeners()
    {
        $emitter = new Emitter();
        $calls = [];

        $one = function () use (&$calls) {
            array_push($calls, 'one');
        };
        $two = function () use (&$calls) {
            array_push($calls, 'two');
        };

        $emitter->on('foo', $one);
        $emitter->on('foo', $two);
        $emitter->off();

        $emitter->emit('foo');
        $emitter->emit('foo');

        $this->assertEquals([], $calls);
    }

    public function testListeners()
    {
        $emitter = new Emitter();
        $foo = function () {
        };

        $emitter->on('foo', $foo);
        $this->assertEquals([$foo], $emitter->listeners('foo'));
    }

    public function testListenersWhenEmpty()
    {
        $emitter = new Emitter();

        $this->assertEquals([], $emitter->listeners('foo'));
    }

    public function testHasListener()
    {
        $emitter = new Emitter();
        $emitter->on('foo', function () {
        });

        $this->assertTrue($emitter->hasListeners('foo'));
    }

    public function testHasListenerFalse()
    {
        $emitter = new Emitter();

        $this->assertFalse($emitter->hasListeners('foo'));
    }
}