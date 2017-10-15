<?php

namespace Tests\Unit\Controller\Web;

use App\Device;
use App\Repositories\DeviceRepository;
use App\User;
use Illuminate\Foundation\Testing\TestResponse;
use Mockery;
use Tests\Unit\Controller\Common\DevicesControllerTestCase;

class DevicesControllerTest extends DevicesControllerTestCase
{
    public function testDevices_GivenUserNotLoggedIn_RedirectToLogin(): void
    {
        $response = $this->get('/devices');

        $this->assertRedirectedToRouteWith302($response, '/login');
    }

    public function testDevices_GivenUserLoggedIn_ViewContainsUsersName(): void
    {
        $user = $this->createUser();

        $mockUser = Mockery::mock(User::class);
        $mockUser
            ->shouldReceive('getAttribute')->with('name')->once()->andReturn($user->name)
            ->shouldReceive('getAttribute')->with('devices')->once()->andReturn([]);

        $response = $this->actingAs($mockUser)->get('/devices');

        $response->assertSee($user->name . '\'s Controllable Devices');
        $response->assertStatus(200);
    }

    public function testDevices_GivenUserLoggedIn_ViewContainsUsersDevices(): void
    {
        $user = $this->createUser();
        $deviceName = self::$faker->word();
        $deviceDescription = self::$faker->sentence();
        $htmlAttributeName = self::$faker->word();
        $htmlAttributeValue = self::$faker->randomDigit();
        $htmlAttribute = 'data-device-' . $htmlAttributeName . '=' . $htmlAttributeValue;

        $mockDevice = Mockery::mock(Device::class);
        $mockDevice
            ->shouldReceive('getAttribute')->with('id')->atLeast()->once()->andReturn(self::$faker->randomDigit())
            ->shouldReceive('getAttribute')->with('name')->atLeast()->once()->andReturn($deviceName)
            ->shouldReceive('getAttribute')->with('description')->atLeast()->once()->andReturn($deviceDescription)
            ->shouldReceive('htmlDataAttributesForSpecificDeviceProperties')->once()->andReturn([ $htmlAttribute ]);

        $this->app->instance(Device::class, $mockDevice);

        $mockUser = Mockery::mock(User::class);
        $mockUser
            ->shouldReceive('getAttribute')->with('name')->once()->andReturn($user->name)
            ->shouldReceive('getAttribute')->with('devices')->once()->andReturn([$mockDevice]);

        $response = $this->actingAs($mockUser)->get('/devices');

        $response->assertSee($htmlAttribute);
        $response->assertSee($deviceName);
        $response->assertSee($deviceDescription);
        $response->assertStatus(200);
    }

    public function testAdd_GivenPostedData_CallsAddOnModelsThenRedirectsToDevices(): void
    {
        $user = $this->givenSingleUserExists();
        $device = factory(Device::class)->make();

        $response = $this->callAdd($device, $user);

        $this->assertRedirectedToRouteWith302($response, '/devices');
    }

    public function testAdd_GivenSingleUserExists_SessionContainsSuccessMessage(): void
    {
        $user = $this->givenSingleUserExists();
        $device = factory(Device::class)->make();

        $response = $this->callAdd($device, $user);

        $response->assertSessionHas('alert-success');
    }

    public function testDelete_GivenUserDoesNotOwnDevice_RedirectToDevices(): void
    {
        $response = $this->callDeleteOnDeviceUserDoesNotOwn();

        $this->assertRedirectedToRouteWith302($response, '/devices');
    }

    public function testDelete_GivenUserDoesNotOwnDevice_SessionContainsErrorMessage(): void
    {
        $response = $this->callDeleteOnDeviceUserDoesNotOwn();

        $response->assertSessionHas('alert-danger');
    }

    public function testDelete_GivenUserOwnsDevice_RedirectToDevices(): void
    {
        $response = $this->callDeleteOnDeviceUserOwns();

        $this->assertRedirectedToRouteWith302($response, '/devices');
    }

    public function testDelete_GivenUserOwnsDevice_SessionContainsSuccessMessage(): void
    {
        $response = $this->callDeleteOnDeviceUserOwns();

        $response->assertSessionHas('alert-success');
    }

