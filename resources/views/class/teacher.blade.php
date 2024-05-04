@extends('layouts.master')

@section('title')
{{ __('class') }} {{__('teacher')}}
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="car\`qd-title">
                        {{ __('assign') . ' ' . __('class') . ' ' . __('teacher') }}
                    </h4>
                    <div class="row">
                        <div class="col-12">


                            <div id="toolbar">
                                <select name="filter_class_id" id="filter_class_id" class="form-control">
                                    <option value="">{{ __('all') }}</option>
                                    @foreach ($classes as $class)
                                    <option value={{ $class->id }}>
                                        {{ $class->name . ' ' . $class->medium->name. ' ' .($class->streams->name ?? ' ')}}</option>
                                    @endforeach
                                </select>



                            </div>
                            <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table" data-url="{{ url('class-teacher-list') }}" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true" data-fixed-columns="true" data-fixed-number="2" data-fixed-right-number="1" data-trim-on-search="false" data-mobile-responsive="true" data-sort-name="id" data-query-params="AssignTeacherQueryParams" data-sort-order="desc" data-maintain-selected="true" data-export-types='["txt","excel"]' data-export-options='{ "fileName": "data-list-<?= date(' d-m-y') ?>" }'
                                >
                                <thead>
                                    <tr>
                                        <th scope="col" data-field="id" data-sortable="true" data-visible="false">
                                            {{ __('id') }}</th>
                                        <th scope="col" data-field="no" data-sortable="false">
                                            {{ __('no.') }}</th>
                                        <th scope="col" data-field="class" data-sortable="false">
                                            {{ __('class') }}</th>
                                        <th scope="col" data-field="stream_name" data-sortable="true">
                                            {{ __('stream') }}</th>
                                        <th scope="col" data-field="section" data-sortable="false">
                                            {{ __('section') }}</th>
                                            <th scope="col" data-field="teacher_id" data-sortable="false" data-visible="false">
                                                {{ __('Teacher ID') }}</th>
                                        <th scope="col" data-field="teachers" data-sortable="false" data-formatter="classTeacherFormatter">
                                            {{ __('Class Teacher') }}</th>
                                        <th data-events="actionEvents" scope="col" data-field="operate" data-sortable="false">{{ __('action') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">
                            {{ __('edit') . ' ' . __('class') . ' ' . __('teacher') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form class="edit-class-teacher-form" action="{{ route('class.teacher.store') }}" novalidate="novalidate">
                        @csrf
                        <div class="modal-body">
                            <div class="row form-group">
                                <div class="form-group col-sm-12 col-md-12">
                                    {{-- hidden input to store id --}}
                                    <input type="hidden" name="class_section_id" id="class_section_id_value">

                                    <label>{{ __('class') }} {{ __('section') }} <span class="text-danger">*</span></label>
                                    <select name="class_section_id_select" id="class_section_id" class="form-control" disabled>
                                        @foreach ($class_section as $section)
                                        <option value="{{ $section->id }}">
                                            {{ $section->class->name . ' ' . $section->section->name }}     {{$section->class->streams->name ?? ' '}}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row form-group">
                                <div class="form-group col-sm-12 col-md-12">
                                    <label>{{ __('Add New Class Teacher') }} <span class="text-danger">*</span></label>
                                    <select name="teacher_id" id="teacher_id" class="form-control teacher">


                                    </select>
                                </div>
                            </div>
                            <div class="form-group" id="hello">
                                    <label>{{ __('Remove Class Teacher') }}</label>
                                    <div class="class_teachers">
                                    </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('close') }}</button>
                            <input class="btn btn-theme" type="submit" value={{ __('submit') }} />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    window.actionEvents = {
            'click .editdata': function(e, value, row, index) {
                $('#class_section_id').val(row.id);

                //hidden input to store id
                $('#class_section_id_value').val(row.id);

                $.ajax('/get-all-class-teacher/' + row.id,{
                        dataType: 'json',

                            success: function (data,status,xhr) {
                                let html = ''
                                html += '<option value=" ">Please Select</option>';
                                $.each(data, function (key, value) {

                                    html += '<option value="' + value.id + '">'+ value.user.first_name + ' ' + value.user.last_name + '</option>';

                                });
                                $('.teacher').html(html);
                            },
                            error: function (jqXhr, textStatus, errorMessage) {
                                $('.teacher').append('Error: ' + errorMessage);
                            }
                });

                if(row.teacher_id == null){
                    $(function() {
                        let html = '';
                        html += '<label class="form-check label">No Data Found</label>';
                        $('.class_teachers').html(html);
                    });

                }else
                {
                    $.ajax('/get-class-teacher/' + row.id,{
                        dataType: 'json',

                            success: function (data,status,xhr) {
                                let html = ''
                                let count = 1;
                                $.each(data, function (key, value) {
                                    html += '<div class="form-check form-check-inline"><label class="form-check-label">'+ count + ". " + value.user.first_name + ' ' + value.user.last_name + '';
                                    html += '<button type="button" class="btn btn-inverse-danger btn-icon ml-3 remove-class-teacher-btn" data-class-id="' + row.id + '" data-teacher-id="' + value.id + '"><i class="fa fa-close"></i></button>';
                                    html += '</label></div>';
                                    count ++;
                                });
                                $('.class_teachers').html(html);
                            },
                            error: function (jqXhr, textStatus, errorMessage) {
                                $('.class_teachers').append('Error: ' + errorMessage);
                            }
                        });

                         $(document).on('click', '.remove-class-teacher-btn', function() {
                            const classSectionId = $(this).data('class-id');
                            const teacherId = $(this).data('teacher-id');


                            Swal.fire({
                                title: 'Are you sure?',
                                text: "Remove Class Teacher!",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Confirm!',
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        let url = baseUrl + '/remove-class-teacher/' + classSectionId + '/' + teacherId;
                                        function successCallback(response) {
                                            $.toast({
                                                text: response.message,
                                                icon: 'success',
                                                loader:false,
                                                position: 'top-right',
                                            });
                                            setTimeout(() => {
                                                window.location.reload();
                                            }, 1000);
                                        }
                                        function errorCallback(response) {
                                            showErrorToast(response.message);
                                        }
                                        ajaxRequest('POST', url, null, null, successCallback, errorCallback);
                                    }
                                });
                        });

                }
            }
        };
</script>
@endsection
