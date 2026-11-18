@extends('layouts.app')

@section('title', 'Home - WorkNest')

@section('content')
    <div class="rounded-3xl bg-cream p-8">
        <h1 class="text-center text-xl font-bold">Task List</h1>
        <p class="mt-4 text-center">Welcome, {{ auth()->user()->name }}!</p>
        <p class="mt-4 text-center">Your tasks will show up here.</p>
    </div>
@endsection