@extends('web.master')

    <!-- navbar ends here  -->
@section('title')
    {{ __('photos') }}
@endsection
@section('content')
    <div class="main">
        <div class="breadcrumb">
            <div class="container">
                <div class="contentWrapper">
                    <span class="title">
                        {{ __('photos') }}
                    </span>
                    <span>
                        <span class="home"><a href="{{url('/')}}" style="text-decoration: none">{{ __('home') }}</a></span>
                        <span><i class="fa-solid fa-angles-right"></i></span>
                        <span class="home"><a href="#">  {{ __('gallery') }}</a></span>
                        <span><i class="fa-solid fa-angles-right"></i></span>
                        <span class="page"> {{ __('photos') }}</span>
                    </span>
                </div>
            </div>
        </div>

        @if ($photo)
            <section class="photosGallery commonMT container">
                <div class="row">
                    <div class="col-12">
                        <div class="flex_column_center">
                            <span class="commonTag">
                                {{isset($photo->tag) ? $photo->tag :"Our Photos"}}
                            </span>
                            <span class="commonTitle">
                                {{isset($photo->heading) ? $photo->heading :" Capturing Memories, Building Dreams"}}
                            </span>

                            <span class="commonDesc">
                                {{isset($photo->content) ? $photo->content :"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod
                                tempor incididunt ut labore et dolore magna aliqua."}}
                            </span>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="row photosCardWrapper">
                            @if (isset($images))
                                @foreach ($images as $image)
                                    <div class="col-sm-6 col-md-6 col-lg-3">
                                        <a href="{{route('photo.gallery',$image->id)}}">
                                            <div class="card">
                                                <div>
                                                    <img src="{{$image->thumbnail}}" loading="lazy" alt="">
                                                </div>
                                                <div class="festival">
                                                    <span>{{$image->name}}</span>
                                                    <span><i class="fa-solid fa-arrow-right"></i></span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        @endif

    </div>
@endsection
