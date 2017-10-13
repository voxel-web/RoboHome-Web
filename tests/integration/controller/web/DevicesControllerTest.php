<?php

namespace Tests\integration\controller\web;

use App\Device;
use App\RFDevice;
use App\User;

class DevicesControllerTest extends IntegrationTestCase
{
    public function testDevices_GivenUserLoggedIn_ViewContainsUsersName(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/devices');

        $response->assertSee($user->name);
        $response->assertStatus(200);
    }

    public function testDevices_GivenUserLoggedIn_ViewContainsUsersDevices(): void
    {
        $user = $this->createUser();
        $devices = $this->createManyDevices($user->id);
        $response = $this->actingAs($user)->get('/devices');

        foreach ($devices as $device) {
            $response->assertSee($device->name);

            $specificDevice = RFDevice::where('device_id', $device->id)->first();
            $specificDeviceProperties = $specificDevice->getFillable();

            foreach ($specificDeviceProperties as $property) {
                $partialAttributeName = str_replace('_', '-', $property);
                $htmlDataAttribute = 'data-device-' . $partialAttributeName . '=' . $specificDevice->$property;
                $response->assertSee($htmlDataAttribute);
            }
        }

        $response->assertStatus(200);
    }

    private function createUser(): User
    {
        $user = factory(User::class)->create([
            'id' => self::$faker->randomDigit()
        ]);

        return $user;
    }

    private function createManyDevices(int $userId): array
    {
        $devices = array();
        $numberOfDevicesToCreate = self::$faker->randomDigit();

        for ($i = 0; $i < $numberOfDevicesToCreate; $i++) {
            $device = factory(Device::class)->create([
                'user_id' => $userId
            ]);

            factory(RFDevice::class)->create([
                'device_id' => $device->id
            ]);

            $devices[] = $device;
        }

        return $devices;
    }
}
