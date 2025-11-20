<div id="app">
  <devices-show-page
    :device-id="{{ $device->id }}"
    device-name="{{ $device->name }}"
    device-model="{{ $device->model }}"
  ></devices-show-page>
</div>
@vite(['resources/css/app.css', 'resources/js/app.js'])


