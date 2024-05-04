@extends('web.master')

    <!-- navbar ends here  -->
@section('title')
    {{ __('videos') }}
@endsection
@section('content')
    <div class="main">

        <div class="breadcrumb">
            <div class="container">
                <div class="contentWrapper">
                    <span class="title">
                        {{ __('videos') }}
                    </span>
                    <span>
                        <span class="home"><a href="{{url('/')}}">{{ __('home') }}</a></span>
                        <span><i class="fa-solid fa-angles-right"></i></span>
                        <span class="home"><a href="#">{{ __('gallery') }}</a></span>
                        <span><i class="fa-solid fa-angles-right"></i></span>
                        <span class="page">{{ __('videos') }}</span>
                    </span>
                </div>
            </div>
        </div>
        @if ($video)
            <section class="photosGallery commonMT container">
                <div class="row">
                    <div class="col-12">
                        <div class="flex_column_center">
                            <span class="commonTag">
                                {{isset($video->tag) ? $video->tag :"Our Videos"}}
                            </span>
                            <span class="commonTitle">
                                {{isset($video->heading) ? $video->heading :"Rewind, Replay, Rejoice! Dive into Our Video Vault"}}
                            </span>

                            <span class="commonDesc">
                                {{isset($video->content) ? $video->content :"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod
                                    tempor incididunt ut labore et dolore magna aliqua."}}
                            </span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div id="videoContainer" class="row photosCardWrapper">
                            @if (isset($videos))
                                @foreach ($videos->take(8) as $item )
                                    <div class="col-sm-6 col-md-6 col-lg-3">
                                        <div class="card">
                                            <div class="img_div">
                                                <img src="{{$item->embeded_url['thumbnailUrl'] }}" data-embedUrl="{{$item->embeded_url['embedUrl'] }}"  class="openVideo" alt="Thumbnail">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <div class="col-12">
                            <div class="loadmoreBtnWrapper">
                                <button id="loadMoreVideosBtn" class="commonBtn">Load More</button>
                            </div>
                        </div>
                    </div>
                    <div id="videoModal" class="modal">
                        <div class="modal-content">
                            <div class="iframe_div">
                                <iframe id="videoIframe" class="video_player" src="" frameborder="0" allowfullscreen></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

    </div>
@endsection
@section('script')
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            var loadMoreVideosBtn = document.getElementById('loadMoreVideosBtn');

            loadMoreVideosBtn.addEventListener('click', function() {
                var videoContainer = document.getElementById('videoContainer');
                if (!videoContainer) {
                    console.error('Video container not found');
                    return;
                }

                if($videos)
                {
                    var videos = {!! json_encode($videos->slice(8)->values()->all()) !!}; // Remaining videos
                }
                else
                {
                     // Handle the case when $videos is null
                     var videos = []; // or any default value you prefer
                }

                videos.forEach(function(item) {
                    var card = document.createElement('div');
                    card.className = 'col-sm-6 col-md-6 col-lg-3';
                    card.innerHTML = `
                        <div class="card">
                            <div class="img_div">
                                <iframe src="${item.embeded_url}" loading="lazy" frameborder="0" allowfullscreen></iframe>
                            </div>
                        </div>
                    `;
                    videoContainer.appendChild(card);
                });

                // Hide Load More Videos button when all videos are loaded
                loadMoreVideosBtn.style.display = 'none';
            });
            if ({!! $videos->count() !!} <= 8) {
                loadMoreVideosBtn.style.display = 'none';
            }
        });

    </script>
     <script type="text/javascript">
        $(document).ready(function () {
            $('.openVideo').click(function () {
                // Get the modal

                var modal = document.getElementById('videoModal');

                var embedUrl = this.getAttribute('data-embedUrl');

                videoIframe.src = embedUrl;
                // Display the modal when the image is clicked
                modal.style.display = "block";

                // Close the modal when clicking outside of it
                window.addEventListener("click", function (event) {
                    if (event.target == document.getElementById("videoModal")) {
                        const modal = document.getElementById("videoModal");
                        modal.style.display = "none";
                        // Enable scrolling
                        document.body.classList.remove("modal-open");
                        var videoElement = document.getElementById('videoIframe');
                        videoElement.src = "";
                    }
                });
            });
        });

    </script>
@endsection
