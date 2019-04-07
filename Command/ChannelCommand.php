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

use function iter\fn\method;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ChannelCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('messaging:channel')
            ->setDescription('Lists the available channels and partitions')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $channelRegistry = $this->getContainer()->get('webmozarts_messaging.channel_registry');
        $channels = $channelRegistry->getChannels();
        $lineFormat = '%-20s %s';

        $output->writeln(sprintf(
            $lineFormat,
            'Channel Name',
            'Partitions'
        ));

        foreach ($channels as $channel) {
            $output->writeln(sprintf(
                $lineFormat,
                $channel->getName(),
                implode(', ', array_map(method('getName'), $channel->getPartitions()))
            ));
        }
    }
}
