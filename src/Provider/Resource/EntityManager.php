<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Provider\Resource;

use LowlyPHP\Exception\StorageReadException;
use LowlyPHP\Service\Resource\EntityInterface;
use LowlyPHP\Service\Resource\EntityManagerInterface;
use LowlyPHP\Service\Resource\EntityMapperInterface;
use LowlyPHP\Service\Resource\ScopedEntityInterface;
use LowlyPHP\Service\Resource\Storage\SchemaMapperInterface;

/**
 * Default entity manager implementation for {@see EntityManagerInterface}.
 */
class EntityManager implements EntityManagerInterface
{
    /** @var array */
    private $cache = [];

    /** @var array */
    private $entities = [];

    /** @var EntityMapperInterface */
    private $entityMapper;

    /** @var SchemaMapperInterface */
    private $schemaMapper;

    /** @var StorageFactory */
    private $storageFactory;

    /**
     * @param EntityMapperInterface $entityMapper
     * @param StorageFactory $storageFactory
     * @param SchemaMapperInterface $schemaMapper
     * @codeCoverageIgnore
     */
    public function __construct(
        EntityMapperInterface $entityMapper,
        StorageFactory $storageFactory,
        SchemaMapperInterface $schemaMapper
    ) {
        $this->entityMapper = $entityMapper;
        $this->storageFactory = $storageFactory;
        $this->schemaMapper = $schemaMapper;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LowlyPHP\Exception\ConfigException
     */
    public function flush(EntityInterface $entity = null) : void
    {
        if ($entity === null) {
            /** @var EntityInterface $entity */
            foreach ($this->entities as $entity) {
                $this->flush($entity);
            }

            return;
        }

        /** @var \LowlyPHP\Service\Resource\StorageInterface $storage */
        $storage = $this->storageFactory->get(
            $this->schemaMapper->map($entity)
        );

        $entity->setEntityId(
            $storage->save($entity->export(), $entity->getEntityId())
        );

        unset($this->cache[$this->getCacheId($entity)]);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LowlyPHP\Exception\ConfigException
     */
    public function hydrate(EntityInterface $entity, array $data = null, bool $strict = false) : void
    {
        /** @var array $data */
        $data = \array_merge($this->getData($entity, $strict), (array) $data);
        $this->entityMapper->map($data, $entity);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LowlyPHP\Exception\ConfigException
     */
    public function persist(EntityInterface $entity) : void
    {
        $this->flush($entity);
        $this->entities[$entity->getEntityId()] = $entity;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LowlyPHP\Exception\ConfigException
     */
    public function remove(EntityInterface $entity) : void
    {
        /** @var \LowlyPHP\Service\Resource\StorageInterface $storage */
        $storage = $this->storageFactory->get(
            $this->schemaMapper->map($entity)
        );

        $storage->delete($entity->getEntityId());
    }

    /**
     * Generate a cache ID for the given entity.
     *
     * @param EntityInterface $entity
     * @return string
     */
    private function getCacheId(EntityInterface $entity) : string
    {
        return \sha1(
            \implode(
                ':',
                [
                    (string) $entity->getEntityId(),
                    $this->getReferenceType($entity),
                    (string) ($entity instanceof ScopedEntityInterface ? $entity->getScopeId() : 0),
                ]
            )
        );
    }

    /**
     * Load the data for the given entity.
     *
     * @param EntityInterface $entity
     * @param bool $strict
     * @return array
     * @throws StorageReadException
     * @throws \LowlyPHP\Exception\ConfigException
     */
    private function getData(EntityInterface $entity, bool $strict = false) : array
    {
        $cacheId = $this->getCacheId($entity);

        if (isset($this->cache[$cacheId])) {
            return $this->cache[$cacheId];
        }

        /** @var \LowlyPHP\Service\Resource\StorageInterface $storage */
        $storage = $this->storageFactory->get(
            $this->schemaMapper->map($entity)
        );

        $data = (array) $storage->load($entity->getEntityId());
        if (empty($data) && $strict) {
            throw new StorageReadException(\sprintf('No such entity with ID "%d."', $entity->getEntityId()));
        }

        $this->cache[$cacheId] = $data;

        return $data;
    }

    /**
     * Lookup the reference type for the given entity.
     *
     * @param EntityInterface $entity
     * @return string
     */
    private function getReferenceType(EntityInterface $entity) : string
    {
        return (string) \current(\class_implements($entity));
    }
}
