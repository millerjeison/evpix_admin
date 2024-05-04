@extends('web.master')

<!-- navbar ends here  -->
@section('title')
    {{ __('eSchool') }}
@endsection
@section('content')

  <div class="main">
    <section class="heroSection">
        <div class="owl-carousel owl-theme hero-carousel">
          @if ($sliders->isEmpty())
              <div class="item">
                  <img src="{{ url('assets/images/heroImg1.png') }}" alt="" class="swiperImage" />
              </div>
              <div class="item">
                  <img src="{{ url('assets/images/heroImg2.png') }}" alt="" class="swiperImage" />
              </div>
              <div class="item">
                  <img src="{{ url('assets/images/heroImg3.png') }}" alt="" class="swiperImage" />
              </div>
              <div class="item">
                  <img src="{{ url('assets/images/heroImg4.png') }}" alt="" class="swiperImage" />
              </div>
          @else
              @foreach ($sliders as $slider)
                  <div class="item">
                      <img src="{{ url($slider->image) }}" alt="" class="swiperImage">
                  </div>
              @endforeach
          @endif
      </div>
    </section>

    <!-- heroSection ends here  -->
    @if ($about)
        <section class="aboutUs commonMT">
            <div class="container">
            <div class="row aboutWrapper">
                <div class="col-sm-12 col-md-12 col-lg-6">
                    <div class="aboutImgWrapper">
                        <img src="{{ isset($about->image) ? $about->image : url('assets/images/dummyImg.png') }}"
                            alt="">
                    </div>
                </div>

                <div class="col-sm-12 col-md-12 col-lg-6">
                    <div class="aboutContentWrapper">
                        <span class="commonTag">
                            {{ isset($about->tag) ? $about->tag : '' }}
                        </span>
                        <span class="commonTitle">
                            {{ isset($about->heading) ? $about->heading : '' }}
                        </span>
                        <span class="commonDesc">
                            {{ isset($about->content)? $about->content : '' }}

                        </span>
                        <button class="commonBtn">
                            <a href="{{ route('about.us') }}">{{ __('read_more') }}</a> <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

            </div>
            </div>
        </section>
      <!-- aboutUs ends here  -->
    @endif

    @if ($program)
        <section class="programs commonMT">
            <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="programsCardWrapper">
                    <div class="row">
                        <div class="col-12">
                            <div class="flex_column_center">
                                <span class="commonTag">
                                    {{ isset($program->tag) ? $program->tag : 'Educational Programs' }}
                                </span>
                                <span class="commonTitle">
                                    {{ isset($program->heading) ? $program->heading : 'Educational Programs for every Stage' }}
                                </span>

                                <span class="commonDesc">
                                    {{ isset($program->content)
                                        ? $program->content
                                        : "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod
                                                                    tempor incididunt ut labore et dolore magna aliqua." }}
                                </span>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="programsCardWrapper">
                                <div class="row">
                                    @if (isset($eprograms))
                                        @foreach ($eprograms as $item)
                                            <div class="col-sm-6 col-md-6 col-lg-3">
                                                <div class="card">
                                                    <img src="{{ $item->image }}" class="card-img-top" alt="...">
                                                    <div class="cardTitle"><span>{{ $item->title }}</span></div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="col-sm-6 col-md-6 col-lg-3">
                                            <div class="card">
                                                <img src="{{ url('assets/images/programImg.png') }}" class="card-img-top"
                                                    alt="...">
                                                <div class="cardTitle"><span>Pre-primary School</span></div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-6 col-lg-3">
                                            <div class="card">
                                                <img src="{{ url('assets/images/programImg.png') }}" class="card-img-top"
                                                    alt="...">
                                                <div class="cardTitle"><span>Primary School</span></div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-6 col-lg-3">
                                            <div class="card">
                                                <img src="{{ url('assets/images/programImg.png') }}" class="card-img-top"
                                                    alt="...">
                                                <div class="cardTitle"><span>Secondary School</span></div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-6 col-lg-3">
                                            <div class="card">
                                                <img src="{{ url('assets/images/programImg.png') }}" class="card-img-top"
                                                    alt="...">
                                                <div class="cardTitle"><span>Higher Secondary School</span></div>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="sideImgs">
                                        <img src="{{ url('assets/images/notes.svg') }}" class="notesImg" alt="notesImg">
                                        <img src="{{ url('assets/images/bulbImg.svg') }}" class="bulbImg" alt="bulbImg">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
            </div>
        </section>
      <!-- programs ends here  -->
    @endif

    @if ($event)
        <section class="events commonMT container">
            <div class="row">
                <div class="col-12">
                    <div class="flex_column_center">
                        <span class="commonTag">
                            {{ isset($event->tag) ? $event->tag : 'Our Events and News' }}
                        </span>
                        <span class="commonTitle">
                            {{ isset($event->heading)? $event->heading: "Don't Miss the Biggest Events and News of the Year!" }}
                        </span>

                        <span class="commonDesc">
                            {{ isset($event->content) ? $event->content : "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua." }}
                        </span>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="eventsCardWrapper">
                        <div class="row">
                            @if (isset($news))
                                @foreach ($news as $item)
                                    <div class="col-md-6 col-lg-6">
                                        <div class="card" data-id="{{ $item->id }}">
                                            <div class="eventDescWrapper">
                                                <div class="eventTitle">{{ $item->title }}</div>
                                                <hr>
                                                <div class="eventDate">
                                                    @if ($item->date)
                                                        <span class="month">{{ date('d M, Y', strtotime($item->date)) }}</span>
                                                    @else
                                                        @if ($item->end_date)
                                                            <span class="month">{{ date('d M, Y', strtotime($item->start_date)) . '  To  ' .  date('d M, Y', strtotime($item->end_date)) }}</span>
                                                        @else
                                                            <span class="month">{{ date('d M, Y', strtotime($item->start_date)) }}</span>
                                                        @endif
                                                    @endif
                                                </div>
                                                <span class="eventType" style="display: none">{{$item->type}}</span>
                                                <span class="eventDesc">{{ $item->description }}</span>
                                                <span class="eventReadMoreBtn">{{ __('view_details') }}</span>
                                                <span class="image" style="display: none">{{ $item->image}}</span>
                                                <span class="eventDetails" style="display: none"><{{$item->multipleEvent}}</span>

                                            </div>
                                        </div>
                                    </div>

                                @endforeach
                            @endif
                        </div>

                        <!-- Add a modal container -->
                        <div id="eventModal" class="modal">
                            <div class="modal-content modal-dialog-scrollable">
                                <div class="modal-header">
                                    <h4 id="fullEventTitle"></h4>
                                    <span class="close">&times;</span>
                                </div>
                                <div class="event-modal-body">
                                    <span class="eventDate" id="fullEventDate"></span>
                                    <img class="eventImage" style="display: none" id="eventImage" src="" alt="image" /><br><br><br>
                                    <span class="fullEventDescription" id="fullEventDescription"></span>


                                    <div class="eventDetails" style="display: none">
                                        <span class="eventDay"></span>
                                        <div class="eventDescription">
                                            <div class="d-flex">
                                                <div class="eventTime">
                                                    <span class="time"></span>
                                                </div>
                                                <div class="multiEvent">
                                                    <div class="eventTitle">
                                                        <h5 class="subtitle"></h5>
                                                        <span class="subdescription"></span>
                                                    </div>
                                                    <div>
                                                        <i class="fa fa-calendar menu-icon"></i>
                                                        <span class="date"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="multiEventDetails"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- events ends here  -->
    @endif

    @if ($photo)
        <section class="ourPhotos commonMT">
            <div class="container">
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
                    <div class="commonSlider">
                        <div class="viewAllBtn">
                            <button class="commonBtn">
                                <a href="{{ route('photo') }}">
                                {{ __('view_all') }}
                                    <i class="fa-solid fa-circle-chevron-right"></i>
                                </a>
                            </button>
                        </div>
                        <div class="slider-content owl-carousel">
                            @if (isset($images))
                                @foreach ($images as $image)
                                    <!-- Example slide -->
                                    <a href="{{route('photo.gallery',$image->id)}}">
                                        <div class="swiperDataWrapper">
                                            <div class="img_div">
                                                <img src="{{ $image->thumbnail }}" alt="">
                                            </div>
                                            <div class="festival">
                                                <span>{{ $image->name }}</span>
                                                <span><i class="fa-solid fa-arrow-right"></i></span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            @endif

                            <!-- Add more swiperDataWrapper elements here -->
                        </div>
                        <!-- Navigation buttons -->
                        <div class="navigationBtns">
                            <button class="prev commonBtn">
                                <i class="fa-solid fa-arrow-left"></i>
                            </button>
                            <button class="next commonBtn">
                                <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </section>
    @endif

    <!-- ourPhotos ends here  -->

    @if ($video)
        <section class="ourPhotos ourVideos commonMT">
            <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="flex_column_center">
                        <span class="commonTag">
                            {{ isset($video->tag) ? $video->tag : 'Our Videos' }}
                        </span>
                        <span class="commonTitle">
                            {{ isset($video->heading) ? $video->heading : 'Rewind, Replay, Rejoice! Dive into Our Video Vault' }}
                        </span>

                        <span class="commonDesc">
                            {{ isset($video->content)? $video->content : "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua." }}
                        </span>
                    </div>
                </div>
                <div class="col-12">
                    <div class="commonSlider">
                        <div class="viewAllBtn">
                            <button class="commonBtn">
                                <a href="{{ route('video') }}">
                                    {{ __('view_all') }}
                                    <i class="fa-solid fa-circle-chevron-right"></i>
                                </a>
                            </button>
                        </div>

                    <div class="slider-content owl-carousel">
                        @if (isset($videos))
                            @foreach ($videos as $index => $item)
                                <div class="swiperDataWrapper">
                                    <div class="img_div">
                                        <img src="{{$item->embeded_url['thumbnailUrl'] }}" data-embedUrl="{{$item->embeded_url['embedUrl'] }}"  class="openVideo" alt="Thumbnail">
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                            <!-- Navigation buttons -->
                        <div class="navigationBtns">
                            <button class="prev commonBtn">
                                <i class="fa-solid fa-arrow-left"></i>
                            </button>
                            <button class="next commonBtn">
                                <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Modal -->
                <div id="videoModal" class="modal">
                    <div class="modal-content">
                        <div class="iframe_div">
                            <iframe id="videoIframe" class="video_player" src="" frameborder="0" allowfullscreen></iframe>
                        </div>
                    </div>
                </div>

            </div>
            </div>
        </section>
        <!-- ourVideos ends here  -->
    @endif


    @if ($faq)
        <section class="faqs commonMT">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="flex_column_center">
                            <span class="commonTag">
                                {{ isset($faq->tag) ? $faq->tag : "FAQ'S" }}
                            </span>
                            <span class="commonTitle">
                                {{ isset($faq->heading)? $faq->heading: "Got Questions? We've Got Answers! Dive into Our FAQs" }}
                            </span>
                            <span class="commonDesc">
                                {{ isset($faq->content)? $faq->content: "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua." }}
                            </span>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="accordion" id="accordionExample">
                            @if (isset($faqs))
                                @foreach ($faqs as $item)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingFour">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapse-{{ $item->id }}"
                                                aria-expanded="false" aria-controls="collapse-{{ $item->id }}">
                                                <span>{{ $item->id }}. {{ $item->question }}</span>
                                            </button>
                                        </h2>
                                        <div id="collapse-{{ $item->id }}" class="accordion-collapse collapse"
                                            aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                                            <div class="accordion-body">
                                                <span>{{ $item->answer }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- faqs ends here  -->
    @endif


    @if ($app)
        <section class="ourApp commonMT container">
            <div class="row">
            <div class="col-12">
                    <div class="flex_column_center">
                    <span class="commonTag">
                        {{ isset($app->tag) ? $app->tag : 'Download Our School Apps!' }}
                    </span>
                    <span class="commonTitle">
                        {{ isset($app->heading) ? $app->heading : 'Empower Everyone: Teachers, Students, Parents - Get the App Now!' }}
                    </span>

                    <span class="commonDesc">
                        {{ isset($app->content)
                            ? $app->content
                            : "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua." }}
                    </span>
                    </div>
                </div>
                <div class="col-12">
                    <div class="row wrapper">
                        <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                            <div class="card">
                            <span>
                                <img src="{{ url('assets/images/androidApp.svg') }}" class="card-img-top"
                                    alt="...">
                            </span>
                            <span class="appName"> {{ __('student_parent_app') }}</span>
                            <span class="demoBtn"><a
                                    href="{{ isset($settings['app_link']) ? $settings['app_link'] : '' }}"
                                    style="color: #ffffff !important">{{ __('android_demo') }}</a>
                                <i class="fa-solid fa-arrow-right"></i>
                            </span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                            <div class="card">
                                <span>
                                    <img src="{{ url('assets/images/iosApp.svg') }}" class="card-img-top"
                                        alt="...">
                                </span>
                                <span class="appName">{{ __('student_parent_app') }}</span>
                                <span class="demoBtn"><a href="{{ isset($settings['ios_app_link']) ? $settings['ios_app_link'] : '' }}"
                                        style="color: #ffffff !important">
                                        {{ __('ios_demo') }}</a>
                                    <i class="fa-solid fa-arrow-right"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                            <div class="card">
                                <span>
                                    <img src="{{ url('assets/images/androidApp.svg') }}" class="card-img-top"
                                        alt="...">
                                </span>
                                <span class="appName">{{ __('teacher_app') }}</span>
                                <span class="demoBtn"><a href="{{ isset($settings['teacher_app_link']) ? $settings['teacher_app_link'] : '' }}"
                                        style="color: #ffffff !important">
                                        {{ __('android_demo') }}</a>
                                    <i class="fa-solid fa-arrow-right"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                            <div class="card">
                                <span>
                                    <img src="{{ url('assets/images/iosApp.svg') }}" class="card-img-top"
                                        alt="...">
                                </span>
                                <span class="appName">{{ __('teacher_app') }}</span>
                                <span class="demoBtn"><a href="{{ isset($settings['teacher_ios_app_link']) ? $settings['teacher_ios_app_link'] : '' }}"
                                        style="color: #ffffff !important">
                                        {{ __('ios_demo') }}</a>
                                    <i class="fa-solid fa-arrow-right"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- ourApp ends here  -->
    @endif

  </div>
@endsection
@section('script')

    <script type="text/javascript">
           // Function to format time (e.g., from "09:00:00" to "09:00 AM")
           function formatTime(timeString) {
                const [hours, minutes] = timeString.split(':');
                const formattedHours = parseInt(hours) % 12 || 12;
                const period = parseInt(hours) < 12 ? 'AM' : 'PM';
                return `${formattedHours}:${minutes} ${period}`;
            }

            function formatDate(dateString) {
                const date = new Date(dateString);
                const day = date.getDate().toString().padStart(2, '0');
                const month = (date.getMonth() + 1).toString().padStart(2, '0');
                const year = date.getFullYear();
                return `${day}-${month}-${year}`;
            }

        document.addEventListener("DOMContentLoaded", function () {
            const eventDescElements = document.querySelectorAll(".eventDesc");

            eventDescElements.forEach(function (eventDescElement) {
                const originalText = eventDescElement.textContent;
                const maxLength = 100;
                const readMoreBtn = eventDescElement.nextElementSibling; // Assuming the button is a sibling
                if (originalText.length > maxLength) {
                    const truncatedText = originalText.substring(0, maxLength) + "...";
                    eventDescElement.textContent = truncatedText;

                    readMoreBtn.style.display = "inline"; // Show the button
                }
                // Add event listener to "Read More" button
                readMoreBtn.addEventListener("click", function () {
                    // Get the title from eventDescWrapper

                    const title = eventDescElement.parentElement.querySelector(".eventTitle").textContent;
                    // Get the date and month from eventDateWrapper
                    const month = eventDescElement.parentElement.querySelector(".month").textContent;
                    const image = eventDescElement.parentElement.querySelector(".image").textContent;

                    const EventDetails = eventDescElement.parentElement.querySelector(".eventDetails").textContent;

                    const details = EventDetails.replace(/</g, '');


                       // Display the full event information in the modal
                    document.getElementById("fullEventTitle").textContent = title;
                    document.getElementById("fullEventDate").textContent = month;
                    document.getElementById("fullEventDescription").textContent = originalText;

                    if (image) {
                        document.getElementById("eventImage").src = image;
                        document.getElementById("eventImage").style.display = "block";
                    } else {
                        document.getElementById("eventImage").style.display = "none";
                    }

                    if(details && details.length > 0)
                    {
                        const events = JSON.parse(details);
                        const modalContainer = document.querySelector('.eventDetails');
                        let currentDay = 0; // Initialize the current day counter
                        let prevDate = null;


                        function cloneAndPopulateEventDescription() {
                            let eventDescription = $('.eventDetails:last').clone().show();

                            return  eventDescription;
                        }

                        $.each(events, function (key, event) {

                            const startTime = formatTime(event.start_time);
                            const endTime = formatTime(event.end_time);
                            const eventDescription = cloneAndPopulateEventDescription();

                            $('#multiEventDetails').append(eventDescription);

                            if (event.date) {
                                if (event.date !== prevDate) {
                                    currentDay++; // Increment currentDay if the date is different
                                    prevDate = event.date; // Update prevDate
                                    eventDescription.find('.eventDay').text('Day ' + currentDay).show(); // Show day element
                                } else {
                                    eventDescription.find('.eventDay').hide(); // Hide day element if date is same
                                }
                                    eventDescription.find('.time').text(startTime + ' To ' + endTime);
                                    eventDescription.find('.subtitle').text(event.title);
                                    eventDescription.find('.subdescription').text(event.description);
                                    eventDescription.find('.date').text(formatDate(event.date));
                            }
                        });
                    }


                    // Show the modal
                    const modal = document.getElementById("eventModal");
                    modal.style.display = "block";
                    // Disable scrolling
                    document.body.classList.add("modal-open");

                });
            });
            // Close the modal when the close button is clicked
            document.querySelector(".close").addEventListener("click", function () {
                const modal = document.getElementById("eventModal");
                modal.style.display = "none";


                const eventDetailsContainer = document.getElementById("multiEventDetails");
                eventDetailsContainer.innerHTML = "";
                document.body.classList.remove("modal-open");  // Enable scrolling

            });
            // Close the modal when clicking outside of it
            window.addEventListener("click", function (event) {
                if (event.target == document.getElementById("eventModal")) {
                    const modal = document.getElementById("eventModal");
                    modal.style.display = "none";

                    const eventDetailsContainer = document.getElementById("multiEventDetails");
                    eventDetailsContainer.innerHTML = "";
                    document.body.classList.remove("modal-open");   // Enable scrolling

                }

            });
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


