<?php

namespace Ordermind\LogicalPermissions;

interface PermissionTypeCollectionInterface
{
    /**
     * Adds a permission type to the collection.
     *
     * @param PermissionTypeInterface $permissionType
     * @param bool                    $overwriteIfExists (optional) If the permission type already exists in the
     * collection, it will be overwritten if this parameter is set to TRUE. If it is set to FALSE,
     * Ordermind\LogicalPermissions\Exceptions\PermissionTypeAlreadyExistsException will be thrown.
     * Default value is FALSE.
     *
     * @return PermissionTypeCollectionInterface
     */
    public function add(PermissionTypeInterface $permissionType, $overwriteIfExists = false);

    /**
     * Removes a permission type by name from the collection. If the permission type cannot be found in the collection,
     * nothing happens.
     *
     * @param string $name The name of the permission type.
     *
     * @return PermissionTypeCollectionInterface
     */
    public function remove($name);

    /**
     * Checks if a permission type exists in the collection.
     *
     * @param string $name The name of the permission type.
     *
     * @return bool
     */
    public function has($name);

    /**
     * Gets a permission type by name. If the permission type cannot be found, NULL is returned.
     *
     * @param string $name The name of the permission type.
     *
     * @return PermissionTypeInterface|null
     */
    public function get($name);

    /**
     * Returns a PHP array representation of this collection.
     *
     * @return array
     */
    public function toArray();
}
