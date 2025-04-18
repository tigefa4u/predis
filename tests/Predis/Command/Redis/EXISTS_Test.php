<?php

/*
 * This file is part of the Predis package.
 *
 * (c) 2009-2020 Daniele Alessandri
 * (c) 2021-2025 Till Krüss
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Predis\Command\Redis;

use Predis\Command\PrefixableCommand;

/**
 * @group commands
 * @group realm-key
 */
class EXISTS_Test extends PredisCommandTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getExpectedCommand(): string
    {
        return 'Predis\Command\Redis\EXISTS';
    }

    /**
     * {@inheritdoc}
     */
    protected function getExpectedId(): string
    {
        return 'EXISTS';
    }

    /**
     * @group disconnected
     */
    public function testFilterArguments(): void
    {
        $arguments = ['key'];
        $expected = ['key'];

        $command = $this->getCommand();
        $command->setArguments($arguments);

        $this->assertSame($expected, $command->getArguments());
    }

    /**
     * @group disconnected
     */
    public function testFilterArgumentsMultipleKeys(): void
    {
        $arguments = ['key:1', 'key:2', 'key:3'];
        $expected = ['key:1', 'key:2', 'key:3'];

        $command = $this->getCommand();
        $command->setArguments($arguments);

        $this->assertSame($expected, $command->getArguments());
    }

    /**
     * @group disconnected
     */
    public function testParseResponse(): void
    {
        $command = $this->getCommand();

        $this->assertSame(0, $command->parseResponse(0));
        $this->assertSame(1, $command->parseResponse(1));
        $this->assertSame(2, $command->parseResponse(2));
    }

    /**
     * @group disconnected
     */
    public function testPrefixKeys(): void
    {
        /** @var PrefixableCommand $command */
        $command = $this->getCommand();
        $actualArguments = ['arg1', 'arg2', 'arg3', 'arg4'];
        $prefix = 'prefix:';
        $expectedArguments = ['prefix:arg1', 'prefix:arg2', 'prefix:arg3', 'prefix:arg4'];

        $command->setArguments($actualArguments);
        $command->prefixKeys($prefix);

        $this->assertSame($expectedArguments, $command->getArguments());
    }

    /**
     * @group connected
     */
    public function testReturnValueWhenKeyExists(): void
    {
        $redis = $this->getClient();

        $redis->set('foo', 'bar');
        $this->assertSame(1, $redis->exists('foo'));
    }

    /**
     * @group connected
     * @requiresRedisVersion >= 6.0.0
     */
    public function testReturnValueWhenKeyExistsResp3(): void
    {
        $redis = $this->getResp3Client();

        $redis->set('foo', 'bar');
        $this->assertSame(1, $redis->exists('foo'));
    }

    /**
     * @group connected
     */
    public function testReturnValueWhenKeyDoesNotExist(): void
    {
        $redis = $this->getClient();

        $this->assertSame(0, $redis->exists('foo'));
    }

    /**
     * @group connected
     * @requiresRedisVersion >= 3.0.3
     */
    public function testReturnValueWhenKeysExist(): void
    {
        $redis = $this->getClient();

        $redis->mset('foo', 'bar', 'hoge', 'piyo');
        $this->assertSame(2, $redis->exists('foo', 'hoge'));
    }

    /**
     * @group connected
     * @requiresRedisVersion >= 3.0.3
     */
    public function testReturnValueWhenKeyDoNotExist(): void
    {
        $redis = $this->getClient();

        $this->assertSame(0, $redis->exists('foo', 'bar'));
    }
}
