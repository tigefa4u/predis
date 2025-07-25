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

namespace Predis\Command\Redis\Json;

use Predis\Command\PrefixableCommand;
use Predis\Command\Redis\PredisCommandTestCase;

/**
 * @group commands
 * @group realm-stack
 */
class JSONSTRLEN_Test extends PredisCommandTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function getExpectedCommand(): string
    {
        return JSONSTRLEN::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedId(): string
    {
        return 'JSONSTRLEN';
    }

    /**
     * @group disconnected
     */
    public function testFilterArguments(): void
    {
        $arguments = ['key', '$..'];
        $expected = ['key', '$..'];

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
     * @dataProvider jsonProvider
     * @param  array  $jsonArguments
     * @param  string $key
     * @param  string $path
     * @param  array  $expectedStringLength
     * @return void
     * @requiresRedisJsonVersion >= 1.0.0
     */
    public function testReturnsLengthOfGivenJsonString(
        array $jsonArguments,
        string $key,
        string $path,
        array $expectedStringLength
    ): void {
        $redis = $this->getClient();

        $redis->jsonset(...$jsonArguments);

        $this->assertSame($expectedStringLength, $redis->jsonstrlen($key, $path));
    }

    /**
     * @group connected
     * @return void
     * @requiresRedisJsonVersion >= 1.0.0
     */
    public function testReturnsLengthOfGivenJsonStringResp3(): void
    {
        $redis = $this->getResp3Client();

        $redis->jsonset('key', '$', '{"key1":"value1","key2":"value2"}');

        $this->assertSame([6], $redis->jsonstrlen('key', '$.key2'));
    }

    public function jsonProvider(): array
    {
        return [
            'on root level' => [
                ['key', '$', '{"key1":"value1","key2":"value2"}'],
                'key',
                '$.key2',
                [6],
            ],
            'on nested level' => [
                ['key', '$', '{"key1":{"key2":"value2"}}'],
                'key',
                '$..key2',
                [6],
            ],
            'on both levels' => [
                ['key', '$', '{"key1":{"key2":"value2"},"key2":"value2"}'],
                'key',
                '$..key2',
                [6, 6],
            ],
            'with non-json string' => [
                ['key', '$', '{"key1":{"key2":[1,2,3]}}'],
                'key',
                '$..key2',
                [null],
            ],
        ];
    }
}
