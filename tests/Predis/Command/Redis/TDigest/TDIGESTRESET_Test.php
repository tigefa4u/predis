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

namespace Predis\Command\Redis\TDigest;

use Predis\Command\PrefixableCommand;
use Predis\Command\Redis\PredisCommandTestCase;
use Predis\Response\ServerException;

/**
 * @group commands
 * @group realm-stack
 */
class TDIGESTRESET_Test extends PredisCommandTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function getExpectedCommand(): string
    {
        return TDIGESTRESET::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedId(): string
    {
        return 'TDIGESTRESET';
    }

    /**
     * @group disconnected
     */
    public function testFilterArguments(): void
    {
        $actualArguments = ['key'];
        $expectedArguments = ['key'];

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
     * @requiresRedisBfVersion >= 2.4.0
     */
    public function testResetExistingSketch(): void
    {
        $redis = $this->getClient();

        $redis->tdigestcreate('key', 500);
        $redis->tdigestadd('key', 1, 2, 2, 3, 3, 3);

        $this->assertEquals(
            ['1', '2', '2', '3', '3', '3'],
            $redis->tdigestbyrank('key', 0, 1, 2, 3, 4, 5)
        );

        $actualResponse = $redis->tdigestreset('key');
        $info = $redis->tdigestinfo('key');

        $this->assertEquals('OK', $actualResponse);
        $this->assertSame(500, $info['Compression']);
        $this->assertEquals(
            ['nan', 'nan', 'nan', 'nan', 'nan', 'nan'],
            $redis->tdigestbyrank('key', 0, 1, 2, 3, 4, 5)
        );
    }

    /**
     * @group connected
     * @group relay-resp3
     * @return void
     * @requiresRedisBfVersion >= 2.6.0
     */
    public function testResetExistingSketchResp3(): void
    {
        $redis = $this->getResp3Client();

        $redis->tdigestcreate('key', 500);
        $redis->tdigestadd('key', 1, 2, 2, 3, 3, 3);

        $this->assertEquals(
            ['1', '2', '2', '3', '3', '3'],
            $redis->tdigestbyrank('key', 0, 1, 2, 3, 4, 5)
        );

        $actualResponse = $redis->tdigestreset('key');
        $info = $redis->tdigestinfo('key');

        $this->assertEquals('OK', $actualResponse);
        $this->assertSame(500, $info['Compression']);
        $this->assertEquals(
            [null, null, null, null, null, null],
            $redis->tdigestbyrank('key', 0, 1, 2, 3, 4, 5)
        );
    }

    /**
     * @group connected
     * @group relay-resp3
     * @return void
     * @requiresRedisBfVersion >= 2.4.0
     */
    public function testThrowsExceptionOnNonExistingTDigestSketch(): void
    {
        $redis = $this->getClient();

        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('ERR T-Digest: key does not exist');

        $redis->tdigestreset('key');
    }
}
