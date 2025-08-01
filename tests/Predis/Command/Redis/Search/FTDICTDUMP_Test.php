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

namespace Predis\Command\Redis\Search;

use Predis\Command\PrefixableCommand;
use Predis\Command\Redis\PredisCommandTestCase;
use Predis\Response\ServerException;

/**
 * @group commands
 * @group realm-stack
 */
class FTDICTDUMP_Test extends PredisCommandTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function getExpectedCommand(): string
    {
        return FTDICTDUMP::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedId(): string
    {
        return 'FTDICTDUMP';
    }

    /**
     * @group disconnected
     */
    public function testFilterArguments(): void
    {
        $actualArguments = ['dict'];
        $expectedArguments = ['dict'];

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
     * @requiresRediSearchVersion >= 1.4.0
     */
    public function testDumpTermsFromGivenDictionary(): void
    {
        $redis = $this->getClient();

        $redis->ftdictadd('dict', 'foo', 'bar');

        $this->assertSame(['bar', 'foo'], $redis->ftdictdump('dict'));
    }

    /**
     * @group connected
     * @return void
     * @requiresRediSearchVersion >= 2.8.0
     */
    public function testDumpTermsFromGivenDictionaryResp3(): void
    {
        $redis = $this->getResp3Client();

        $redis->ftdictadd('dict', 'foo', 'bar');

        $this->assertSame(['bar', 'foo'], $redis->ftdictdump('dict'));
    }

    /**
     * @group connected
     * @group relay-resp3
     * @return void
     * @requiresRediSearchVersion >= 1.4.0
     * @requiresRedisVersion <= 7.9.0
     */
    public function testThrowsExceptionOnNonExistingDictionary(): void
    {
        $redis = $this->getClient();

        $this->expectException(ServerException::class);

        $redis->ftdictdump('dict');
    }
}
