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
class SDIFF_Test extends PredisCommandTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getExpectedCommand(): string
    {
        return 'Predis\Command\Redis\SDIFF';
    }

    /**
     * {@inheritdoc}
     */
    protected function getExpectedId(): string
    {
        return 'SDIFF';
    }

    /**
     * @group disconnected
     */
    public function testFilterArguments(): void
    {
        $arguments = ['key1', 'key2', 'key3'];
        $expected = ['key1', 'key2', 'key3'];

        $command = $this->getCommand();
        $command->setArguments($arguments);

        $this->assertSame($expected, $command->getArguments());
    }

    /**
     * @group disconnected
     */
    public function testFilterArgumentsAsSingleArray(): void
    {
        $arguments = [['key1', 'key2', 'key3']];
        $expected = ['key1', 'key2', 'key3'];

        $command = $this->getCommand();
        $command->setArguments($arguments);

        $this->assertSame($expected, $command->getArguments());
    }

    /**
     * @group disconnected
     */
    public function testParseResponse(): void
    {
        $raw = ['member1', 'member2', 'member3'];
        $expected = ['member1', 'member2', 'member3'];

        $command = $this->getCommand();

        $this->assertSame($expected, $command->parseResponse($raw));
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
        $expectedArguments = ['prefix:arg1', 'prefix:arg2', 'prefix:arg3', 'prefix:arg4'];

        $command->setArguments($actualArguments);
        $command->prefixKeys($prefix);

        $this->assertSame($expectedArguments, $command->getArguments());
    }

    /**
     * @group connected
     */
    public function testReturnsMembersOnSingleKeyOrNonExistingSetForDifference(): void
    {
        $redis = $this->getClient();

        $redis->sadd('letters:1st', 'a', 'b', 'c', 'd', 'e', 'f', 'g');

        $this->assertSameValues(['a', 'b', 'c', 'd', 'e', 'f', 'g'], $redis->sdiff('letters:1st'));
        $this->assertSameValues(['a', 'b', 'c', 'd', 'e', 'f', 'g'], $redis->sdiff('letters:1st', 'letters:2nd'));
    }

    /**
     * @group connected
     * @requiresRedisVersion >= 6.0.0
     */
    public function testReturnsMembersOnSingleKeyOrNonExistingSetForDifferenceResp3(): void
    {
        $redis = $this->getResp3Client();

        $redis->sadd('letters:1st', 'a', 'b', 'c', 'd', 'e', 'f', 'g');

        $this->assertSameValues(['a', 'b', 'c', 'd', 'e', 'f', 'g'], $redis->sdiff('letters:1st'));
        $this->assertSameValues(['a', 'b', 'c', 'd', 'e', 'f', 'g'], $redis->sdiff('letters:1st', 'letters:2nd'));
    }

    /**
     * @group connected
     */
    public function testReturnsMembersFromDifferenceAmongSets(): void
    {
        $redis = $this->getClient();

        $redis->sadd('letters:1st', 'a', 'b', 'c', 'd', 'e', 'f', 'g');
        $redis->sadd('letters:2nd', 'a', 'c', 'f', 'g');
        $redis->sadd('letters:3rd', 'a', 'b', 'e', 'f');

        $this->assertSameValues(['b', 'd', 'e'], $redis->sdiff('letters:1st', 'letters:2nd'));
        $this->assertSameValues(['d'], $redis->sdiff('letters:1st', 'letters:2nd', 'letters:3rd'));
    }

    /**
     * @group connected
     */
    public function testThrowsExceptionOnWrongType(): void
    {
        $this->expectException('Predis\Response\ServerException');
        $this->expectExceptionMessage('Operation against a key holding the wrong kind of value');

        $redis = $this->getClient();

        $redis->set('set:foo', 'a');
        $redis->sdiff('set:foo');
    }
}
