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

namespace Predis\Command\Redis\CuckooFilter;

use Predis\Command\PrefixableCommand;
use Predis\Command\Redis\PredisCommandTestCase;
use Predis\Response\ServerException;

/**
 * @group commands
 * @group realm-stack
 */
class CFADDNX_Test extends PredisCommandTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function getExpectedCommand(): string
    {
        return CFADDNX::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedId(): string
    {
        return 'CFADDNX';
    }

    /**
     * @group disconnected
     */
    public function testFilterArguments(): void
    {
        $actualArguments = ['key', 'item'];
        $expectedArguments = ['key', 'item'];

        $command = $this->getCommand();
        $command->setArguments($actualArguments);

        $this->assertSameValues($expectedArguments, $command->getArguments());
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
        $actualArguments = ['arg1'];
        $prefix = 'prefix:';
        $expectedArguments = ['prefix:arg1'];

        $command->setArguments($actualArguments);
        $command->prefixKeys($prefix);

        $this->assertSame($expectedArguments, $command->getArguments());
    }

    /**
     * @group connected
     * @group relay-resp3
     * @return void
     * @requiresRedisBfVersion >= 1.0.0
     */
    public function testAddItemToCuckooFilterWhenExists(): void
    {
        $redis = $this->getClient();

        $actualResponse = $redis->cfaddnx('key', 'item');
        $this->assertSame(1, $actualResponse);
        $this->assertSame(1, $redis->cfexists('key', 'item'));

        $actualResponse = $redis->cfaddnx('key', 'item');
        $this->assertSame(0, $actualResponse);
        $this->assertSame(1, $redis->cfexists('key', 'item'));
    }

    /**
     * @group connected
     * @return void
     * @requiresRedisBfVersion >= 2.6.0
     */
    public function testAddItemToCuckooFilterWhenExistsResp3(): void
    {
        $redis = $this->getResp3Client();

        $actualResponse = $redis->cfaddnx('key', 'item');
        $this->assertTrue($actualResponse);
        $this->assertTrue($redis->cfexists('key', 'item'));

        $actualResponse = $redis->cfaddnx('key', 'item');
        $this->assertFalse($actualResponse);
        $this->assertTrue($redis->cfexists('key', 'item'));
    }

    /**
     * @group connected
     * @group relay-resp3
     * @requiresRedisBfVersion >= 1.0.0
     */
    public function testThrowsExceptionOnWrongType(): void
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('Operation against a key holding the wrong kind of value');

        $redis = $this->getClient();

        $redis->set('cfaddnx_foo', 'bar');
        $redis->cfaddnx('cfaddnx_foo', 'foo');
    }
}
