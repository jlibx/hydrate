<?php
declare(strict_types=1);

namespace Kabunx\Hydrate\Test;

use PHPUnit\Framework\TestCase;

final class EntityTest extends TestCase
{

    /**
     * @return array
     */
    public function testNotEmpty(): array
    {
        $source = [
            'id' => 1,
            'name' => 'test01',
            'email' => 'test01@sp.local',
            'gender' => 'm',
            'birthday' => '2000-10-01',
            'createdAt' => '2021-07-23',
            'modifiedAt' => '2021-07-23 12:05:10'
        ];
        $this->assertNotEmpty($source);

        return $source;
    }

    /**
     * @depends testNotEmpty
     */
    public function testArray2Entity(array $source): User
    {
        $user = User::instance($source);
        // 属性断言
        $this->assertSame('test01', $user->name);
        $this->assertSame(0, $user->gender);

        return $user;
    }

    /**
     * @depends testArray2Entity
     */
    public function testEntity2Array(User $user): void
    {
        $target = $user->toArray();
        $this->assertIsArray($target);
    }
}