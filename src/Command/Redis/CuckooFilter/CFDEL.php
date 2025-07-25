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

use Predis\Command\PrefixableCommand as RedisCommand;

/**
 * @see https://redis.io/commands/cf.del/
 *
 * Deletes an item once from the filter.
 * If the item exists only once, it will be removed from the filter.
 * If the item was added multiple times, it will still be present.
 */
class CFDEL extends RedisCommand
{
    public function getId()
    {
        return 'CF.DEL';
    }

    public function prefixKeys($prefix)
    {
        $this->applyPrefixForFirstArgument($prefix);
    }
}
