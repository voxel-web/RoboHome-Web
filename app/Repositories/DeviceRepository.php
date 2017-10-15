<?php

namespace App\Repositories;

use App\Device;
use App\Http\Globals\DeviceTypes;

class DeviceRepository implements IDeviceRepository
{
    private $rfDeviceRepository;

    public function __construct(IRFDeviceRepository $rfDeviceRepository)
    {
        $this->rfDeviceRepository = $rfDeviceRepository;
    }

    public function create(array $deviceProperties, int $userId): Device
    {
        $device = new Device();

        $device->name = $deviceProperties['name'];
        $device->description = $deviceProperties['description'];
        $device->user_id = $userId;
        $device->device_type_id = DeviceTypes::RF_DEVICE;

        $device->save();

        $this->createSpecificDevice($device, $deviceProperties);

        return $device;
    }

    public function update(int $id, array $deviceProperties): Device
    {
        $device = Device::find($id);

        $device->name = $deviceProperties['name'];
        $device->description = $deviceProperties['description'];

        $this->updateSpecificDevice($device, $deviceProperties);

        $device->save();

        return $device;
    }

    public function get(int $id): Device
    {
        return Device::find($id);
    }

    public function delete(int $id): void
    {
        Device::destroy($id);
    }

    public function name(int $id): string
    {
        return Device::find($id)->name;
    }

    private function createSpecificDevice(Device $device, array $deviceProperties): void
    {
        $type = $device->device_type_id;
        $id = $device->id;

        switch($type) {
            case DeviceTypes::RF_DEVICE:
                $this->rfDeviceRepository->create($id, $deviceProperties);
            default:
        }
    }

    private function updateSpecificDevice(Device $device, array $deviceProperties): void
    {
        $type = $device->device_type_id;
        $id = $device->id;

        switch($type) {
            case DeviceTypes::RF_DEVICE:
                $this->rfDeviceRepository->update($id, $deviceProperties);
            default:
        }
    }
}
