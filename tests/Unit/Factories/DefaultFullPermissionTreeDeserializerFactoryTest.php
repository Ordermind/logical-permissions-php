<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\Factories;

use Ordermind\LogicalPermissions\Factories\DefaultFullPermissionTreeDeserializerFactory;
use Ordermind\LogicalPermissions\PermissionCheckerLocator;
use Ordermind\LogicalPermissions\Serializers\FullPermissionTreeDeserializer;
use Ordermind\LogicalPermissions\Serializers\PermissionTreeDeserializer;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\FlagPermissionChecker;
use Ordermind\LogicalPermissions\Test\Fixtures\PermissionChecker\RolePermissionChecker;
use Ordermind\LogicGates\LogicGateFactory;
use PHPUnit\Framework\TestCase;

class DefaultFullPermissionTreeDeserializerFactoryTest extends TestCase
{
    public function testCreate()
    {
        $permissionCheckers = [new RolePermissionChecker(), new FlagPermissionChecker()];
        $factory = new DefaultFullPermissionTreeDeserializerFactory();
        $expected = new FullPermissionTreeDeserializer(
            new PermissionTreeDeserializer(
                new PermissionCheckerLocator(...$permissionCheckers),
                new LogicGateFactory()
            )
        );
        $this->assertEquals($expected, $factory->create(...$permissionCheckers));
    }

    public function testCreateFromIterable()
    {
        $permissionCheckers = [new RolePermissionChecker(), new FlagPermissionChecker()];
        $factory = new DefaultFullPermissionTreeDeserializerFactory();
        $expected = new FullPermissionTreeDeserializer(
            new PermissionTreeDeserializer(
                new PermissionCheckerLocator(...$permissionCheckers),
                new LogicGateFactory()
            )
        );
        $this->assertEquals($expected, $factory->createFromIterable($permissionCheckers));
    }
}