    public function testUpdate_GivenUserDoesNotOwnDevice_RedirectToDevices(): void
    {
        $device = factory(Device::class)->make([
            'id' => self::$faker->randomNumber()
        ]);

        $mockUser = $this->mockUserOwnsDevice($device->id, false);

        $response = $this->callUpdate($mockUser, $device, self::$faker->word(), self::$faker->sentence(), self::$faker->randomNumber(), self::$faker->randomNumber(), self::$faker->randomNumber());

        $this->assertRedirectedToRouteWith302($response, '/devices');
    }

    public function testUpdate_GivenUserDoesNotOwnDevice_SessionContainsErrorMessage(): void
    {
        $device = factory(Device::class)->make([
            'id' => self::$faker->randomNumber()
        ]);

        $mockUser = $this->mockUserOwnsDevice($device->id, false);

        $response = $this->callUpdate($mockUser, $device, self::$faker->word(), self::$faker->sentence(), self::$faker->randomNumber(), self::$faker->randomNumber(), self::$faker->randomNumber());

        $response->assertSessionHas('alert-danger');
    }

    public function testUpdate_GivenUserOwnsDevice_RedirectToDevices(): void
    {
        $device = factory(Device::class)->make([
            'id' => self::$faker->randomNumber()
        ]);

        $mockUser = $this->mockUserOwnsDevice($device->id, true);

        $response = $this->callUpdate($mockUser, $device, self::$faker->word(), self::$faker->sentence(), self::$faker->randomNumber(), self::$faker->randomNumber(), self::$faker->randomNumber());

        $this->assertRedirectedToRouteWith302($response, '/devices');
    }

    public function testUpdate_GivenUserOwnsDevice_SessionContainsSuccessMessage(): void
    {
        $device = factory(Device::class)->make([
            'id' => self::$faker->randomNumber()
        ]);

        $mockUser = $this->mockUserOwnsDevice($device->id, true);

        $response = $this->callUpdate($mockUser, $device, self::$faker->word(), self::$faker->sentence(), self::$faker->randomNumber(), self::$faker->randomNumber(), self::$faker->randomNumber());

        $response->assertSessionHas('alert-success');
    }

    public function testUpdate_GivenUserOwnsDevice_ValuesForDeviceChanged(): void
    {
        $device = factory(Device::class)->make([
            'id' => self::$faker->randomNumber()
        ]);

        $mockUser = $this->mockUserOwnsDevice($device->id, true);

        $newDeviceName = self::$faker->word();
        $newDeviceDescription = self::$faker->sentence();
        $newOnCode = self::$faker->randomNumber();
        $newOffCode = self::$faker->randomNumber();
        $newPulseLength = self::$faker->randomNumber();

        $this->callUpdate($mockUser, $device, $newDeviceName, $newDeviceDescription, $newOnCode, $newOffCode, $newPulseLength);

        $deviceId = $device->id;
        $updatedDevice = Device::find($deviceId);

        echo 'a';
    }

    public function testHandleControlRequest_GivenUserExistsWithNoDevices_PublishIsNotCalled(): void
    {
        $deviceId = self::$faker->randomDigit();
        $action = self::$faker->word();

        $mockUser = $this->mockUserOwnsDevice($deviceId, false);
        $mockUser->shouldReceive('getAttribute')->with('id')->never();

        $this->mockMessagePublisher(0);

        $response = $this->callControl($mockUser, $action, $deviceId);

        $this->assertRedirectedToRouteWith302($response, '/devices');
    }

    public function testHandleControlRequest_GivenUserExistsWithNoDevices_SessionContainsErrorMessage(): void
    {
        $deviceId = self::$faker->randomDigit();
        $action = self::$faker->word();

        $mockUser = $this->mockUserOwnsDevice($deviceId, false);

        $response = $this->callControl($mockUser, $action, $deviceId);

        $response->assertSessionHas('alert-danger');
    }

