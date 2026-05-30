@extends('layouts.app')

@section('title', __('Services near you').' — '.config('medca.brand_name'))

@section('content')
    @include('public.partials.near-you-services', [
        'services' => $services,
        'pincode' => $pincode,
        'pinCodeRecord' => $pinCodeRecord,
        'locationRequired' => $locationRequired,
    ])
@endsection
