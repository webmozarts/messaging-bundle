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

namespace Webmozarts\MessagingBundle\Serializer;

use Webmozarts\MessagingBundle\HandlerDescriptor\ServiceMethodHandlerDescriptor;
use Webmozarts\MessagingBundle\Message\WrappedMessageWithHandlerDescriptors;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

class WrappedMessageWithHandlerDescriptorsNormalizer implements SerializerAwareInterface, NormalizerInterface, DenormalizerInterface
{
    use SerializerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $serializer = $this->serializer;

        if (!$serializer instanceof DenormalizerInterface) {
            throw new LogicException('Cannot denormalize message parameters because injected serializer is not a denormalizer.');
        }

        return new WrappedMessageWithHandlerDescriptors(
            $serializer->denormalize($data['message'], $data['message_type'], 'json', $context),
            array_map(
                function (array $data) use ($serializer, $context) {
                    return $serializer->denormalize($data, ServiceMethodHandlerDescriptor::class, 'json', $context);
                },
                $data['handler_descriptors']
            )
        );
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return WrappedMessageWithHandlerDescriptors::class === $type;
    }

    public function normalize($object, $format = null, array $context = array())
    {
        /** @var WrappedMessageWithHandlerDescriptors $object */
        $serializer = $this->serializer;

        if (!$serializer instanceof NormalizerInterface) {
            throw new LogicException('Cannot normalize message parameters because injected serializer is not a normalizer.');
        }

        return [
            'message' => $serializer->normalize($object->getMessage(), $format, $context),
            'message_type' => get_class($object->getMessage()),
            'handler_descriptors' => array_map(
                function (ServiceMethodHandlerDescriptor $handlerDescriptor) use ($serializer, $format, $context) {
                    return $serializer->normalize($handlerDescriptor, $format, $context);
                },
                $object->getHandlerDescriptors()
            ),
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof WrappedMessageWithHandlerDescriptors;
    }
}
