<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class DetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'new_clock_in' => 'required|date_format:H:i',
            'new_clock_out' => 'required|date_format:H:i',
            'breaks.*.start' => 'nullable|date_format:H:i',
            'breaks.*.end' => 'nullable|date_format:H:i',
            'remarks' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'remarks.required' => '備考を記入してください',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $start = Carbon::parse($this->new_clock_in);
            $end = Carbon::parse($this->new_clock_out);
            if ($start && $end && $start >= $end) {
                $validator->errors()->add('new_clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            $breaks = $this->input('breaks', []);

            foreach ($breaks as $index => $break) {
                $breakStart = isset($break['start']) && $break['start'] ? Carbon::parse($break['start']) : null;
                $breakEnd = isset($break['end']) && $break['end'] ? Carbon::parse($break['end']) : null;

                if (
                    ($breakStart && ($breakStart->lt($start) || $breakStart->gt($end))) || ($breakEnd && ($breakEnd->lt($start) || $breakEnd->gt($end))) 
                ){
                    $validator->errors()->add("breaks.{$index}", "休憩時間が勤務時間外です");
                }
            }
        });
    }
}
