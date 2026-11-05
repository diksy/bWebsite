@extends('layouts.panel')
@section('title', 'Dashboard')
@section('content')
    <h1 class="text-2xl font-bold">Halo, {{ auth()->user()->name }}</h1>
@endsection
