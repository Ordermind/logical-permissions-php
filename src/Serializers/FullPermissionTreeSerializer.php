<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Serializers;

use Ordermind\LogicalPermissions\PermissionTree\FullPermissionTree;

/**
 * Converts a full permission tree into a serialized array representation.
 */
class FullPermissionTreeSerializer
{
    private PermissionTreeSerializer $treeSerializer;

    public function __construct(PermissionTreeSerializer $treeSerializer)
    {
        $this->treeSerializer = $treeSerializer;
    }

    public function serialize(FullPermissionTree $fullPermissionTree): array
    {
        $permissions = $this->treeSerializer->serialize($fullPermissionTree->getMainTree());

        if ($fullPermissionTree->hasNoBypassTree()) {
            $permissions['NO_BYPASS'] = $this->treeSerializer->serialize($fullPermissionTree->getNoBypassTree());
        }

        return $permissions;
    }
}