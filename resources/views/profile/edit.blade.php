@extends('layouts.app')

@section('title', 'Perfil de Usuario')

@section('content')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
        {{ __('Profile') }}
    </h2>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 bg-white shadow sm:rounded-lg">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="p-4 bg-white shadow sm:rounded-lg">
                @include('profile.partials.update-password-form')
            </div>

            <div class="p-4 bg-white shadow sm:rounded-lg">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
@endsection
