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

use Predis\Command\Argument\Search\CreateArguments;
use Predis\Command\Argument\Search\SchemaFields\TextField;
use Predis\Command\PrefixableCommand;
use Predis\Command\Redis\PredisCommandTestCase;
use Predis\Response\ServerException;

/**
 * @group commands
 * @group realm-stack
 */
class FTINFO_Test extends PredisCommandTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function getExpectedCommand(): string
    {
        return FTINFO::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedId(): string
    {
        return 'FTINFO';
    }

    /**
     * @group disconnected
     */
    public function testFilterArguments(): void
    {
        $actualArguments = ['index'];
        $expectedArguments = ['index'];

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
     * @requiresRediSearchVersion >= 1.0.0
     *
     * Prior to Redis 7.2 `-nan` is messing with Relay/hiredis.
     */
    public function testInfoReturnsInformationAboutGivenIndex(): void
    {
        $redis = $this->getClient();

        $arguments = new CreateArguments();
        $arguments->prefix(['prefix:']);
        $arguments->language();

        $schema = [new TextField('text_field')];

        $createResponse = $redis->ftcreate('index', $schema, $arguments);
        $this->assertEquals('OK', $createResponse);

        $actualResponse = $redis->ftinfo('index');
        $this->assertEquals('index', $actualResponse[1]);
    }

    /**
     * @group connected
     * @group relay-resp3
     * @return void
     * @requiresRediSearchVersion >= 2.8.0
     */
    public function testInfoReturnsInformationAboutGivenIndexResp3(): void
    {
        $redis = $this->getResp3Client();

        $arguments = new CreateArguments();
        $arguments->prefix(['prefix:']);
        $arguments->language();

        $schema = [new TextField('text_field')];

        $createResponse = $redis->ftcreate('index', $schema, $arguments);
        $this->assertEquals('OK', $createResponse);

        $actualResponse = $redis->ftinfo('index');
        $this->assertEquals('index', $actualResponse['index_name']);
    }

    /**
     * @group connected
     * @group relay-resp3
     * @return void
     * @requiresRediSearchVersion >= 1.0.0
     */
    public function testThrowsExceptionOnNonExistingIndex(): void
    {
        $redis = $this->getClient();

        $this->expectException(ServerException::class);

        $redis->ftinfo('index');
    }
}
