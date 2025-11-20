@extends('layouts.base')

@section('content')
  <div id="app">
    <devices-page :devices='@json($devices)'></devices-page>
  </div>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
@endsection
