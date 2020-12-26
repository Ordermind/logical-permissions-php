<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\PermissionTree\PermissionTreeNode;

/**
 * DTO that holds the evaluated value and debug information of a permission tree node.
 */
class PermissionTreeNodeDebugValue
{
    private bool $internalValue;

    /**
     * @param array|string|bool $permissions
     */
    private $permissions;

    /**
     * @param array|string|bool $permissions
     */
    public function __construct(bool $internalValue, $permissions)
    {
        $this->internalValue = $internalValue;
        $this->permissions = $permissions;
    }

    public function getInternalValue(): bool
    {
        return $this->internalValue;
    }

    /**
     * @return array|string|bool $permissions
     */
    public function getPermissions()
    {
        return $this->permissions;
    }
}
