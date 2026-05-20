@extends('layouts.app')

@php
    use Illuminate\Support\Str;

    $pageTitle = $vacancy->seo_title ?: $vacancy->title;
    $metaDescription = $vacancy->seo_description ?: Str::limit(strip_tags((string) ($vacancy->summary ?: $vacancy->description)), 160);
@endphp

@section('title')
    {{ $pageTitle }} — {{ config('app.name') }}
@endsection

@push('meta')
    <meta name="description" content="{{ $metaDescription }}">
    @if ($vacancy->focus_keywords)
        <meta name="keywords" content="{{ $vacancy->focus_keywords }}">
    @endif
    <link rel="canonical" href="{{ url()->current() }}">
@endpush

@push('schema')
    @if ($schema !== [])
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif
@endpush

@section('content')
    @include('careers.partials.job-detail-layout', ['vacancy' => $vacancy])
@endsection
