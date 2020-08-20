<?php

declare(strict_types=1);

namespace Ordermind\LogicalPermissions\Test\Unit\PermissionTree;

use Ordermind\LogicalPermissions\PermissionTree\BooleanPermission;
use PHPUnit\Framework\TestCase;

class BooleanPermissionTest extends TestCase
{
    /**
     * @dataProvider getValueProvider
     */
    public function testGetValue(bool $expectedResult, bool $input)
    {
        $permission = new BooleanPermission($input);

        $this->assertSame($expectedResult, $permission->getValue());
    }

    public function getValueProvider()
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}