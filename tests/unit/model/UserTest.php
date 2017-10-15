<?php

namespace Tests\Unit\Model;

use App\Device;
use App\User;

class UserTest extends ModelTestCase
{
    public function testDevices_GivenNoDevicesExist_ReturnsZeroDevices(): void
    {
        $user = $this->createUser();

        $devices = $user->devices();

        $this->assertEquals(0, $devices->count());
    }

    public function testDevices_GivenUserHasSeveralDevices_ReturnsAllDevices(): void
    {
        $user = $this->createUser();
        $numberOfDevices = self::$faker->randomDigit();

        $this->createSeveralDevicesForUser($user, $numberOfDevices);

        $devices = $user->devices();

        $this->assertEquals($numberOfDevices, $devices->count());
    }

    public function testDoesUserOwnDevice_GivenFirstUserDoesNotOwnAnyDevices_ReturnsFalse(): void
    {
        $firstUser = $this->createUser();
        $secondUser = $this->createUser();

        $deviceIdForSecondUser = $this->createSingleDeviceForUser($secondUser)->id;

        $doesUserOwnDevice = $firstUser->doesUserOwnDevice($deviceIdForSecondUser);

        $this->assertFalse($doesUserOwnDevice);
    }

    public function testDoesUserOwnDevice_GivenUserOwnsDevice_ReturnsTrue(): void
    {
        $user = $this->createUser();
        $device = $this->createSingleDeviceForUser($user);

        $doesUserOwnDevice = $user->doesUserOwnDevice($device->id);

        $this->assertTrue($doesUserOwnDevice);
    }

    private function createUser(): User
    {
        $user = factory(User::class)->create();

        return $user;
    }

    private function createSingleDeviceForUser(User $user): Device
    {
        $device = factory(Device::class)->create([
            'user_id' => $user->id
        ]);

        return $device;
    }

    private function createSeveralDevicesForUser(User $user, int $numberOfDevicesForUser): void
    {
        factory(Device::class, $numberOfDevicesForUser)->create([
            'user_id' => $user->id
        ]);
    }
}
