<!-- partial:../../partials/_sidebar.html -->
<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        {{-- dashboard --}}
        <li class="nav-item">
            <a  class="nav-link" href="{{ url('/home') }}">
                <i class="fa fa-tachometer menu-icon" style="margin: 0 1px 0 1px"></i>
                <span class="menu-title">{{ __('dashboard') }}</span>
            </a>
        </li>

        @hasrole('Super Admin')
            {{-- academics --}}
            @canany(['medium-create', 'section-create', 'subject-create', 'class-create', 'subject-create',
                'class-teacher-create', 'subject-teacher-list', 'subject-teachers-create', 'assign-class-to-new-student',
                'promote-student-create'])
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#academics-menu" aria-expanded="false"
                        aria-controls="academics-menu">
                        <i class="fa fa-university menu-icon"></i><span class="menu-title">{{ __('academics') }}</span>
                        <i class="fa fa-angle-left fa-2xl menu-arrow"></i>
                    </a>
                    <div class="collapse" id="academics-menu">
                        <ul class="nav flex-column sub-menu">
                            @can('medium-create')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('medium.index') }}"> {{ __('medium') }} </a>
                                </li>
                            @endcan

                            @can('section-create')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('section.index') }}"> {{ __('section') }} </a>
                                </li>
                            @endcan

                            @can('stream-create')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('stream.index') }}"> {{ __('stream') }} </a>
                                </li>
                            @endcan

                            @can('shift-create')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('shifts.index') }}"> {{ __('shifts') }} </a>
                            </li>
                            @endcan

                            @can('subject-create')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('subject.index') }}"> {{ __('subject') }} </a>
                                </li>
                            @endcan

                            @can('class-create')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('class.index') }}"> {{ __('class') }} </a>
                                </li>
                            @endcan

                            @can('subject-create')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('class.subject') }}">{{ __('assign_class_subject') }} </a>
                                </li>
                            @endcan
                            @can('class-teacher-create')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('class.teacher') }}">
                                        {{ __('assign_class_teacher') }}
                                    </a>
                                </li>
                            @endcan

                            @canany(['subject-teacher-list', 'subject-teacher-create', 'subject-teacher-edit',
                                'subject-teacher-delete'])
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('subject-teachers.index') }}">
                                        {{ __('assign') . ' ' . __('subject') . ' ' . __('teacher') }}
                                    </a>
                                </li>
                            @endcan
                            @can('assign-class-to-new-student')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('students.assign-class') }}">
                                        {{ __('assign_new_student_class') }}
                                    </a>
                                </li>
                            @endcan
                            @can('promote-student-create')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('promote-student.index') }}">
                                        {{ __('promote_student') }}
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </li>
            @endcanany
        @endrole

        {{-- student --}}
        @canany(['student-create', 'student-list', 'category-create', 'student-reset-password', 'class-teacher', 'form-field-create'])
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#student-menu" aria-expanded="false"
                    aria-controls="academics-menu"><i class="fa fa-graduation-cap menu-icon" style="padding: 8px"></i>
                    <span class="menu-title">{{ __('students') }}</span>
                    <i class="fa fa-angle-left menu-arrow"></i>
                </a>
                <div class="collapse" id="student-menu">
                    <ul class="nav flex-column sub-menu">
                        @can('form-field-create')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('form-fields.index') }}">
                                {{ __('admission') }}  {{ __('custom_fields')}}
                            </a>
                        </li>
                        @endcan
                        @can('student-create')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('students.create') }}">
                                    {{ __('student_admission') }}
                                </a>
                            </li>
                        @endcan

                        @can('student-create')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('students.index-students-roll-number') }}">
                                    {{ __('assign') }} {{ __('roll_no') }}
                                </a>
                            </li>
                        @endcan

                        @canany(['student-list', 'class-teacher'])
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('students.index') }}">
                                    {{ __('student_details') }}
                                </a>
                            </li>
                        @endcanany

                        @can('category-create')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('category.index') }}">
                                    {{ __('student_category') }}
                                </a>
                            </li>
                        @endcan
                        @can('student-reset-password')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('students.reset_password') }}">
                                    {{ __('students') . ' ' . __('reset_password') }}
                                </a>
                            </li>
                        @endcan
                        @if (Auth::user()->hasRole('Super Admin'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('students.create-bulk-data') }}">
                                    {{ __('add_bulk_data') }}
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </li>
        @endcanany

        {{-- teacher --}}
        @can(['teacher-create', 'teacher-list'])
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#teacher-menu" aria-expanded="false"
                    aria-controls="academics-menu"><i class="fa fa-user menu-icon" style="margin: 0 4px 0 4px"></i>
                    <span class="menu-title">{{ __('teacher') }}</span>
                    <i class="fa fa-angle-left menu-arrow"></i>
                </a>
                <div class="collapse" id="teacher-menu">
                    <ul class="nav flex-column sub-menu">
                        @can('teacher-create')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('teachers.index') }}">
                                    {{ __('teacher_create') }}
                                </a>
                            </li>
                        @endcan
                        @can('teacher-list')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('teacher.details') }}">
                                    {{ __('teacher_details') }}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </div>
            </li>
        @endcan

        {{-- parents --}}
        @can('parents-create')
            <li class="nav-item">
                <a href="{{ route('parents.index') }}" class="nav-link">
                    <i class="fa fa-users menu-icon"></i>
                     <span class="menu-title">{{ __('parents') }}</span>
                </a>
            </li>
        @endcan

        {{-- Staff Management --}}
        @canany(['role-create','staff-create','staff-list'])
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#settings-menu" aria-expanded="false"
                aria-controls="settings-menu"><i class="fa fa-user-secret menu-icon" style="margin: 0 2px 0 2px"></i>
                <span class="menu-title">{{ __('staff').' '. __('management') }}</span>
                <i class="fa fa-angle-left menu-arrow"></i>
            </a>
            <div class="collapse" id="settings-menu">
                <ul class="nav flex-column sub-menu">
                    @can('role-create')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('roles/') }}"> {{ __('role_permission') }}
                            </a>
                        </li>
                    @endcan
                    @can('staff-list')
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('staff.index')}}"> {{ __('staff') }}
                        </a>
                    </li>
                @endcan
                </ul>
            </div>
        </li>
        @endcanany


        {{-- timetable --}}
        @canany(['timetable-create', 'class-timetable', 'teacher-timetable'])
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#timetable-menu" aria-expanded="false"
                    aria-controls="timetable-menu"> <i class="fa fa-calendar menu-icon" style="margin: 0 1px 0 1px"></i>
                     <span class="menu-title">{{ __('timetable') }}</span>
                     <i class="fa fa-angle-left menu-arrow"></i>
                </a>
                <div class="collapse" id="timetable-menu">
                    <ul class="nav flex-column sub-menu">
                        @can('timetable-create')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('timetable.index') }}">{{ __('create_timetable') }} </a>
                            </li>
                        @endcan
                        @canany(['class-timetable', 'class-teacher'])
                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('class-timetable') }}">
                                    {{ __('class_timetable') }}
                                </a>
                            </li>
                        @endcanany
                        @can('teacher-timetable')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('teacher-timetable') }}">
                                    {{ __('teacher_timetable') }}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </div>
            </li>
        @endcanany

          {{-- attendance --}}
        @canany(['class-teacher'])
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#attendance-menu" aria-expanded="false"
                    aria-controls="attendance-menu"><i class="fa fa-check menu-icon"></i>
                    <span class="menu-title">{{ __('attendance') }}</span>
                    <i class="fa fa-angle-left menu-arrow"></i>
                    </a>
                <div class="collapse" id="attendance-menu">
                    <ul class="nav flex-column sub-menu">
                        @can('attendance-create')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('attendance.index') }}">
                                    {{ __('add_attendance') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('attendance.add-bulk-data')}}">
                                    {{ __('add_bulk_data') }}
                                </a>
                            </li>
                        @endcan

                        {{-- view attendance --}}
                        @can('attendance-list')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('attendance.view') }}">
                                    {{ __('view_attendance') }}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </div>
            </li>
        @endcanany

        {{-- subject lesson --}}
        @canany(['lesson-list', 'lesson-create', 'lesson-edit', 'lesson-delete', 'topic-list', 'topic-create',
            'topic-edit', 'topic-delete'])
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#subject-lesson-menu" aria-expanded="false"
                    aria-controls="subject-lesson-menu"><i class="fa fa-book menu-icon"></i>
                    <span class="menu-title">{{ __('subject_lesson') }}</span> <i class="fa fa-angle-left menu-arrow"></i></a>
                <div class="collapse" id="subject-lesson-menu">
                    <ul class="nav flex-column sub-menu">
                        @canany(['lesson-list', 'lesson-create', 'lesson-edit', 'lesson-delete'])
                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('lesson') }}"> {{ __('create_lesson') }}</a>
                            </li>
                        @endcanany

                        @canany(['topic-list', 'topic-create', 'topic-edit', 'topic-delete'])
                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('lesson-topic') }}"> {{ __('create_topic') }}</a>
                            </li>
                        @endcanany
                    </ul>
                </div>
            </li>
        @endcanany

        {{-- student assignment --}}
        @canany(['assignment-create', 'assignment-submission'])
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#student-assignment-menu" aria-expanded="false"
                    aria-controls="student-assignment-menu"> <i class="fa fa-tasks menu-icon"></i>
                    <span class="menu-title">{{ __('student_assignment') }}</span>
                    <i class="fa fa-angle-left menu-arrow"></i>
                </a>
                <div class="collapse" id="student-assignment-menu">
                    <ul class="nav flex-column sub-menu">
                        @can('assignment-create')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('assignment.index') }}">
                                    {{ __('create_assignment') }}
                                </a>
                            </li>
                        @endcan
                        @can('assignment-submission')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('assignment.submission') }}">
                                    {{ __('assignment_submission') }}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </div>
            </li>
        @endcanany

        {{-- exam --}}
        @canany(['exam-create', 'exam-timetable-create', 'exam-upload-marks', 'grade-create'])
        {{-- @dd(Auth::user()->toArray()) --}}
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#exam-menu" aria-expanded="false"
                aria-controls="exam-menu"><i class="fa fa-file-text menu-icon" style="margin: 0 2px 0 2px"></i>
                <span class="menu-title">{{ __('exam') }}</span>
                <i class="fa fa-angle-left menu-arrow"></i>
            </a>
            <div class="collapse" id="exam-menu">
                <ul class="nav flex-column sub-menu">
                    @can('exam-create')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('exams.index') }}"> {{ __('create_exam') }}
                            </a>
                        </li>
                    @endcan
                    @can('exam-timetable-create')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('exam-timetable.index') }}">
                                {{ __('create_exam_timetable') }}
                            </a>
                        </li>
                    @endcan
                    @can('class-teacher')
                        @can('exam-upload-marks')

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('exams.upload-marks') }}">
                                    {{ __('upload') }} {{ __('exam_marks') }}
                                </a>
                            </li>
                        @endcan
                        @can('exam-result')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('exams.get-result') }}">
                                    {{ __('students') }} {{ __('exam_result') }}
                                </a>
                            </li>
                        @endcan
                    @endcan
                    @can('grade-create')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('grades') }}">
                                {{ __('exam') }} {{ __('grade') }}
                            </a>
                        </li>
                    @endcan
                </ul>
            </div>
        </li>
        @endcan

        {{-- Fees --}}
        @canany(['fees-type', 'fees-classes', 'fees-paid'])
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#fees-menu" aria-expanded="false"
                aria-controls="exam-menu"><i class="fa fa-money menu-icon"></i>
                <span class="menu-title">{{ __('fees') }}</span>
                <i class="fa fa-angle-left menu-arrow"></i>
            </a>
            <div class="collapse" id="fees-menu">
                <ul class="nav flex-column sub-menu">
                    @can('fees-type')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('fees-type.index') }}"> {{ __('fees') }}
                                {{ __('type') }}
                            </a>
                        </li>
                    @endcan
                    @can('fees-classes')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('fees.class.index') }}">{{ __('assign') }}
                                {{ __('fees') }} {{ __('classes') }} </a>
                        </li>
                    @endcan
                    @can('fees-paid')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('fees.paid.index') }}"> {{ __('fees') }}
                                {{ __('paid') }}
                            </a>
                        </li>
                    @endcan
                    {{-- @can('fees-paid')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('fees.pending.list') }}"> {{ __('fees') }}
                                {{ __('pending') }}
                            </a>
                        </li>
                    @endcan --}}
                    @can('fees-paid')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('fees.transactions.log.index') }}"> {{ __('fees') }}
                                {{ __('transactions') }} {{ __('logs') }}
                            </a>
                        </li>
                    @endcan
                    {{-- @can('fees-paid')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('fees.receipt') }}"> {{__('fees')}} {{ __('receipt') }} {{__('logs')}}
                    </a>
                </li>
                @endcan --}}
                </ul>
            </div>
        </li>
        @endcan

        @canany(['manage-online-exam'])
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#online-exam-menu" aria-expanded="false"
                aria-controls="online-exam-menu"> <i class="fa fa-laptop menu-icon"></i>
                <span class="menu-title">{{ __('online') }} {{ __('exam') }}</span>
                <i class="fa fa-angle-left menu-arrow"></i>
            </a>
            <div class="collapse" id="online-exam-menu">
                <ul class="nav flex-column sub-menu">
                    @can('manage-online-exam')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('online-exam.index') }}"> {{ __('manage') }}
                                {{ __('online') }} {{ __('exam') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('online-exam-question.index') }}"> {{ __('manage') }}
                                {{ __('questions') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('online-exam.terms-conditions') }}">
                                {{ __('terms_condition') }}
                            </a>
                        </li>
                    @endcan
                </ul>
            </div>
        </li>
        @endcan

        {{-- notification --}}
        @can('notification-create')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('notifications.index') }}">
                    <i class="fa fa-bell menu-icon"></i>
                    <span class="menu-title">{{ __('custom').' '. __('notifications') }}</span>
                </a>
            </li>
        @endcan


        {{-- announcement --}}
        @can('announcement-create')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('announcement.index') }}">
                    <i class="fa fa-bullhorn menu-icon"></i>
                    <span class="menu-title">{{ __('announcement') }}</span>
                </a>
            </li>
        @endcan


        {{-- sliders --}}
        @can('slider-create')
           <li class="nav-item">
               <a href="{{ route('sliders.index') }}" class="nav-link"> <i class="fa fa-sliders menu-icon"></i>
                   <span class="menu-title">{{ __('sliders') }}</span></a>
           </li>
        @endcan

         {{-- Holiday --}}
        @canany(['holiday-create', 'holiday-list'])
            <li class="nav-item">
                @can('holiday-list')
                    <a href="{{ route('holiday.index') }}" class="nav-link">
                        <i class="fa fa-calendar-check-o menu-icon"></i>
                        <span class="menu-title">{{ __('holiday_list') }}</span>  </a>
                @endcan
            </li>
        @endcanany

        {{-- Events--}}
        @canany(['event-create'])
            <li class="nav-item">
                @can('holiday-list')
                    <a href="{{route('events.index')}}" class="nav-link">
                        <i class="fa fa-list-ul menu-icon"></i>
                        <span class="menu-title">{{ __('events') }}</span>  </a>
                @endcan
            </li>
        @endcanany

        {{-- session-year --}}
        @can('session-year-create')
            <li class="nav-item">
                <a href="{{ route('session-years.index') }}" class="nav-link">
                    <i class="fa fa-calendar-o menu-icon" style="margin: 0 1px 0 1px"></i>
                    <span class="menu-title">{{ __('session_years') }}</span>
                </a>
            </li>
        @endcan


        {{-- web-settings --}}
        @canany(['content-create','event-create','program-create', 'media-create', 'faq-create','contact-us'])
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#web-settings" aria-expanded="false"
            aria-controls="settings-menu"><i class="fa fa-wrench menu-icon" style="margin: 0 2px 0 2px"></i>
            <span class="menu-title">{{ __('web_settings') }}</span>
            <i class="fa fa-angle-left menu-arrow"></i>
            </a>
            <div class="collapse" id="web-settings">
                <ul class="nav flex-column sub-menu">
                    @can('content-create')
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('content.index')}}">{{ __('content_settings') }}</a>
                    </li>
                    @endcan
                    @can('program-create')
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('educational.index')}}">{{ __('educational_program') }}</a>
                        </li>
                    @endcan
                    @can('media-create')
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('photo.index')}}">{{ __('photos') }}</a>
                        </li>
                    @endcan
                    @can('media-create')
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('video.index')}}"> {{ __('videos') }}</a>
                        </li>
                    @endcan
                    @can('faq-create')
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('faq.index')}}"> {{ __('faqs') }}</a>
                        </li>
                    @endcan
                    @can('contact-us')
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('contact_us.index')}}"> {{ __('contact_us') }}</a>
                    </li>
                @endcan
                </ul>
            </div>
        </li>
        @endcan


        {{-- settings --}}
        @canany(['setting-create', 'fcm-setting-create', 'email-setting-create', 'privacy-policy', 'contact-us',
            'about-us'])
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#settings-menu" aria-expanded="false"
                    aria-controls="settings-menu"><i class="fa fa-gear menu-icon" style="margin: 0 2px 0 2px"></i>
                    <span class="menu-title">{{ __('system_settings') }}</span>
                    <i class="fa fa-angle-left menu-arrow"></i>
                </a>
                <div class="collapse" id="settings-menu">
                    <ul class="nav flex-column sub-menu">
                        @can('setting-create')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('app-settings') }}">
                                    {{ __('app_settings') }}</a>
                            </li>
                        @endcan
                        @can('setting-create')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('settings') }}">
                                    {{ __('general_settings') }}</a>
                            </li>
                        @endcan
                        @can('language-create')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('language') }}">
                                    {{ __('language_settings') }}</a>
                            </li>
                        @endcan
                        @can('fcm-setting-create')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('fcm-settings') }}"> {{__('notification').' '. __('setting')}}
                                </a>
                            </li>
                        @endcan
                        @can('fees-config')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('fees.config.index') }}"> {{ __('fees') }}
                                    {{ __('configration') }}
                                </a>
                            </li>
                        @endcan
                        @can('email-setting-create')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('email-settings') }}">
                                    {{ __('email_configuration') }}
                                </a>
                            </li>
                        @endcan
                        @can('privacy-policy')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('privacy.index') }}">
                                    {{ __('privacy_policy') }}
                                </a>
                            </li>
                        @endcan
                        @can('contact-us')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('contact-us') }}"> {{ __('contact_us') }}
                                </a>
                            </li>
                        @endcan
                        @can('about-us')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('about-us') }}"> {{ __('about_us') }}
                                </a>
                            </li>
                        @endcan
                        @can('terms-condition')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('terms-condition') }}">
                                    {{ __('terms_condition') }}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endif

            @if (Auth::user()->hasRole('Super Admin'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('system-update.index') }}">
                        <i class="fa fa-cloud-download menu-icon"></i>
                        <span class="menu-title">{{ __('system_update') }}</span>
                    </a>
                </li>
            @endif

        </ul>
    </nav>
