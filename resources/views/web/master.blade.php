<!DOCTYPE html>
@php
    $lang = Session::get('language');
@endphp
@if($lang)
    @if ($lang->is_rtl)
        <html lang="en" dir="rtl">
    @else
        <html lang="en">
    @endif
@else
    <html lang="en">
@endif
@php
    $about = DB::table('web_settings')->where('name', 'about_us')->where('status', 1)->first();
    $whoweare = DB::table('web_settings')->where('name', 'who_we_are')->where('status', 1)->first();
    $teacher = DB::table('web_settings')->where('name', 'teacher')->where('status', 1)->first();
    $photo = DB::table('web_settings')->where('name', 'photos')->where('status', 1)->first();
    $video = DB::table('web_settings')->where('name', 'videos')->where('status', 1)->first();
    $question = DB::table('web_settings')->where('name', 'question')->where('status', 1)->first();
@endphp
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title') || {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ url(Storage::url(env('FAVICON'))) }}"/>
    @yield('css')

</head>
<body class="sidebar-fixed">
<div class="container-scroller">

    {{-- header --}}
    @include('web.header')

    <div class="page-body-wrapper">

        <div class="main-panel">

            @yield('content')



        </div>

    </div>

</div>

  {{-- footer --}}
  @include('web.footer')

@yield('js')

@yield('script')

</body>

</html>
