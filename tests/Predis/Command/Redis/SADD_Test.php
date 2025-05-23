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
 * @group realm-set
 */
class SADD_Test extends PredisCommandTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getExpectedCommand(): string
    {
        return 'Predis\Command\Redis\SADD';
    }

    /**
     * {@inheritdoc}
     */
    protected function getExpectedId(): string
    {
        return 'SADD';
    }

    /**
     * @group disconnected
     */
    public function testFilterArguments(): void
    {
        $arguments = ['key', 'member1', 'member2', 'member3'];
        $expected = ['key', 'member1', 'member2', 'member3'];

        $command = $this->getCommand();
        $command->setArguments($arguments);

        $this->assertSame($expected, $command->getArguments());
    }

    /**
     * @group disconnected
     */
    public function testFilterArgumentsValuesAsSingleArray(): void
    {
        $arguments = ['key', ['member1', 'member2', 'member3']];
        $expected = ['key', 'member1', 'member2', 'member3'];

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
    public function testAddsMembersToSet(): void
    {
        $redis = $this->getClient();

        $this->assertSame(1, $redis->sadd('letters', 'a'));
        $this->assertSame(2, $redis->sadd('letters', 'b', 'c'));
        $this->assertSame(0, $redis->sadd('letters', 'b'));
    }

    /**
     * @group connected
     * @requiresRedisVersion >= 6.0.0
     */
    public function testAddsMembersToSetResp3(): void
    {
        $redis = $this->getResp3Client();

        $this->assertSame(1, $redis->sadd('letters', 'a'));
        $this->assertSame(2, $redis->sadd('letters', 'b', 'c'));
        $this->assertSame(0, $redis->sadd('letters', 'b'));
    }

    /**
     * @group connected
     * @requiresRedisVersion >= 2.4.0
     */
    public function c()
    {
        $redis = $this->getClient();

        $this->assertSame(3, $redis->sadd('letters', 'a', 'b', 'c', 'b'));
    }

    /**
     * @group connected
     */
    public function testThrowsExceptionOnWrongType(): void
    {
        $this->expectException('Predis\Response\ServerException');
        $this->expectExceptionMessage('Operation against a key holding the wrong kind of value');

        $redis = $this->getClient();

        $redis->set('metavars', 'foo');
        $redis->sadd('metavars', 'hoge');
    }
}
