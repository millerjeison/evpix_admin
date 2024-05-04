@extends('layouts.master')

@section('title')
    {{ __('Shifts') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage').' '.__('shifts') }}
            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('create').' '.__('new').' '.__('shifts') }}
                        </h4>
                        <form class="pt-3 section-create-form" id="create-form" action="{{route('shifts.store')}}" method="POST" novalidate="novalidate">
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('shift_name') }} <span class="text-danger">*</span></label>
                                    <input name="name" type="text" placeholder="{{ __('name') }}" class="form-control" required/>
                                </div>
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('start_time') }} <span class="text-danger">*</span></label>
                                    <input name="start_time" type="time" class="form-control" required/>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('end_time') }} <span class="text-danger">*</span></label>
                                    <input name="end_time"type="time" class="form-control" required/>
                                </div>
                            </div>
                            <input class="btn btn-theme" id="create-btn" type="submit" value={{ __('submit') }}>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list').' '.__('shifts') }}
                        </h4>
                        <table aria-describedby="mydesc" class='table table-striped' id='table_list' data-toggle="table" data-url="{{ url('shifts/show') }}" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"  data-search="true" data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true" data-fixed-columns="true" data-fixed-number="2" data-fixed-right-number="1" data-trim-on-search="false" data-mobile-responsive="true" data-sort-name="id" data-sort-order="desc" data-maintain-selected="true" data-query-params="queryParams" data-show-export="true"
                               data-export-options='{"fileName": "shift-list-<?= date('d-m-y') ?>","ignoreColumn": ["operate"]}'>
                            <thead>
                            <tr>
                                <th scope="col" data-field="id" data-sortable="true" data-visible="false">{{__('id')}}</th>
                                <th scope="col" data-field="no" data-sortable="false">{{__('no.')}}</th>
                                <th scope="col" data-field="title" data-sortable="false">{{__('shift_name')}}</th>
                                <th scope="col" data-field="start_time" data-sortable="false">{{__('starting_time')}}</th>
                                <th scope="col" data-field="end_time" data-sortable="false">{{__('ending_time')}}</th>
                                <th scope="col" data-field="status" data-sortable="false" data-formatter="shiftStatusFormatter">{{__('status')}}</th>
                                <th scope="col" data-field="created_at" data-sortable="true" data-visible="false">{{__('created_at')}}</th>
                                <th scope="col" data-field="updated_at" data-sortable="true" data-visible="false">{{__('updated_at')}}</th>
                                <th scope="col" data-field="operate" data-sortable="false" data-events="actionEvents">{{__('action')}}</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
             <!-- Modal -->
             <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">{{__('edit').' '.__('shifts')}}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form class="pt-3 section-edit-form" id="edit-form" action="{{ url('shifts') }}" novalidate="novalidate">
                            <input type="hidden" name="edit_id" id="edit_id" value=""/>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="form-group col-sm-6 col-md-6">
                                        <label>{{ __('shift_name') }} <span class="text-danger">*</span></label>
                                        <input name="edit_name" id="edit_name" type="text" placeholder="{{ __('name') }}" class="form-control" required/>
                                    </div>
                                    <div class="form-group col-sm-6 col-md-6">
                                        <label>{{ __('start_time') }} <span class="text-danger">*</span></label>
                                        <input name="edit_start_time" id="edit_start_time" type="time" class="form-control" required/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-sm-6 col-md-6">
                                        <label>{{ __('end_time') }} <span class="text-danger">*</span></label>
                                        <input name="edit_end_time" id="edit_end_time" type="time" class="form-control" required/>
                                    </div>
                                    <div class="form-group col-sm-6 col-md-6">
                                            <label>{{ __('status') }} <span class="text-danger">*</span></label><br>
                                            <div class="d-flex">
                                                <div class="form-check form-check-inline">
                                                    <label class="form-check-label">
                                                        {!! Form::radio('status', '1',false,['id' => 'edit_status_active']) !!}
                                                        {{ __('active') }}
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <label class="form-check-label">
                                                        {!! Form::radio('status', '0',false,['id' => 'edit_status_inactive']) !!}
                                                        {{ __('inactive') }}
                                                    </label>
                                                </div>
                                            </div>
                                        
                                    </div>
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
                $('#edit_id').val(row.id);
                $('#edit_name').val(row.title);
                $('#edit_start_time').val(row.start_time);
                $('#edit_end_time').val(row.end_time);
                if (row.status == '1') {
                $('#edit_status_active').removeAttr('checked');
                $('#edit_status_active').attr('checked', 'true');
                } else {
                $('#edit_status_inactive').removeAttr('checked');
                $('#edit_status_inactive').attr('checked', 'true');
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