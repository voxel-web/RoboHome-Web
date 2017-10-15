<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Common\Controller;
use App\Http\Globals\FlashMessageLevels;
use App\Http\MQTT\MessagePublisher;
use App\Repositories\IDeviceRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DevicesController extends Controller
{
    private $deviceRepository;
    private $messagePublisher;

    public function __construct(IDeviceRepository $deviceRepository, MessagePublisher $messagePublisher)
    {
        $this->middleware('auth');

        $this->deviceRepository = $deviceRepository;
        $this->messagePublisher = $messagePublisher;
    }

    public function devices(Request $request): View
    {
        $currentUser = $request->user();

        return view('devices', [
            'name' => $currentUser->name,
            'devices' => $currentUser->devices
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $properties = $request->all();
        $currentUserId = $request->user()->id;
        $device = $this->deviceRepository->create($properties, $currentUserId);
        $name = $device->name;

        $request->session()->flash(FlashMessageLevels::SUCCESS, "Device '$name' was successfully added!");

        return redirect()->route('devices');
    }

    public function delete(Request $request, int $id): RedirectResponse
    {
        $doesUserOwnDevice = $request->user()->doesUserOwnDevice($id);

        if (!$doesUserOwnDevice) {
            $request->session()->flash(FlashMessageLevels::DANGER, 'Error deleting device!');

            return redirect()->route('devices');
        }

        $name = $this->deviceRepository->name($id);

        $this->deviceRepository->delete($id);

        $request->session()->flash(FlashMessageLevels::SUCCESS, "Device '$name' was successfully deleted!");

        return redirect()->route('devices');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $doesUserOwnDevice = $request->user()->doesUserOwnDevice($id);

        if (!$doesUserOwnDevice) {
            $request->session()->flash(FlashMessageLevels::DANGER, 'Error updating device!');

            return redirect()->route('devices');
        }

        $properties = $request->all();

        $device = $this->deviceRepository->update($id, $properties);

        $request->session()->flash(FlashMessageLevels::SUCCESS, "Device '$device->name' was successfully updated!");

        return redirect()->route('devices');
    }

    public function handleControlRequest(Request $request, string $action, int $deviceId): RedirectResponse
    {
        $currentUser = $request->user();
        $doesUserOwnDevice = $currentUser->doesUserOwnDevice($deviceId);

        if (!$doesUserOwnDevice) {
            $request->session()->flash(FlashMessageLevels::DANGER, 'Error controlling device!');

            return redirect()->route('devices');
        }

        $this->messagePublisher->publish($currentUser->id, $action, $deviceId);

        return redirect()->route('devices');
    }
}
