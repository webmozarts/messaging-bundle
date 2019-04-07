<?php

/*
 * This file is part of the Webmozarts Messaging Bundle.
 *
 * (c) 2016-2019 Bernhard Schussek <bernhard.schussek@webmozarts.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Webmozarts\MessagingBundle\Command;

use OldSound\RabbitMqBundle\Command\BaseConsumerCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class ConsumerCommand extends BaseConsumerCommand
{
    protected function configure()
    {
        parent::configure();

        $this->getDefinition()->setArguments([]);

        $this
            ->setName('messaging:consumer')
            ->setDescription('Executes the consumer for a message channel')
            ->addArgument('channel', InputArgument::REQUIRED, 'The channel name')
            ->addArgument('partition', InputArgument::OPTIONAL, 'The name of the partition')
        ;
    }

    protected function getConsumerService()
    {
        return 'unused';
    }

    protected function initConsumer($input)
    {
        /* @var InputInterface $input */
        $this->consumer = $this->getContainer()->get('webmozarts_messaging.rabbit_mq.consumer');

        $channelRegistry = $this->getContainer()->get('webmozarts_messaging.channel_registry');

        $channel = $channelRegistry->getChannel($input->getArgument('channel'));
        $partition = null !== $input->getArgument('partition')
            ? $channel->getPartition($input->getArgument('partition'))
            : null;

        $this->consumer->selectChannel($channel, $partition);

        if (null !== $input->getOption('memory-limit')
            && ctype_digit((string) $input->getOption('memory-limit'))
            && $input->getOption('memory-limit') > 0) {
            $this->consumer->setMemoryLimit($input->getOption('memory-limit'));
        }

        $this->consumer->setRoutingKey($input->getOption('route'));
    }
}
