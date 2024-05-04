@extends('web.master')

    <!-- navbar ends here  -->
@section('title')
    {{ __('about-us') }}
@endsection
@section('content')
  <div class="main">
    <div class="breadcrumb">
      <div class="container">
        <div class="contentWrapper">
          <span class="title"> {{ __('about-us') }}</span>
          <span>
            <span class="home"><a href="{{url('/')}}">{{ __('home') }}</a></span>
            <span><i class="fa-solid fa-angles-right"></i></span>
            <span class="page">{{ __('about-us') }}</span>
          </span>
        </div>
      </div>
    </div>

    @if ($about)
        <section class="aboutUs commonMT">
        <div class="container">
            <div class="row aboutWrapper">
            <div class="col-sm-12 col-md-12 col-lg-6">
                <div class="aboutImgWrapper">
                <img src="{{isset($about->image) ? $about->image : url('assets/images/dummyImg.png')}}" alt="" />
                </div>
            </div>

            <div class="col-sm-12 col-md-12 col-lg-6">
                <div class="aboutContentWrapper">
                <span class="commonTag">  {{isset($about->tag) ? $about->tag : "About Us"}}</span>
                <span class="commonTitle">
                    {{isset($about->heading) ? $about->heading : "Cutting-Edge Education That Empowers"}}
                </span>
                <span class="commonDesc">
                    {{isset($about->content) ? $about->content : " Lorem ipsum dolor sit amet consectetur. Faucibus non mauris risus enim sed. Lectus fusce
                    elit duis dignissim aliquet nisl vitae. Eget sit nisi vulputate enim sem. Facilisi
                    tincidunt donec interdum in in eros quisque consectetur sit. Sagittis purus velit amet
                    risus egestas sed arcu nam. Pellentesque pharetra blandit fringilla volutpat tristique
                    sit. Sit euismod praesent volutpat eu et. Id egestas dictum cursus purus morbi semper
                    praesent quam."}}
                </span>
                </div>
            </div>
            </div>
        </div>
        </section>
    @endif

    <!-- aboutUs ends here  -->

    @if ($whoweare)
        <section class="whoWeAre commonMT">
        <div class="container">
            <div class="row whoWeAreContentWrapper">
            <div class="col-lg-6 contentDiv">
                <div class="flex_column_center">
                <span class="commonTag">  {{isset($whoweare->tag) ? $whoweare->tag : "Who We Are"}} </span>
                <span class="commonTitle">
                    {{isset($whoweare->heading) ? $whoweare->heading : "Empowering Minds, Shaping Futures"}}
                </span>

                <span class="commonDesc">
                    {{isset($whoweare->content) ? $whoweare->content : "Lorem ipsum dolor sit amet consectetur. Faucibus non mauris risus enim sed. Lectus fusce
                                    elit duis dignissim aliquet nisl vitae. Eget sit nisi vulputate enim sem. Facilisi
                                    tincidunt donec interdum in in eros quisque consectetur sit. Sagittis purus velit amet
                                    risus egestas sed arcu nam. Pellentesque pharetra blandit fringilla volutpat tristique
                                    sit. Sit euismod praesent volutpat eu et. Id egestas dictum cursus purus morbi semper
                                    praesent quam."}}
                </span>
                </div>

                <div class="row whoWeAreCardsWrapper">
                <div class="col-12 col-sm-6  col-md-6 col-lg-6">
                    <div class="card">
                        <div class="imgWrapper">
                            <img src="{{url('assets/images/student.png')}}" alt="">
                        </div>
                        <div class="detailWrapper">
                            <span>
                                {{$studentcount}}+
                            </span>
                            <span>
                                {{ __('student') }} {{ __('enrolled') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6  col-md-6 col-lg-6">
                    <div class="card">
                        <div class="imgWrapper">
                            <img src="{{url('assets/images/teacher.png')}}" alt="">
                        </div>
                        <div class="detailWrapper">
                            <span>
                                {{$teachercount}}+
                            </span>
                            <span>
                                {{ __('expert') }} {{ __('teachers') }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>
            </div>
            <div class="col-lg-6 whoweAreImgDiv">
                <div class="">
                    <img src="{{isset($whoweare->image) ? $whoweare->image : url('assets/images/who-we-are-img.png')}}" alt="">
                </div>
            </div>
            </div>
        </div>
        </section>
    @endif

    <!-- whoWeAre ends here  -->

    @if ($teacher)
        <div class="ourTeacher commonMT container">
        <div class="row">
            <div class="col-12">
            <div class="flex_column_center">
                <span class="commonTag">{{isset($teacher->tag)? $teacher->tag : "Our Expert Teacher"}}</span>
                <span class="commonTitle">
                {{isset($teacher->heading)? $teacher->heading : "More Than Just Teachers, They're Mentors"}}
                </span>

                <span class="commonDesc">
                {{isset($teacher->content)? $teacher->content : "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod
                tempor incididunt ut labore et dolore magna aliqua."}}
                </span>
            </div>
            </div>

            <div class="col-12">
                <div class="commonSlider">
                <div class="slider-container">
                    <div class="slider-content owl-carousel">
                        @if (isset($teachers))
                            @foreach ($teachers as $teacher)
                                <!-- Example slide -->
                                <div class="swiperDataWrapper">
                                    <div class="card">
                                    <div>
                                        <img src="{{$teacher->user->image ?? ''}}" alt="">
                                    </div>
                                    <div class="teacherDetails">
                                        <span class="name">{{$teacher->user->first_name .'  '. $teacher->user->last_name}}</span>
                                        <span class="subject">{{$teacher->qualification}}</span>
                                    </div>
                                    </div>
                                </div>
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
        </div>
    @endif

    <!-- ourTeacher ends here  -->
  </div>
@endsection
