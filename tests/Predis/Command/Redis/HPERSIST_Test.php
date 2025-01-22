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

class HPERSIST_Test extends PredisCommandTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function getExpectedCommand(): string
    {
        return HPERSIST::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedId(): string
    {
        return 'HPERSIST';
    }

    /**
     * @group disconnected
     */
    public function testFilterArguments(): void
    {
        $command = $this->getCommand();
        $command->setArguments(['key', ['field1', 'field2']]);

        $this->assertSame(['key', 'FIELDS', 2, 'field1', 'field2'], $command->getArguments());
    }

    /**
     * @group disconnected
     */
    public function testParseResponse(): void
    {
        $command = $this->getCommand();

        $this->assertSame(0, $command->parseResponse(0));
        $this->assertSame(1, $command->parseResponse(1));
    }

    /**
     * @group connected
     * @group medium
     * @return void
     * @requiresRedisVersion >= 7.3.0
     */
    public function testRemovesExpirationFromGivenField(): void
    {
        $redis = $this->getClient();

        $redis->hset('hashkey', 'field1', 'value1', 'field2', 'value2');

        $this->assertSame([1, 1], $redis->hpexpire('hashkey', 100, ['field1', 'field2']));
        $this->assertSame([1], $redis->hpersist('hashkey', ['field1']));
        $this->sleep(0.2);
        $this->assertSame(['field1' => 'value1'], $redis->hgetall('hashkey'));
        $this->assertSame([-2], $redis->hpersist('wrongkey', ['field1']));
    }
}