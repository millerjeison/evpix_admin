@extends('web.master')

    <!-- navbar ends here  -->
@section('title')
    {{ __('contact_us') }}
@endsection
@section('content')
    <div class="main">
        <div class="breadcrumb">
            <div class="container">
                <div class="contentWrapper">
                    <span class="title">
                        {{ __('contact_us') }}
                    </span>
                    <span>
                        <span class="home"><a href="{{url('/')}}">{{ __('home') }}</a></span>
                        <span><i class="fa-solid fa-angles-right"></i></span>
                        <span class="page">{{ __('contact_us') }}</span>
                    </span>
                </div>
            </div>
        </div>
    @if ($question)
        <section class="contactUs container commonMT">
            <div class="row">
                <div class="col-12">
                    <div class="flex_column_center">
                        <span class="commonTag">
                            {{isset($question->tag) ? $question->tag : "Got a Question?"}}
                        </span>
                        <span class="commonTitle">
                            {{isset($question->heading) ? $question->heading : "Admissions, Academics, Support:Find Your Answer Here!"}}
                        </span>
                    </div>
                </div>

                <div class="col-12">
                    <div class="row contactCardsWrapper">
                        <div class="col-sm-6 col-md-4 col-lg-4">
                            <div class="card">
                                <div>
                                    <img src="{{url('assets/images/contactIcon.png')}}" alt="">
                                </div>
                                <div>
                                    <span class="text">{{ __('phone_number') }}</span>
                                </div>
                                <div class="contactDetailsWrapper">
                                    <span class="numb"><a href="tel:{{$settings['school_phone']}}">{{ __('phone') }}: {{isset($settings['school_phone']) ? $settings['school_phone'] : '( +91 ) 12345 67890'}}</a></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-4">
                            <div class="card">
                                <div>
                                    <img src="{{url('assets/images/mailIcon.png')}}" alt="">
                                </div>
                                <div>
                                    <span class="text">{{ __('email') }}</span>
                                </div>
                                <div class="contactDetailsWrapper">
                                    <span class="numb"><a href="mailto:{{$settings['school_email']}}">{{ __('mail_us') }}: {{isset($settings['school_email'])? $settings['school_email'] :  'Schoolinfous@gmail.com'}}</a></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-4">
                            <div class="card">
                                <div>
                                    <img src="{{url('assets/images/locationIcon.png')}}" alt="">
                                </div>
                                <div>
                                    <span class="text">{{ __('address') }}</span>
                                </div>
                                <div class="contactDetailsWrapper">
                                    <span class="numb">{{ __('address') }}:  {{isset($settings['school_address']) ? $settings['school_address'] : ' 4517 Washington Ave. Manchester, Kentucky
                                        39495.'}}</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="col-12 mainCardWrapper">
                    <div class="mainCard card">

                        <div class="getInTouchWrapper">
                            <div class="card">
                                <div class="content">
                                    <span class="commonSpan">Get In Touch</span>
                                    <span class="commonDesc">Admission Made Easy: Start Your Application
                                        with Our Contact Form.</span>
                                </div>
                            </div>
                        </div>
                        <div class="formWrapper">
                            <form  id="create-form" name="create-form" method="POST" action="{{route('contact_us.store')}}">
                                <div class="row">
                                    <div class="col-sm-12 col-md-6 col-lg-6">
                                        <div class="d-flex flex-column gap-1 form-group">
                                            <label for="First Name">{{ __('first_name') }}</label>
                                            {!! Form::text('first_name', null, ['required', 'placeholder' => __('first_name'), 'class' => 'form-control']) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-6 col-lg-6 ">
                                        <div class="d-flex flex-column gap-1 form-group">
                                            <label for="Last Name">{{ __('last_name') }}</label>
                                            {!! Form::text('last_name', null, ['required', 'placeholder' => __('last_name'), 'class' => 'form-control']) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-6 col-lg-6">
                                        <div class="d-flex flex-column gap-1 form-group">
                                            <label for="email">{{ __('email') }}</label>
                                            {!! Form::text('email', null, ['required', 'placeholder' => __('email'), 'class' => 'form-control']) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-6 col-lg-6">
                                        <div class="d-flex flex-column gap-1 form-group">
                                            <label for="phone">{{ __('phone') }}</label>
                                            {!! Form::number('phone', null, ['required', 'placeholder' => __('phone'), 'class' => 'form-control']) !!}
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex flex-column gap-1 form-group">
                                            <label for="message">{{ __('message') }}</label>
                                            {!! Form::textarea('message', null, ['required', 'placeholder' => __('message'), 'class' => 'form-control','rows' => 5]) !!}
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <button type="submit" class="commonBtn" name="contactbtn">
                                            Submit <span><i class="fa-solid fa-arrow-right"></i></span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="mapWrapper commonMT">
                        <div>
                            <iframe src="{{isset($settings['maplink']) ? $settings['maplink'] : ''}}" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    </div>
@endsection
