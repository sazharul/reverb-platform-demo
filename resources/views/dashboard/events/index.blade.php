@extends('layouts.dashboard')

@section('title', 'Event Logs')
@section('breadcrumb', 'Dashboard / Event Logs')

@section('content')
    <livewire:dashboard.event-log-table />
@endsection

