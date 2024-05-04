<footer class="commonMT">

    <div class="container">
      <div class="row">
        <div class="col-sm-6 col-md-6 col-lg-3">
          <div class="companyInfoWrapper">
            <div>
              <a href="index.html">
                <a href="{{url('/')}}">
                    <img src="{{ env('LOGO1') ? url(Storage::url(env('LOGO1'))) : url('assets/logo.svg') }}" height="50px" width="150px" alt="logo">
                </a>
              </a>
            </div>
            <div>
              <span class="commonDesc">
                {{isset($settings['school_address']) ? $settings['school_address'] : ' Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor
                incididunt ut labore et dolore magna.'}}
              </span>
            </div>

            <div class="socialIcons">
              <span>
                <a href="{{isset($settings['facebook']) ? $settings['facebook'] : ''}}" target="_blank">
                  <i class="fa-brands fa-square-facebook"></i>
                </a>
              </span>
              <span>
                <a href="{{isset($settings['instagram']) ? $settings['instagram'] : ''}}" target="_blank">
                  <i class="fa-brands fa-square-instagram"></i>
                </a>
              </span>
              <span>
                <a href="{{isset($settings['linkedin']) ? $settings['linkedin'] : ''}}" target="_blank">
                  <i class="fa-brands fa-linkedin"></i>
                </a>
              </span>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-md-6 col-lg-3">
          <div class="linksWrapper usefulLinksDiv">
            <span class="title">{{ __('useful_links') }}</span>
                <span><a href="{{url('/')}}">{{ __('home') }}</a></span>
                @if ($about || $whoweare || $teacher)<span><a href="{{route('about.us')}}">{{ __('about-us') }}</a></span> @endif
                @if ($photo)<span><a href="{{route('photo')}}">{{ __('photos') }}</a></span> @endif
                @if ($video)<span><a href="{{route('video')}}">{{ __('videos') }}</a></span> @endif
                @if ($question)<span><a href="{{route('contact.us')}}"> {{ __('contact_us') }}</a></span> @endif
          </div>
        </div>

        <div class="col-sm-6 col-md-6 col-lg-3">
            <div class="linksWrapper">
                <span class="title">{{ __('quick_links') }}</span>
                <span>
                    <a href="{{url('login')}}">
                        {{ __('admin_login')}}
                    </a>
                </span>
                <span>
                    <a href="{{url('terms-conditions')}}">
                        {{ __('terms_condition')}}
                    </a>
                </span>
                <span>
                    <a href="{{url('privacy-policy')}}">
                        {{ __('privacy_policy')}}
                    </a>
                </span>
            </div>
        </div>

        <div class="col-sm-6 col-md-6 col-lg-3">
          <div class="linksWrapper">
            <span class="title">{{ __('contact_info') }}</span>
                <span class="iconsWrapper">
                    <span>
                        <i class="fa-solid fa-phone-volume"></i>
                    </span>
                    <span>
                        <a href="tel:{{$settings['school_phone']}}">{{isset($settings['school_phone']) ? $settings['school_phone'] : '( +91 ) 12345 67890'}}</a>
                    </span>
                </span>
            <span class="iconsWrapper">
                <span>
                    <i class="fa-solid fa-envelope-circle-check"></i>
                </span>
                <span>
                        <a href="mailto:{{$settings['school_email']}}">{{isset($settings['school_email'])? $settings['school_email'] :  'Schoolinfous@gmail.com'}}</a>
                </span>
            </span>
            <span class="iconsWrapper">
                <span>
                    <i class="fa-solid fa-location-dot location"></i>
                </span>
                <span>
                    {{isset($settings['school_address']) ? $settings['school_address'] : ' 4517 Washington Ave. Manchester, Kentucky
                    39495.'}}
                </span>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="copyRightText">
      <span>© 2024 eSchool. All rights reserved.</span>
    </div>
  </footer>
  <script>
    let currentSlide = 0;
    const swiperDataWrappers = document.querySelector('.slider-content .row');
    const swiperDataWrapperWidth = document.querySelector('.swiperDataWrapper').offsetWidth;

    function showSlide(n) {
        currentSlide = (n + swiperDataWrappers.children.length) % swiperDataWrappers.children.length;

        const transformValue = -currentSlide * swiperDataWrapperWidth + 'px';
        swiperDataWrappers.style.transform = 'translateX(' + transformValue + ')';
    }

    function changeSlide(n) {
        showSlide(currentSlide + n);
    }

    // Initial display
    showSlide(0);

    // Infinite loop by resetting the position after transition
    swiperDataWrappers.addEventListener('transitionend', () => {
        if (currentSlide === 0) {
            swiperDataWrappers.style.transition = 'none';
            currentSlide = swiperDataWrappers.children.length / 1;
            showSlide(currentSlide);
            setTimeout(() => {
                swiperDataWrappers.style.transition = 'transform 0.5s ease-in-out';
            });
        }
    });

</script>

<script>
    let currentVideoSlide = 0;
    const swiperDataVideoWrappers = document.querySelector('.slider-video .row');
    const swiperDataVideoWrapperWidth = document.querySelector('.swiperVideoDataWrapper').offsetWidth;

    function showVideoSlide(n) {
        currentVideoSlide = (n + swiperDataVideoWrappers.children.length) % swiperDataVideoWrappers.children.length;

        const transformValue = -currentVideoSlide * swiperDataVideoWrapperWidth + 'px';
        swiperDataVideoWrappers.style.transform = 'translateX(' + transformValue + ')';
    }

    function changeVideoSlide(n) {
        showVideoSlide(currentVideoSlide + n);
    }

    // Initial display
    showVideoSlide(0);

    // Infinite loop by resetting the position after transition
    swiperDataVideoWrappers.addEventListener('transitionend', () => {

        console.log("Hello");
        if (currentVideoSlide === 0) {
            swiperDataVideoWrappers.style.transition = 'none';
            currentVideoSlide = swiperDataVideoWrappers.children.length / 1;
            showVideoSlide(currentVideoSlide);
            setTimeout(() => {
                swiperDataVideoWrappers.style.transition = 'transform 0.5s ease-in-out';
            });
        }
    });

</script>

<!-- bootstrap  -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
    crossorigin="anonymous"></script>

<!-- fontawesome icons   -->
<script src="https://kit.fontawesome.com/1d2a297b20.js" crossorigin="anonymous"></script>

<!-- swiper  -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-element-bundle.min.js"></script>

<!-- swiper  -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="{{ asset('/assets/jquery-toast-plugin/jquery.toast.min.js') }}"></script>
<script src="{{ asset('/assets/js/custom/function.js') }}"></script>
<script src="{{ asset('/assets/js/jquery.validate.min.js') }}"></script>
<script src="{{ asset('/assets/js/script.js') }}"></script>
<script src="{{ asset('/assets/js/ekko-lightbox.min.js') }}"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<script>
    $(document).ready(function () {
      // Initialize each carousel separately
      $(".slider-content.owl-carousel").each(function () {
        var owl = $(this).owlCarousel({
          loop: false,
          margin: 20,
          nav: false,
          responsive: {
            0: {
              items: 1,
            },
            600: {
              items: 3,
            },
            1000: {
              items: 5,
            },
          },
        });

        // Custom navigation buttons for this specific carousel
        $(this)
          .closest(".commonSlider")
          .find(".prev")
          .click(function () {
            owl.trigger("prev.owl.carousel");
          });

        $(this)
          .closest(".commonSlider")
          .find(".next")
          .click(function () {
            owl.trigger("next.owl.carousel");
          });
      });
    });

    $(document).ready(function () {
      $(".hero-carousel").owlCarousel({
        items: 1,
        loop: true,
        autoplay: true,
        autoplayTimeout: 2000, // Set autoplay interval in milliseconds
        autoplayHoverPause: true, // Pause autoplay when mouse hovers over the carousel
        nav: true,
        navText: [
          "<i class='fa-solid fa-arrow-left'></i>",
          "<i class='fa-solid fa-arrow-right'></i>",
        ],
      });
    });
</script>

@php
    $theme_color = getSettings('theme_color');
    $secondary_color = getSettings('secondary_color');

    // echo json_encode($theme_color);
    $theme_color = $theme_color['theme_color'];
    $secondary_color =   $secondary_color['secondary_color'];
@endphp
<style>
    :root {
        --primary-color: <?=$theme_color ?>;
        /* --primary-hover-color:<?=$secondary_color ?>; */
        --secondary-color1:<?=$secondary_color ?>;
    }
</style>
