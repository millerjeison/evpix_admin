@extends('layouts.master')

@section('title')
    {{ __('educational_program') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage') . ' ' . __('educational_program') }}
            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('create') . ' ' . __('educational_program') }}
                        </h4>
                        <div class="col-12">
                            <form class="pt-3 sliders-create-form" id="create-form" action="{{route('educational.store')}}" method="POST" novalidate="novalidate" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('title') }} <span class="text-danger">*</span></label>
                                            {!! Form::text('title', null, ['required', 'placeholder' => __('title'), 'class' => 'form-control', 'id' => 'title']) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('image') }} <span class="text-danger">*</span></label>
                                            <input type="file" name="image" class="file-upload-default"  accept="image/*"/>
                                            <div class="input-group col-xs-12">
                                                <input type="text" class="form-control file-upload-info" disabled="" placeholder="{{ __('image') }}"/>
                                                <span class="input-group-append">
                                                    <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input class="btn btn-theme" id="create-btn" type="submit" value={{ __('submit') }}>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list') . ' ' . __('educational_program') }}
                        </h4>
                        <table aria-describedby="mydesc" class='table table-striped' id='table_list'
                               data-toggle="table" data-url="{{url('educational-program-list')}}" data-click-to-select="true"
                               data-side-pagination="server" data-pagination="true"
                               data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                               data-toolbar="#toolbar" data-show-columns="true"
                               data-show-refresh="true" data-fixed-columns="true"
                               data-fixed-number="2" data-fixed-right-number="1"
                               data-trim-on-search="false" data-mobile-responsive="true"
                               data-sort-name="id" data-sort-order="desc"
                               data-maintain-selected="true" data-export-types='["txt","excel"]'
                               data-export-options='{ "fileName": "slider-list-<?= date('d-m-y') ?>" ,"ignoreColumn": ["operate"]}'>
                            <thead>
                            <tr>
                                <th scope="col" data-field="id" data-sortable="true" data-visible="false">{{ __('id') }}</th>
                                <th scope="col" data-field="no" data-sortable="false">{{ __('no.') }}</th>
                                <th scope="col" data-field="title" data-sortable="true">{{ __('title') }}</th>
                                <th scope="col" data-field="image" data-sortable="true" data-formatter="imageFormatter">{{ __('image') }}</th>
                                <th scope="col" data-field="created_at" data-sortable="true" data-visible="false">{{ __('created_at') }}</th>
                                <th scope="col" data-field="updated_at" data-sortable="true" data-visible="false">{{ __('updated_at') }}</th>
                                <th scope="col" data-field="operate" data-sortable="false" data-events="programEvents">{{ __('action') }}</th>
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
                            <h5 class="modal-title" id="exampleModalLabel">{{ __('edit') . ' ' . __('sliders') }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form class="pt-3 edit-program" id="edit-program" action="{{ route('educational.update') }}" novalidate="novalidate">
                            <div class="modal-body">
                                <input type="hidden" name="edit_id" id="edit_id" value=""/>
                                <div class="form-group">
                                    <div class="form-group">
                                        <label>{{ __('title') }} <span class="text-danger">*</span></label>
                                        {!! Form::text('title', null, ['required', 'placeholder' => __('title'), 'class' => 'form-control', 'id' => 'edit_title']) !!}
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('image') }} <span class="text-danger">*</span></label>
                                    <input type="file" name="image" class="file-upload-default edit_image"  accept="image/*"/>
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control edit_image" value="" placeholder="{{__('image')}}" />
                                        <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                        </span>
                                    </div>
                                    <br>
                                    <br>
                                    <div class="w-100 text-center">
                                        <img src="" id="edit_program_image" class="w-100">
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

    window.programEvents = {
        'click .edit-data': function (e, value, row, index) {
            console.log(row);
            $('#edit_id').val(row.id);
            $('#edit_title').val(row.title);
            var fileInput = $('.edit_image');
            fileInput.val(null);  // Clear the selected file by resetting the input value
            fileInput.siblings('.form-control').val('');  // Update the text input field to display "No file selected"
            $('#edit_program_image').attr('src', row.image);
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
