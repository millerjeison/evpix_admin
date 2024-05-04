@extends('layouts.master')

@section('title')
    {{ __('events') }}
@endsection

@section('content')
<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">
            {{ __('manage') . ' ' . __('events') }}
        </h3>
    </div>
    <div class="row">
        @if (Auth::user()->can('event-create'))
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">
                        {{ __('create') . ' ' . __('events') }}
                    </h4>
                    <form class="event-form pt-3" id="event-form" action="{{route('events.store')}}" method="POST" novalidate="novalidate">
                        @csrf
                        <div class="row">
                            <div class="form-group col-sm-12 col-md-6">
                                <label>{{ __('title') }} <span class="text-danger">*</span></label>
                                {!! Form::text('title', null, ['required', 'placeholder' => __('title'), 'class' => 'form-control']) !!}
                            </div>
                            <div class="form-group col-sm-12 col-md-6">
                                <label>{{ __('description') }}</label>
                                {!! Form::textarea('description', null, ['rows' => '3', 'placeholder' => __('description'), 'class' => 'form-control']) !!}
                            </div>
                            <div class="form-group col-sm-12 col-md-6">
                                <label>{{ __('image') }}</label>
                                <input type="file" name="image" class="file-upload-default" accept="image/*"/>
                                <div class="input-group col-xs-12">
                                    <input type="text" class="form-control file-upload-info" disabled="" placeholder="{{ __('image') }}" required="required"/>
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-sm-12 col-md-12">
                                <div class="d-flex">
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input type="radio" name="event_type" value="single" required id="single" class="form-check-input event_type">
                                            {{__('single')}}
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input type="radio" name="event_type" value="multiple" required id="multiple" class="form-check-input event_type">
                                            {{__('multiple')}}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Single Events Div --}}
                        <div class="row" id="single-div"  style="display:none;">
                            <div class="form-group col-sm-12 col-md-6">
                                <label>{{ __('date') }} <span class="text-danger">*</span></label>
                                {!! Form::text('date', null, ['required', 'placeholder' => __('date'), 'class' => 'datepicker-popup form-control']) !!}
                                <span class="input-group-addon input-group-append">
                                </span>
                            </div>
                            <div class="form-group col-sm-12 col-md-6">
                                <label>{{ __('timing') }}</label>
                                {!! Form::text('time', null, ['placeholder' => __('start_time') .' - ' . __('end_time'), 'class' => 'timerange form-control']) !!}
                                <span class="input-group-addon input-group-append">
                                </span>
                            </div>
                        </div>
                        {{-- Select date range for multiple events --}}
                        <div class="row date-range-div" id="date-range-div" style="display:none;">
                            <div class="form-group col-sm-12 col-md-6">
                                <label>{{ __('date').' '.__('range') }} <span class="text-danger">*</span></label>
                                {!! Form::text('date_range', null, ['required', 'id' => 'date_range', 'placeholder' => __('date'), 'class' => 'daterange form-control']) !!}
                                <span class="input-group-addon input-group-append">
                                </span>
                            </div>
                        </div>
                        {{-- Multiple Events Div --}}
                        <div class="row add-multiple-event-div" id="add-multiple-event-div" style="display:none;">
                            <div class="form-group col-sm-12 col-md-6">
                                <label>{{ __('title') }} <span class="text-danger">*</span></label>
                                {!! Form::text('events[0][title]', null, ['required','placeholder' => __('title'), 'class' => 'form-control']) !!}
                            </div>
                            <div class="form-group col-sm-12 col-md-6">
                                <label>{{ __('date') }} <span class="text-danger">*</span></label>
                                {!! Form::text('events[0][date]', null, ['required','placeholder' => __('date'), 'class' => 'datepicker-popup form-control']) !!}
                                <span class="input-group-addon input-group-append">
                                </span>
                            </div>
                            <div class="form-group col-sm-12 col-md-6">
                                <label>{{ __('timing') }} <span class="text-danger">*</span></label>
                                {!! Form::text('events[0][timerange]', null, ['required','placeholder' => __('start_time') .' - ' . __('end_time'), 'class' => 'timerange form-control']) !!}
                                <span class="input-group-addon input-group-append">
                                </span>
                            </div>
                            <div class="form-group col-sm-12 col-md-5">
                                <label>{{ __('description') }}</label>
                                {!! Form::textarea('events[0][description]', null, ['rows' => '3', 'placeholder' => __('description'), 'class' => 'form-control']) !!}
                            </div>
                            <div class="col-md-1 text-center pt-4">
                                <button type="button" class="btn btn-icon btn-danger remove-multiple-event-div" disabled>
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>
                        {{-- <div id="extra-multiple-event"></div> --}}
                        <div class="row" id="add-more" style="display:none">
                            <div class="form-group col-sm-12 col-md-12">
                                <button type="button" class="btn btn-success add-multi-div" id="add-multi-div">
                                    <i class="fa fa-plus"></i>&nbsp;&nbsp;{{ __('add_more') }}</button>
                            </div>
                        </div>

                        <input class="btn btn-theme" type="submit" value={{ __('submit') }}>
                    </form>
                </div>
            </div>
        </div>
        @endif

        @can('event-list')
        <div class="col-md-12 grid-margin stretch-card search-container">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">
                        {{ __('list') . ' ' . __('events') }}
                    </h4>
                    <table aria-describedby="mydesc" class='table' id='table_list'
                        data-toggle="table" data-url="{{ url('events/show') }}" data-click-to-select="true"
                        data-side-pagination="server" data-pagination="true"
                        data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                        data-toolbar="#toolbar" data-show-columns="true"
                        data-show-refresh="true" data-fixed-columns="true"
                        data-fixed-number="2" data-fixed-right-number="1"
                        data-trim-on-search="false" data-mobile-responsive="true"
                        data-sort-name="id" data-sort-order="desc"
                        data-maintain-selected="true" data-export-types='["txt","excel"]'
                        data-export-options='{ "fileName": "event-list-<?= date('d-m-y') ?>" ,"ignoreColumn": ["operate"]}'>
                        <thead>
                            <tr>
                                <th  data-field="id" data-sortable="true" data-visible="false">{{ __('id') }}</th>
                                <th data-field="no" data-sortable="false">{{ __('no.') }}</th>
                                <th data-field="title" data-sortable="false">{{ __('title') }}</th>
                                <th data-field="type" data-sortable="false">{{ __('type') }}</th>
                                <th data-field="date" data-sortable="false">{{ __('date') }}</th>
                                <th data-field="time" data-sortable="false">{{ __('timing') }}</th>
                                <th data-field="description" data-sortable="false">{{ __('description') }}</th>
                                <th data-field="schedule" data-sortable="false">{{ __('schedule') }}</th>
                                <th data-field="image" data-formatter="imageFormatter" data-sortable="false">{{ __('image') }}</th>
                                <th data-events="actionEvents" data-field="operate"data-sortable="false">{{ __('action') }}</th>

                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    @endcan
        <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{__('edit').' '.__('events')}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="edit-event" class="edit-event" action="{{url('events/update')}}" novalidate="novalidate">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="edit_id" id="edit_id">
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('title') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('title', null, ['required', 'placeholder' => __('title'), 'class' => 'form-control', 'id' => 'title']) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('description') }}</label>
                                    {!! Form::textarea('description', null, ['rows' => '3', 'placeholder' => __('description'), 'class' => 'form-control', 'id' => 'description']) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('image') }}</label>
                                    <input type="file" name="image" class="file-upload-default" accept="image/*"/>
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" disabled="" placeholder="{{ __('image') }}"/>
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                        </span>
                                    </div>
                                    <div style="width: 100px; margin-top: 10px">
                                        <img src="" id="edit-event-image" class="img-fluid w-100" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-12">
                                    <div class="d-flex">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input type="radio" name="edit_event_type" value="single" required id="edit_single" class="form-check-input edit_event_type">
                                                {{__('single')}}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input type="radio" name="edit_event_type" value="multiple" required id="edit_multiple" class="form-check-input edit_event_type">
                                                {{__('multiple')}}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="edit-single-div"  style="display:none;">
                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('date') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('edit_date', null, ['required', 'placeholder' => __('date'), 'class' => 'datepicker-popup form-control','id' => 'date']) !!}
                                    <span class="input-group-addon input-group-append">
                                    </span>
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('timing') }}</label>
                                    {!! Form::text('edit_time', null, [ 'placeholder' => __('start_time') .' - ' . __('end_time'), 'class' => 'timerange form-control','id' => 'time']) !!}
                                    <span class="input-group-addon input-group-append">
                                    </span>
                                </div>
                            </div>
                            <div class="row edit-date-range-div" id="edit-date-range-div" style="display:none;">
                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('date').' '.__('range') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('date_range', null, ['required', 'placeholder' => __('date'), 'class' => 'daterange form-control' , 'id' => 'daterange']) !!}
                                    <span class="input-group-addon input-group-append">
                                    </span>
                                </div>
                            </div>
                            {{-- Edit Multiple Event Template --}}
                            <div class="row edit-multiple-event-group-div" id="edit-multiple-event-group-div" style="display:none;">
                                <input type="hidden" name="edit_events[0][id]"  class="edit-multiple-event-group-id form-control">
                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('title') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('edit_events[0][title]', null, ['required', 'placeholder' => __('title'), 'class' => 'form-control edit_multi_event_title']) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('date') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('edit_events[0][date]', null, ['required', 'placeholder' => __('date'), 'class' => 'datepicker-popup form-control edit_multi_event_date']) !!}
                                    <span class="input-group-addon input-group-append">
                                    </span>
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('timing') }}</label>
                                    {!! Form::text('edit_events[0][timerange]', null, ['required', 'placeholder' => __('start_time') .' - ' . __('end_time'), 'class' => 'timerange form-control edit_multi_event_time']) !!}
                                    <span class="input-group-addon input-group-append">
                                    </span>
                                </div>
                                <div class="form-group col-sm-12 col-md-5">
                                    <label>{{ __('description') }}</label>
                                    {!! Form::textarea('edit_events[0][description]', null, ['rows' => '3', 'placeholder' => __('description'), 'class' => 'form-control edit_multi_event_description']) !!}
                                </div>
                                <div class="col-md-1 text-center pt-4">
                                    <button type="button" class="btn btn-icon btn-danger edit-remove-multiple-event-group" disabled>
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>

                            <div id="edit-extra-multiple-event"></div>

                            <div class="row edit-add-more" id="edit-add-more" style="display:none">
                                <div class="form-group col-sm-12 col-md-12">
                                    <button type="button" class="btn btn-success add-multiple-event-group-div" id="add-multiple-event-group-div">
                                        <i class="fa fa-plus"></i>&nbsp;&nbsp;{{ __('add_more') }}</button>
                                </div>
                            </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('close')}}</button>
                            <input class="btn btn-theme" type="submit" value={{ __('edit') }} />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
    <script type="text/javascript">

        window.actionEvents = {

            'click .edit-data': function (e, value, row, index) {
                $('#edit_id').val('');
                $('#title').val('');
                $('#description').val('');
                $('#edit-event-image').attr('src', '');
                $('#daterange').val('');
                $('#edit-extra-multiple-event').empty();

                $('#edit_id').val(row.id);
                $('#title').val(row.title);
                $('#description').val(row.description);
                $('#edit-event-image').attr('src', row.image);


                $('input[name="edit_event_type"][value="' + row.type + '"].edit_event_type').prop('checked', true);


                if (row.type == 'multiple') {

                    $('#edit-single-div').hide();
                    $('.edit-add-more').show();
                    $('#edit-date-range-div').show();

                    var DateRange = convertDateRange(row.date);

                    $('#daterange').val(DateRange);

                        $('.remove-multiple-event-group').prop('disabled', false);

                        if(row.multipleEvents.length <= 2)
                        {
                            $('.edit-remove-multiple-event-group').prop('disabled', true);
                        }else{
                            $('.edit-remove-multiple-event-group').prop('disabled', false);
                        }
                        // Loop through each multiple event and append to the container
                        $.each(row.multipleEvents, function (key, group) {

                            if (key == 0) {
                                html = $('.edit-multiple-event-group-div').show();
                                html.find('.edit-remove-multiple-event-group').attr('data-id', group.id);
                                html.find('.edit-multiple-event-group-id').val(group.id);
                                html.find('.edit_multi_event_title').val(group.title);
                                html.find('.edit_multi_event_date').val(formatDate(group.date));
                                html.find('.edit_multi_event_time').val(group.start_time + ' - ' +  group.end_time);
                                html.find('.edit_multi_event_description').val(group.description);

                            } else {
                                  // Clone the template for each event
                                let EventGroup = cloneOldMultipleEventTemplate();
                                $('#edit-extra-multiple-event').append(EventGroup);

                                EventGroup.find('.edit-remove-multiple-event-group').attr('data-id', group.id);
                                EventGroup.find('.edit-multiple-event-group-id').val(group.id);
                                EventGroup.find('.edit_multi_event_title').val(group.title);
                                EventGroup.find('.edit_multi_event_date').val(formatDate(group.date));
                                EventGroup.find('.edit_multi_event_time').val(group.start_time + ' - ' +  group.end_time);
                                EventGroup.find('.edit_multi_event_description').val(group.description);

                                initializeTimerangePicker(EventGroup);
                            }
                        });
                }
                else{
                    $('#edit-multi-div').hide();
                    $('#edit-date-range-div').hide();
                    $('#edit-multiple-event-group-div').hide();
                    $('.edit-add-more').hide();
                    $('#edit-single-div').show();

                    if(row.time)
                    {
                        var timerange = convertTimeRange(row.time);
                        $('#time').val(timerange);
                        initializeTimerangePicker();
                    }
                    $('#date').val(row.date);

                }
            }
        };
    </script>

    <script type="text/javascript">
        function queryParams(p) {
            return {
                limit: p.limit,
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                search: p.search
            };
        }
    </script>
@endsection
