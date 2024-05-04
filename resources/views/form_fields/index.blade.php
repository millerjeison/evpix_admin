@extends('layouts.master')

@section('title')
    {{ __('custom_fields') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage').' '.__('form_fields') }}
            </h3>
        </div>

        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <h4 class="card-title">
                                    {{ __('create').' '.__('form_fields') }}
                                </h4>
                            </div>
                            {{-- <div class="col-6 text-end">
                                <a href="">{{ __('Import Admin Form Fields') }}</a>
                            </div> --}}
                        </div>
                        <form class="pt-3 create-form-field" id="create-form-fields"  method="POST" novalidate="novalidate" action="{{route('form-fields.store')}}">
                            @csrf
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-5">
                                    <label>{{ __('name') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('name', null, ['required', 'placeholder' => __('name'), 'class' => 'form-control','onkeypress' => 'return ((event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || event.charCode == 8 || event.charCode == 32 || (event.charCode >= 48 && event.charCode <= 57));']) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-5">
                                    <label>{{ __('Type') }} <span class="text-danger">*</span></label>
                                    <select name="type" id="type" class="form-control type">
                                        <option value="">Please Select</option>
                                        <option value="text">{{ __('Text') }}</option>
                                        <option value="number">{{ __('Numeric Values') }}</option>
                                        <option value="dropdown">{{ __('Dropdown') }}</option>
                                        <option value="radio">{{ __('Radio Button') }}</option>
                                        <option value="checkbox">{{ __('Checkbox') }}</option>
                                        <option value="textarea">{{ __('Textarea') }}</option>
                                        <option value="file">{{ __('File Upload') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12 col-md-2 mt-3">
                                    <div class="col-12">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input type="checkbox" name="is_required" value="1" class="form-check-input" id="">{{ __('required') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="default-values-div" style="display: none;">
                                <div class="form-group col-sm-12 col-md-3">
                                    <button type="button" class="add-more-default-values btn btn-success">{{ __('Add Default Values') }} <span class="fa fa-plus"></span></button>
                                </div>
                                <div id="add-default-values">
                                    <div class="row">
                                        <div class="form-group col-sm-12 col-md-4">
                                                <label>{{ __('Default Values') }} <span class="text-danger">*</span></label>
                                                <input type="text" name="default_values[]" class="form-control default_values" placeholder="{{ __('Default Values') }}" disabled/>
                                        </div>
                                        <div class="form-group col-sm-12 col-md-2 mt-4">
                                            <button type="button" class="remove-default-values btn btn-inverse-danger btn-icon ml-3" disabled><span class="fa fa-times"></span></button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-sm-12 col-md-4">
                                            <label>{{ __('Default Values') }} <span class="text-danger">*</span></label>
                                            <input type="text" name="default_values[]" class="form-control default_values" placeholder="Default Values" disabled/>
                                        </div>
                                        <div class="form-group col-sm-12 col-md-2 mt-4">
                                            <button type="button" class="remove-default-values btn btn-inverse-danger btn-icon ml-3" disabled><span class="fa fa-times"></span></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input class="btn btn-theme" type="submit" value={{ __('submit') }}>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list') . ' ' . __('form_fields') }}
                        </h4>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-success" id="preview-fields" data-toggle="modal" data-target="#previewFieldModal">{{__('preview').' '.__('form_fields')}}</button>
                        </div>
                        <table aria-describedby="mydesc" class='table table-striped' id='table_list' data-toggle="table"
                        data-url="{{ route('form-fields.show', 1) }}" data-click-to-select="true" data-side-pagination="server"
                        data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                        data-show-columns="true" data-show-refresh="true" data-fixed-columns="true" data-reorderable-rows="true"
                        data-fixed-number="2" data-fixed-right-number="1" data-trim-on-search="false"
                        data-mobile-responsive="true" data-sort-name="id" data-toolbar="#toolbar" data-sort-order="asc"
                        data-maintain-selected="true" data-export-types='["txt","excel"]'
                        data-export-options='{ "fileName": "{{__('form-fields')}}-<?= date(' d-m-y') ?>" ,"ignoreColumn":["operate"]}'
                        data-show-export="true" data-query-params="formFieldQueryParams">
                            <thead>
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true" data-visible="false">{{ __('id') }}</th>
                                    <th scope="col" data-field="no">{{ __('no.') }}</th>
                                    <th scope="col" data-field="name" data-sortable="true">{{ __('name') }}</th>
                                    <th scope="col" data-field="type" data-sortable="true">{{ __('type') }}</th>
                                    <th scope="col" data-field="is_required" data-sortable="true" data-formatter="formFieldRequiredFormatter">{{ __('is').' '.__('required') }}</th>
                                    <th scope="col" data-field="default_values" data-sortable="true" data-formatter="formFieldDefaultValuesFormatter">{{ __('default').' '.__('values') }}</th>
                                    <th scope="col" data-field="other" data-sortable="false" data-visible="false" data-formatter="formFieldOtherValueFormatter">{{ __('other') }}</th>
                                    <th scope="col" data-field="rank" data-sortable="true">{{ __('rank') }}</th>
                                    <th scope="col" data-field="created_at" data-sortable="true" data-visible="false">{{__('created_at')}}</th>
                                    <th scope="col" data-field="updated_at" data-sortable="true" data-visible="false">{{__('updated_at')}}</th>
                                    <th scope="col" data-field="operate" data-sortable="false" data-events="formFieldsEvents">{{ __('action') }}</th>
                                </tr>
                            </thead>
                        </table>
                        <span class="d-block mb-4 mt-2 text-danger small">{{ __('draggable_rows_notes') }}</span>
                        <div class="mt-1">
                            <button id="change-order-form-field" class="btn btn-theme">Update Rank</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        {{ __('edit') . ' ' . __('form_fields') }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="fa fa-close"></i></span>
                    </button>
                </div>
                <form class="pt-3 edit-form-field" id="edit-form-fields" action="{{ url('form-fields',1) }}" novalidate="novalidate">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="edit_id" id="edit-id" value=""/>

                    <div class="modal-body">
                        <div class="form-group col-sm-12">
                            <label>{{ __('name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="edit_name" id="edit-name" placeholder="{{__('name')}}" class="form-control" required>
                        </div>
                        <div class="form-group col-sm-12">
                            <label>{{ __('type') }} <span class="text-danger">*</span></label>
                            <select id="edit-type" name="edit_type" class="form-control edit_type">
                                <option value="text" selected>Text</option>
                                <option value="number">Numeric</option>
                                <option value="dropdown">Dropdown</option>
                                <option value="radio">Radio Button</option>
                                <option value="checkbox">Checkbox</option>
                                <option value="textarea">TextArea</option>
                                <option value="file">File Upload</option>
                            </select>

                            {!! Form::hidden('edit_type', "", ['id' => 'edit-type-value']) !!}
                        </div>
                        <div class="form-group col-sm-12 col-md-2">
                            <label>{{ __('required') }} </label>
                            <div class="col-12">
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input type="checkbox" name="edit_required" id="edit-required" class="form-check-input" value="1">{{ __('required') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Option Section --}}
                        <div id="edit-default-values-div" style="display: none;">
                            <div class="form-group col-sm-12 col-md-12">
                                <button type="button" class="edit-add-more-default-values btn btn-success">{{ __('Add Default Values') }} <span class="fa fa-plus"></span></button>
                            </div>
                            <div class="col-12" id="edit-add-default-values">
                                <div class="row">
                                    <div class="form-group col-md-10">
                                        <label>{{ __('Default Values') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="default_values[]" class="form-control edit_default_values" placeholder="Default Values" disabled readonly/>
                                    </div>
                                    <div class="form-group col-md-2 mt-4">
                                        <button type="button" class="btn btn-icon btn-inverse-danger edit-remove-default-values" disabled><span class="fa fa-times"></span></button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-10">
                                        <label>{{ __('Default Values') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="default_values[]" class="form-control edit_default_values" placeholder="Default Values" disabled/>
                                    </div>
                                    <div class="form-group col-md-2 mt-4">
                                        <button type="button" class="btn btn-icon btn-inverse-danger edit-remove-default-values" disabled><span class="fa fa-times"></span></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- End Option Section --}}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('close') }}</button>
                        <input class="btn btn-theme" type="submit" value={{ __('edit') }} />
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Preview Admission Form -->
    <div class="modal fade" id="previewFieldModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"> {{ __('Admission Form Preview') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="fa fa-close"></i></span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row" id="previewFormBody">
                        @foreach($formFields as $row)
                            @if($row->type==="text" || $row->type==="number")
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ ucwords(str_replace('_', ' ', $row->name)) }} {!! ($row->is_required) ? ' <span class="text-danger">*</span></label>': '' !!}</label></label>
                                    <input type="{{$row->type}}" name="{{$row->name}}" placeholder="{{ ucwords(str_replace('_', ' ', $row->name)) }}" class="form-control" {{($row->is_required===1)?"required":''}}>
                                </div>
                            @endif

                            @if($row->type==="dropdown")
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ ucwords(str_replace('_', ' ', $row->name)) }} {!! ($row->is_required) ? ' <span class="text-danger">*</span></label>': '' !!}</label></label>
                                    <select name="{{ $row->name }}" class="form-control">
                                        <option value="">Please Select</option>
                                        @foreach(json_decode($row->default_values) as $options)
                                            @if($options != null)
                                                <option value="{{$options}}">{{ucfirst($options)}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if($row->type==="radio")
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ ucwords(str_replace('_', ' ', $row->name)) }} {!! ($row->is_required) ? ' <span class="text-danger">*</span></label>': '' !!}</label></label>
                                    <br>
                                    <div class="d-flex">
                                        @foreach(json_decode($row->default_values) as $options)
                                            @if($options != null)
                                                <div class="form-check form-check-inline">
                                                    <label class="form-check-label">
                                                        <input type="radio" name="{{$row->name}}" value="{{$options}}">
                                                        {{ ucfirst($options)}}
                                                    </label>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($row->type==="checkbox")
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ ucwords(str_replace('_', ' ', $row->name)) }} {!! ($row->is_required) ? ' <span class="text-danger">*</span></label>': '' !!}</label></label>
                                    <br>
                                    <div class="col-md-10">
                                        @foreach(json_decode($row->default_values) as $options)
                                            @if($options != null)
                                                <div class="form-check form-check-inline">
                                                    <label class="form-check-label">
                                                        <input type="checkbox" name="{{$row->name}}" value="{{$options}}">
                                                        {{ ucfirst($options) }}
                                                    </label>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($row->type==="textarea")
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ ucwords(str_replace('_', ' ', $row->name)) }} {!! ($row->is_required) ? ' <span class="text-danger">*</span></label>': '' !!}</label></label>
                                    <textarea name="{{$row->name}}" cols="10" rows="3" placeholder="{{ ucwords(str_replace('_', ' ', $row->name)) }}" class="form-control"></textarea>
                                </div>
                            @endif

                            @if($row->type==="file")
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ ucwords(str_replace('_', ' ', $row->name)) }} {!! ($row->is_required) ? ' <span class="text-danger">*</span></label>': '' !!}</label>
                                        <input type="file" name="image" class="file-upload-default"/>
                                        <div class="input-group col-xs-12">
                                            <input type="text" class="form-control file-upload-info" disabled="" placeholder="{{ ucwords(str_replace('_', ' ', $row->name)) }}" required/>
                                            <span class="input-group-append">
                                                <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                            </span>
                                        </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('close') }}</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        function formSuccessFunction() {
            $('#type').val('text').trigger('change');
            $('[data-repeater-item]').slice(2).remove();
        }
    </script>
@endsection
