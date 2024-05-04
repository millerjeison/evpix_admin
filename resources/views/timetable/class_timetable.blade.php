@extends('layouts.master')

@section('title')
    {{ __('class_timetable') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage').' '.__('class_timetable') }}
            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                         {{-- check for admin persmission --}}
                        @if(Auth::user()->hasRole('Super Admin'))
                            @can('timetable-create')
                                <div class="row">
                                    <div class="form-group col-sm-12 col-md-6">
                                        <label>{{ __('class') }} {{ __('section') }} <span class="text-danger">*</span></label>
                                        <select required name="class_section_id" id="class_timetable_class_section" class="form-control select2" style="width:100%;" tabindex="-1" aria-hidden="true">
                                            <option value="">{{__('select')}}</option>
                                            @foreach($class_sections as $section)
                                                <option value="{{$section->id}}" data-class="{{$section->class->id}}" data-section="{{$section->section->id}}">{{$section->class->name.' '.$section->section->name.' - '.$section->class->medium->name}} {{$section->class->streams->name ?? ' '}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="alert alert-warning text-center w-75 m-auto warning_no_data" role="alert">
                                    <strong>{{__('no_data_found')}}</strong>
                                </div>
                                <div class="row set_timetable"></div>
                            @endcan
                        @elseif (Auth::user()->hasRole('Teacher'))
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('class') }} {{ __('section') }} <span class="text-danger">*</span></label>
                                    <select required name="class_section_id" id="class_timetable_class_section" class="form-control select2" style="width:100%;" tabindex="-1" aria-hidden="true">
                                        <option value="">{{__('select')}}</option>
                                        @foreach($class_sections as $section)
                                            <option value="{{$section->id}}" data-class="{{$section->class->id}}" data-section="{{$section->section->id}}">{{$section->class->name.' '.$section->section->name.' - '.$section->class->medium->name}} {{$section->class->streams->name ?? ' '}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="alert alert-warning text-center w-75 m-auto warning_no_data" role="alert">
                                <strong>{{__('no_data_found')}}</strong>
                            </div>
                            <div class="row set_timetable"></div>
                        @else

                            {{-- <div class="alert alert-warning text-center w-75 m-auto warning_no_data" role="alert">
                                <strong>{{__('no_data_found')}}</strong>
                            </div> --}}
                        @endif


                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
