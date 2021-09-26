<?php
declare(strict_types=1);

namespace Kabunx\Hydrate\Test;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Kabunx\Hydrate\Entity
 * @covers \Kabunx\Hydrate\Hydrate
 * @covers \Kabunx\Hydrate\AttributeReader
 * @covers \Kabunx\Hydrate\Column
 * @covers \Kabunx\Hydrate\ArrayEntity
 */
final class EntityTest extends TestCase
{

    /**
     * @return array
     */
    public function testNotEmpty(): array
    {
        $source = [
            'id' => 1,
            'name' => 'test',
            'email' => 'test@sp.local',
            'gender' => 'female',
            'birthday' => '2000-10-01',
            'createdAt' => '2021-07-23',
            'modifiedAt' => '2021-07-23 12:05:10',
            'profile' => [
                'name' => '详情',
                'age' => 10,
                'is_validated' => true
            ],
            'addresses' => [
                ['name' => '默认地址', 'cityName' => '苏州'],
                ['name' => '地址', 'cityName' => '上海']
            ]
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
        $this->assertSame('test', $user->name);
        $this->assertSame('test@sp.local', $user->email);
        $this->assertSame(2, $user->gender);
        $this->assertInstanceOf(Carbon::class, $user->birthday);
        $this->assertIsArray($user->addresses);

        return $user;
    }

    /**
     * @depends testArray2Entity
     */
    public function testEntity2Array(User $user): void
    {
        $target = $user->toArray();
        $this->assertSame([
            'name' => 'test',
            'email' => 'test@sp.local',
            'gender' => 2,
            'birthday' => '2000-10-01',
            'created_at' => '2021-07-23 00:00:00',
            'updated_at' => '2021-07-23 12:05:10',
            'profile_name' => '详情',
            'profile' => [
                'name' => '详情',
                'age' => 10,
                'is_validated' => true
            ],
            'addresses' => [
                ['name' => '默认地址', 'city_name' => '苏州'],
                ['name' => '地址', 'city_name' => '上海']
            ]
        ], $target);
    }
}