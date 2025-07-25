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
class ZPOPMAX_Test extends PredisCommandTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getExpectedCommand(): string
    {
        return 'Predis\Command\Redis\ZPOPMAX';
    }

    /**
     * {@inheritdoc}
     */
    protected function getExpectedId(): string
    {
        return 'ZPOPMAX';
    }

    /**
     * @requiresRedisVersion >= 5.0.0
     *
     * @group disconnected
     */
    public function testFilterArguments(): void
    {
        $arguments = ['zset', 2];
        $expected = ['zset', 2];

        $command = $this->getCommand();
        $command->setArguments($arguments);

        $this->assertSame($expected, $command->getArguments());
    }

    /**
     * @group disconnected
     */
    public function testPrefixKeys(): void
    {
        /** @var PrefixableCommand $command */
        $command = $this->getCommand();
        $actualArguments = ['arg1'];
        $prefix = 'prefix:';
        $expectedArguments = ['prefix:arg1'];

        $command->setArguments($actualArguments);
        $command->prefixKeys($prefix);

        $this->assertSame($expectedArguments, $command->getArguments());
    }

    /**
     * @requiresRedisVersion >= 5.0.0
     *
     * @group disconnected
     */
    public function testParseResponse(): void
    {
        $raw = ['element1', '1', 'element2', '2', 'element3', '3'];
        $expected = ['element1' => '1', 'element2' => '2', 'element3' => '3'];

        $command = $this->getCommand();

        $this->assertSame($expected, $command->parseResponse($raw));
    }

    /**
     * @group connected
     * @requiresRedisVersion >= 5.0.0
     */
    public function testReturnsElements(): void
    {
        $redis = $this->getClient();

        $this->assertSame([], $redis->zpopmax('letters'));
        $this->assertSame([], $redis->zpopmax('letters', 3));

        $redis->zadd('letters', -10, 'a', 0, 'b', 10, 'c', 20, 'd', 20, 'e', 30, 'f');

        $this->assertEquals(['f' => '30'], $redis->zpopmax('letters'));
        $this->assertEquals(['e' => '20', 'd' => '20', 'c' => '10'], $redis->zpopmax('letters', 3));
        $this->assertEquals(['b' => '0', 'a' => '-10'], $redis->zpopmax('letters', 3));
    }

    /**
     * @requiresRedisVersion >= 6.0.0
     *
     * @group connected
     */
    public function testReturnsElementsResp3(): void
    {
        $redis = $this->getResp3Client();

        $this->assertSame([], $redis->zpopmax('letters'));
        $this->assertSame([], $redis->zpopmax('letters', 3));

        $redis->zadd('letters', -10, 'a', 0, 'b', 10, 'c', 20, 'd', 20, 'e', 30, 'f');

        $this->assertSame(['f' => 30.0], $redis->zpopmax('letters'));
        $this->assertSame([['e' => 20.0], ['d' => 20.0], ['c' => 10.0]], $redis->zpopmax('letters', 3));
        $this->assertSame([['b' => 0.0], ['a' => -10.0]], $redis->zpopmax('letters', 3));
    }

    /**
     * @requiresRedisVersion >= 5.0.0
     *
     * @group connected
     */
    public function testThrowsExceptionOnWrongType(): void
    {
        $this->expectException('Predis\Response\ServerException');
        $this->expectExceptionMessage('Operation against a key holding the wrong kind of value');

        $redis = $this->getClient();

        $redis->set('foo', 'bar');
        $redis->zpopmax('foo');
    }
}