    public function testHandleControlRequest_GivenUserExistsWithDevice_CallsPublish(): void
    {
        $userId = self::$faker->randomDigit();
        $deviceId = self::$faker->randomDigit();
        $action = self::$faker->word();

        $mockUser = $this->mockUserOwnsDevice($deviceId, true);
        $mockUser->shouldReceive('getAttribute')->with('id')->once()->andReturn($userId);

        $this->mockMessagePublisher(1);

        $response = $this->callControl($mockUser, $action, $deviceId);

        $this->assertRedirectedToRouteWith302($response, '/devices');
    }

    private function callAdd(Device $device, User $user): TestResponse
    {
        $mockDeviceRepository = Mockery::mock(DeviceRepository::class);
        $mockDeviceRepository->shouldReceive('create')->once()->andReturn($device);

        $this->app->instance(DeviceRepository::class, $mockDeviceRepository);

        $csrfToken = self::$faker->uuid();

        $response = $this->actingAs($user)->withSession(['_token' => $csrfToken])
            ->post('/devices/add', [
                'name' => $device->name,
                'description' => self::$faker->sentence(),
                'on_code' => self::$faker->randomDigit(),
                'off_code' => self::$faker->randomDigit(),
                'pulse_length' => self::$faker->randomDigit(),
                '_token' => $csrfToken
            ]);

        return $response;
    }

    private function callDeleteOnDeviceUserDoesNotOwn(): TestResponse
    {
        $deviceId = self::$faker->randomDigit();

        $mockUser = $this->mockUserOwnsDevice($deviceId, false);

        $mockDeviceRepository = Mockery::mock(DeviceRepository::class);
        $mockDeviceRepository
            ->shouldReceive('name')->never()->with($deviceId)
            ->shouldReceive('delete')->never()->with($deviceId);

        $this->app->instance(DeviceRepository::class, $mockDeviceRepository);

        $response = $this->actingAs($mockUser)->get("/devices/delete/$deviceId");

        return $response;
    }

    private function callDeleteOnDeviceUserOwns(): TestResponse
    {
        $deviceId = self::$faker->randomDigit();

        $mockUser = $this->mockUserOwnsDevice($deviceId, true);

        $mockDeviceRepository = Mockery::mock(DeviceRepository::class);
        $mockDeviceRepository
            ->shouldReceive('name')->once()->with($deviceId)
            ->shouldReceive('delete')->once()->with($deviceId);

        $this->app->instance(DeviceRepository::class, $mockDeviceRepository);

        $response = $this->actingAs($mockUser)->get("/devices/delete/$deviceId");

        return $response;
    }

    private function callUpdate(User $user, Device $device, string $newDeviceName, string $newDeviceDescription, int $newOnCode, int $newOffCode, int $newPulseLength): TestResponse
    {
        $mockDeviceRepository = Mockery::mock(DeviceRepository::class);
        $mockDeviceRepository->shouldReceive('update')->andReturn($device);

        $this->app->instance(DeviceRepository::class, $mockDeviceRepository);

        $csrfToken = self::$faker->uuid();

        $response = $this->actingAs($user)->withSession(['_token' => $csrfToken])
            ->post("/devices/update/$device->id", [
                'name' => $newDeviceName,
                'description' => $newDeviceDescription,
                'on_code' => $newOnCode,
                'off_code' => $newOffCode,
                'pulse_length' => $newPulseLength,
                '_token' => $csrfToken
            ]);

        return $response;
    }

    private function callControl(User $user, string $action, int $deviceId): TestResponse
    {
        $csrfToken = self::$faker->uuid();

        $response = $this->actingAs($user)->withSession(['_token' => $csrfToken])
            ->post("/devices/$action/$deviceId", [
                '_token' => $csrfToken
            ]);

        return $response;
    }

    private function assertDevicePropertiesMatch(Device $device, string $originalDeviceName, string $originalDeviceDescription, int $originalOnCode, int $originalOffCode, int $originalPulseLength): void
    {
        $this->assertEquals($originalDeviceName, $device->name);
        $this->assertEquals($originalDeviceDescription, $device->description);
        $this->assertEquals($originalOnCode, $device->on_code);
        $this->assertEquals($originalOffCode, $device->off_code);
        $this->assertEquals($originalPulseLength, $device->pulse_length);
    }
}
