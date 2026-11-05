@extends('layouts.panel')
@section('title', $artikel ? 'Sunting artikel' : 'Artikel baru')
@section('content')
    @livewire('admin.artikel-editor', ['artikel' => $artikel])
@endsection
