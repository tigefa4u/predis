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
 * @group realm-zset
 */
class ZSCORE_Test extends PredisCommandTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getExpectedCommand(): string
    {
        return 'Predis\Command\Redis\ZSCORE';
    }

    /**
     * {@inheritdoc}
     */
    protected function getExpectedId(): string
    {
        return 'ZSCORE';
    }

    /**
     * @group disconnected
     */
    public function testFilterArguments(): void
    {
        $arguments = ['key', 'member'];
        $expected = ['key', 'member'];

        $command = $this->getCommand();
        $command->setArguments($arguments);

        $this->assertSame($expected, $command->getArguments());
    }

    /**
     * @group disconnected
     */
    public function testParseResponse(): void
    {
        $this->assertSame(1, $this->getCommand()->parseResponse(1));
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
        $expectedArguments = ['prefix:arg1', 'arg2', 'arg3', 'arg4'];

        $command->setArguments($actualArguments);
        $command->prefixKeys($prefix);

        $this->assertSame($expectedArguments, $command->getArguments());
    }

    /**
     * @group connected
     */
    public function testReturnsRank(): void
    {
        $redis = $this->getClient();

        $redis->zadd('letters', -10, 'a', 0, 'b', 10, 'c', 20, 'd', 20, 'e', 30, 'f');

        $this->assertEquals('-10', $redis->zscore('letters', 'a'));
        $this->assertEquals('0', $redis->zscore('letters', 'b'));
        $this->assertEquals('20', $redis->zscore('letters', 'e'));

        $this->assertNull($redis->zscore('unknown', 'a'));
    }

    /**
     * @group connected
     * @requiresRedisVersion >= 6.0.0
     */
    public function testReturnsRankResp3(): void
    {
        $redis = $this->getResp3Client();

        $redis->zadd('letters', -10, 'a', 0, 'b', 10, 'c', 20, 'd', 20, 'e', 30, 'f');

        $this->assertSame(-10.0, $redis->zscore('letters', 'a'));
        $this->assertSame(0.0, $redis->zscore('letters', 'b'));
        $this->assertSame(20.0, $redis->zscore('letters', 'e'));

        $this->assertNull($redis->zscore('unknown', 'a'));
    }

    /**
     * @group connected
     */
    public function testThrowsExceptionOnWrongType(): void
    {
        $this->expectException('Predis\Response\ServerException');
        $this->expectExceptionMessage('Operation against a key holding the wrong kind of value');

        $redis = $this->getClient();

        $redis->set('foo', 'bar');
        $redis->zscore('foo', 'bar');
    }
}
