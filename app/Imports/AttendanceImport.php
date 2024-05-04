<?php

namespace App\Imports;

use App\Models\Students;
use App\Models\Attendance;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AttendanceImport implements ToCollection, WithHeadingRow
{
    public $class_section_id,$date;
    /**
    * @param Collection $collection
    */
    public function  __construct($class_section_id)
    {
        $this->class_section_id = $class_section_id;
    }

    public function collection(Collection $rows)
    {
        $validator= Validator::make($rows->toArray(),[
            '*.student_id' => 'required|numeric',
            '*.date' => 'required',
            '*.type' => 'required|in:0,1',
        ], [
            '*.type' => 'The type must be either 0 or 1.'
        ]);
        $validator->validate();
        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first()
            );
            return response()->json($response);
        }

        foreach($rows as $row)
        {
            $session_year = getSettings('session_year');
            $date = date('Y-m-d', strtotime($row['date']));

            $attendance = Attendance::where('date', $date)
                ->where('class_section_id', $this->class_section_id)
                ->where('student_id', $row['student_id'])
                ->first();

            if ($attendance) {
                $attendance->type = $row['type'];
                $attendance->save();
            } else {
                $attendance = new Attendance();
                $attendance->class_section_id = $this->class_section_id;
                $attendance->student_id = $row['student_id'];
                $attendance->session_year_id = $session_year['session_year'];
                $attendance->date = $date;
                $attendance->type = $row['type'];
                $attendance->save();
            }

        }
    }
}
