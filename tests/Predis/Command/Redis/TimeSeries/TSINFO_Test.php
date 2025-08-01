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

namespace Predis\Command\Redis\TimeSeries;

use Predis\Command\Argument\TimeSeries\CommonArguments;
use Predis\Command\Argument\TimeSeries\CreateArguments;
use Predis\Command\Argument\TimeSeries\InfoArguments;
use Predis\Command\PrefixableCommand;
use Predis\Command\Redis\PredisCommandTestCase;

/**
 * @group commands
 * @group realm-stack
 */
class TSINFO_Test extends PredisCommandTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function getExpectedCommand(): string
    {
        return TSINFO::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedId(): string
    {
        return 'TSINFO';
    }

    /**
     * @group disconnected
     * @dataProvider argumentsProvider
     */
    public function testFilterArguments(array $actualArguments, array $expectedArguments): void
    {
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
     * @requiresRedisTimeSeriesVersion >= 1.10.13
     * @requiresRedisVersion > 6.3.0
     */
    public function testReturnsInformationAboutGivenTimeSeries(): void
    {
        $redis = $this->getClient();
        $expectedResponse = ['totalSamples', 0, 'memoryUsage', 5000, 'firstTimestamp', 0, 'lastTimestamp', 0,
            'retentionTime', 60000, 'chunkCount', 1, 'chunkSize', 4096, 'chunkType', 'compressed', 'duplicatePolicy',
            'max', 'labels', [['sensor_id', '2'], ['area_id', '32']], 'sourceKey', null, 'rules', [],
            'ignoreMaxTimeDiff', 0, 'ignoreMaxValDiff', 0];

        $arguments = (new CreateArguments())
            ->retentionMsecs(60000)
            ->duplicatePolicy(CommonArguments::POLICY_MAX)
            ->labels('sensor_id', 2, 'area_id', 32);

        $this->assertEquals(
            'OK',
            $redis->tscreate('temperature:2:32', $arguments)
        );

        $this->assertEqualsWithDelta($expectedResponse, $redis->tsinfo('temperature:2:32'), 1000);
    }

    /**
     * @group connected
     * @group relay-resp3
     * @return void
     * @requiresRedisTimeSeriesVersion <= 1.10.13
     * @requiresRedisVersion > 6.3.0
     */
    public function testReturnsInformationAboutGivenTimeSeriesResp3(): void
    {
        $redis = $this->getResp3Client();
        $expectedResponse = ['totalSamples' => 0, 'memoryUsage' => 5000, 'firstTimestamp' => 0, 'lastTimestamp' => 0,
            'retentionTime' => 60000, 'chunkCount' => 1, 'chunkSize' => 4096, 'chunkType' => 'compressed',
            'duplicatePolicy' => 'max', 'labels' => ['sensor_id' => '2', 'area_id' => '32'], 'sourceKey' => null, 'rules' => [],
            'ignoreMaxTimeDiff' => 0, 'ignoreMaxValDiff' => 0];

        $arguments = (new CreateArguments())
            ->retentionMsecs(60000)
            ->duplicatePolicy(CommonArguments::POLICY_MAX)
            ->labels('sensor_id', 2, 'area_id', 32);

        $this->assertEquals(
            'OK',
            $redis->tscreate('temperature:2:32', $arguments)
        );

        $this->assertEqualsWithDelta($expectedResponse, $redis->tsinfo('temperature:2:32'), 1000);
    }

    public function argumentsProvider(): array
    {
        return [
            'with default arguments' => [
                ['key'],
                ['key'],
            ],
            'with DEBUG modifier' => [
                ['key', (new InfoArguments())->debug()],
                ['key', 'DEBUG'],
            ],
        ];
    }
}
