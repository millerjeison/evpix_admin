@extends('web.master')

    <!-- navbar ends here  -->
@section('title')
    {{ __('photos_details') }}
@endsection
@section('content')
    <div class="main">
        <div class="breadcrumb">
            <div class="container">
                <div class="contentWrapper">
                    <span class="title">
                        {{ __('photos_details') }}
                    </span>
                    <span>
                        <span class="home"><a href="{{url('/')}}">{{ __('home') }}</a></span>
                        <span><i class="fa-solid fa-angles-right"></i></span>
                        <span class="home"><a href="#">{{ __('gallery') }}</a></span>
                        <span><i class="fa-solid fa-angles-right"></i></span>
                        <span class="home"><a href="{{ route('photo') }}">{{ __('photos') }}</a></span>
                        <span><i class="fa-solid fa-angles-right"></i></span>
                        <span class="page">{{ __('photos_details') }}</span>
                    </span>
                </div>
            </div>
        </div>

        <section class="photosGallery commonMT container">
            <div class="row">
                <div class="col-12">
                    <div id="imageContainer" class="row photosCardWrapper">
                        @if (isset($images))
                            @foreach ($images->take(8) as $index => $item)
                            <div class="col-sm-6 col-md-6 col-lg-3">
                                <div class="card">
                                    <img src="{{$item->file_url}}" loading="lazy" data-target="#imageModal" data-slide-to="{{$index}}" onclick="openModal({{$index}})" data-toggle="modal" alt="">
                                </div>
                            </div>
                            @endforeach
                        @endif
                        <div class="col-12">
                            <div class="loadmoreBtnWrapper">
                                <button id="loadMoreBtn" class="commonBtn">Load More</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
             <!-- Medium Modal with Arrow Buttons -->
            <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div id="slideshow" class="carousel slide" data-ride="carousel">
                            <div class="carousel-inner">
                                @foreach ($images as $index => $item)
                                    <div class="carousel-item {{$index == 0 ? 'active' : ''}}">
                                        <img class="d-block w-100" src="{{$item->file_url}}" alt="Slide {{$index}}">
                                    </div>
                                @endforeach
                            </div>
                            <a class="carousel-control-prev" href="#slideshow" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon bg-color" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#slideshow" role="button" data-slide="next">
                            <span class="carousel-control-next-icon bg-color" aria-hidden="true"></span>
                            <span class="sr-only">Next</span>
                            </a>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@section('script')
    <script type="text/javascript">
        function openModal(index) {
        $('#imageModal').modal('show');
        $('#slideshow .carousel-item').removeClass('active');
        $('#slideshow .carousel-item').eq(index).addClass('active');
        }
    </script>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
        var loadMoreBtn = document.getElementById('loadMoreBtn');

        loadMoreBtn.addEventListener('click', function() {
            var imageContainer = document.getElementById('imageContainer');
            var images = {!! json_encode($images->slice(8)->values()->all()) !!}; // Convert object to array

            images.forEach(function(item, index) {
                var card = document.createElement('div');
                card.className = 'col-sm-6 col-md-6 col-lg-3';
                card.innerHTML = `
                    <div class="card">
                        <img src="${item.file_url}" loading="lazy" data-target="#imageModal" data-slide-to="${index + 8}" onclick="openModal(${index + 8})" data-toggle="modal" alt="">
                    </div>
                `;
                imageContainer.appendChild(card);
            });
            loadMoreBtn.style.display = 'none';

        });
        if ({!! $images->count() !!} <= 8) {
            loadMoreBtn.style.display = 'none';
        }
    });
    </script>
@endsection
