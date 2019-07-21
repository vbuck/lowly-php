<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Provider\Resource;

use LowlyPHP\ApplicationManager;
use LowlyPHP\Service\Resource\EntityInterface;
use LowlyPHP\Service\Resource\EntityMapperInterface;
use LowlyPHP\Service\Resource\SerializableInterface;
use LowlyPHP\Service\Resource\SerializerInterface;

/**
 * Entity mapper implementation for {@see EntityMapperInterface}.
 *
 * Expects that implementations consistently support a snake-case to camel-case relationship on data keys to methods.
 */
class EntityMapper implements EntityMapperInterface
{
    /** @var ApplicationManager */
    private $app;

    /** @var SerializerInterface */
    private $serializer;

    /**
     * @param SerializerInterface $serializer
     * @param ApplicationManager|null $app
     */
    public function __construct(SerializerInterface $serializer, ApplicationManager $app = null)
    {
        $this->app = $app ?? ApplicationManager::getInstance();
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function map(array $data, EntityInterface $entity) : void
    {
        $properties = \array_keys($entity->export());

        foreach ($data as $key => $value) {
            if (!\in_array($key, $properties)) {
                continue;
            }

            $method = 'set'
                . \str_replace(
                    ' ',
                    '',
                    \ucwords(str_replace('_', ' ', $key))
                );

            if (\method_exists($entity, $method)) {
                \call_user_func([$entity, $method], $this->prepareValue($entity, $method, $value));
            }
        }
    }

    /**
     * Convert the given value to its expects input type.
     *
     * @param EntityInterface $entity
     * @param $method
     * @param mixed $value
     * @return mixed
     * @throws \ReflectionException
     * @throws \Exception
     */
    private function prepareValue(EntityInterface $entity, $method, $value)
    {
        /** @var \ReflectionMethod $signature */
        $signature = new \ReflectionMethod($entity, $method);
        /** @var \ReflectionParameter $argument */
        $argument = \current($signature->getParameters());
        /** @var \ReflectionType|null $type */
        $type = $argument->getType();
        $interfaces = [];

        if ($type === null) {
            return $value;
        } elseif (!$type->isBuiltin() && (\class_exists($type->getName()) || \interface_exists($type->getName()))) {
            $interfaces = \class_implements($type->getName());
        }

        if (\in_array(SerializableInterface::class, $interfaces)) {
            /** @var SerializableInterface $instance */
            $instance = $this->app->getObject($type->getName());
            $instance->restore((string) $value);

            return $instance;
        } elseif ($type->getName() === 'array' && !\is_array($value)) {
            return (array) $this->serializer->unserialize((string) $value);
        } elseif ($type->isBuiltin()) {
            \settype($value, $type->getName());
        }

        return $value;
    }
}
