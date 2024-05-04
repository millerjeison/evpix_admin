@extends('layouts.master')

@section('title')
    {{ __('class') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage') . ' ' . __('class') }}
            </h3>
        </div>
        <div class="row">
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('create') . ' ' . __('class') }}
                        </h4>
                        <form class="pt-3 class-create-form" id="create-form" action="{{ route('class.store') }}"
                            method="POST" novalidate="novalidate">
                            <div class="form-group">
                                <label>{{ __('medium') }} <span class="text-danger">*</span></label>
                                <div class="col-12 d-flex row">
                                    @foreach ($mediums as $medium)
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input type="radio" class="form-check-input" name="medium_id"
                                                    id="medium_{{ $medium->id }}" value="{{ $medium->id }}">
                                                {{ $medium->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="form-group">
                                <label>{{ __('name') }} <span class="text-danger">*</span></label>
                                <input name="name" type="text" placeholder="{{ __('name') }}"
                                    class="form-control" />
                            </div>
                            <div class="form-group">
                                <label>{{ __('shifts') }} <span class="text-info">(optional)</span></label>
                                <select name="shift_id" id="shift_id" class="form-control select2" style="width:100%;" tabindex="-1" aria-hidden="true">
                                    <option value="">{{__('Please')}}  {{__('select')}}</option>
                                    @foreach($shifts as $shift)
                                        <option value="{{$shift->id}}">{{$shift->title}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>{{ __('section') }} <span class="text-danger">*</span></label>
                                <div class="col-12">
                                    @foreach ($sections as $section)
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input"  name="section_id[]" value="{{ $section->id }}"/>
                                                {{ $section->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="form-group">
                                <label>{{ __('stream') }} <span class="text-info">(optional)</span></label>
                                <div class="col-12">
                                    @foreach ($streams as $stream)
                                        <div class="form-check">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" name="stream_id[]" value="{{ $stream->id }}" />{{ $stream->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="row">
                                <input class="btn btn-theme" id="create-btn" type="submit" value={{ __('submit') }}>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list') . ' ' . __('class') }}
                        </h4>
                        <div id="toolbar d-block">
                            <select name="medium_id" id="filter_medium_id" class="form-control">
                                <option value="">{{ __('all') }}</option>
                                @foreach ($mediums as $medium)
                                    <option value="{{ $medium->id }}">{{ $medium->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="toolbar d-block" class="mt-3">
                            <select name="shift_id" id="filter_shift_id" class="form-control">
                                <option value="">{{ __('all') }}</option>
                                @foreach ($shifts as $shift)
                                    <option value="{{ $shift->id }}">{{ $shift->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <table aria-describedby="mydesc" class='table table-striped' id='table_list' data-toggle="table"
                            data-url="{{ url('class-list') }}" data-click-to-select="true" data-side-pagination="server"
                            data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                            data-show-columns="true" data-show-refresh="true" data-fixed-columns="true"
                            data-fixed-number="2" data-fixed-right-number="1" data-trim-on-search="false"
                            data-mobile-responsive="true" data-sort-name="id" data-toolbar="#toolbar" data-sort-order="desc"
                            data-maintain-selected="true" data-export-types='["txt","excel"]'
                            data-export-options='{ "fileName": "class-list-<?= date('d-m-y') ?>" ,"ignoreColumn":
                            ["operate"]}'
                            data-show-export="true"
                            data-query-params="classQueryParams">
                            <thead>
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true" data-visible="false">
                                        {{ __('id') }}</th>
                                    <th scope="col" data-field="no" data-sortable="false">{{ __('no.') }}</th>
                                    <th scope="col" data-field="name" data-sortable="true">{{ __('name') }}</th>
                                    <th scope="col" data-field="medium_name" data-sortable="true">{{ __('medium') }}
                                    </th>
                                    <th scope="col" data-field="shift_name" data-sortable="true">{{ __('shifts') }}
                                    </th>
                                    <th scope="col" data-field="stream_name" data-sortable="true">
                                        {{ __('stream') }}
                                    </th>
                                    <th scope="col" data-field="section_name" data-sortable="true">
                                        {{ __('section') }}
                                    </th>
                                    <th scope="col" data-field="created_at" data-sortable="true" data-visible="false">
                                        {{ __('created_at') }}</th>
                                    <th scope="col" data-field="updated_at" data-sortable="true" data-visible="false">
                                        {{ __('updated_at') }}</th>
                                    <th scope="col" data-field="operate" data-sortable="false"
                                        data-events="classEvents">{{ __('action') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">{{ __('edit') . ' ' . __('class') }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form class="pt-3 class-edit-form" id="edit-form" action="{{ url('class') }}"
                            novalidate="novalidate">
                            <div class="modal-body">
                                <input type="hidden" name="edit_id" id="edit_id" value="" />
                                <div class="form-group">
                                    <label>{{ __('medium') }} <span class="text-danger">*</span></label>
                                    <div class="row ml-1">
                                        @foreach ($mediums as $medium)
                                            <div class="form-check form-check-inline col-md-2">
                                                <label class="form-check-label">
                                                    <input type="radio" class="form-check-input edit" name="medium_id"
                                                        id="edit_medium_{{ $medium->id }}"
                                                        value="{{ $medium->id }}"> {{ $medium->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-sm-12 col-md-12">
                                        <label>{{ __('name') }} <span class="text-danger">*</span></label>
                                        <input name="name" id="edit_name" type="text" placeholder="{{ __('name') }}" class="form-control" />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-sm-12 col-md-12">
                                        <label>{{ __('shifts') }} <span class="text-info">(optional)</span></label>
                                        <select name="shift_id" id="edit_shift_id" class="form-control select2" style="width:100%;" tabindex="-1" aria-hidden="true">
                                            <option value="">{{__('Please')}}  {{__('select')}}</option>
                                            @foreach($shifts as $shift)
                                                <option value="{{$shift->id}}">{{$shift->title}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-sm-12 col-md-12">
                                        <label>{{ __('stream') }}<span class="text-info">(optional)</span></label>
                                        <select name="stream_id" id="edit_stream_id" class="form-control select2" style="width:100%;" tabindex="-1" aria-hidden="true">
                                            <option value=" ">{{__('Please')}}  {{__('select')}}</option>
                                            @foreach($streams as $stream)
                                                <option value="{{$stream->id}}">{{$stream->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-sm-12 col-md-12">
                                        <label>{{ __('section') }} <span class="text-danger">*</span></label>
                                        <div class="col-12">
                                            @foreach ($sections as $section)
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input type="checkbox" class="form-check-input edit" name="section_id[]"
                                                            id="edit_sections_{{$section->id}}"
                                                            value="{{ $section->id }}">{{ $section->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-dismiss="modal">{{ __('close') }}</button>
                                <input class="btn btn-theme" type="submit" value={{ __('edit') }} />
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
