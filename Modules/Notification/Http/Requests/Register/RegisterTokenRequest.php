<?php
namespace Modules\Notification\Http\Requests\Register;

use MyCore\Http\Request\BaseFormRequest;

/**
 * Class RegisterTokenRequest
 * @package Modules\Notification\Http\Requests\Register
 * @author DaiDP
 * @since Aug, 2020
 */
class RegisterTokenRequest extends BaseFormRequest
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
            'staff_id'      => 'required|int',
            'platform'     => 'required|in:android,ios',
            'token' => 'required',
            'imei'         => 'required'
        ];
    }

    /**
     * Customize message
     *
     * @return array
     */
    public function messages()
    {
        return [
            'staff_id.required'      => __('ID user là thông tin bắt buộc'),
            'staff_id.integer'       => __('ID user không đúng định dạng'),
            'platform.required'     => __('Platform là thông tin bắt buộc'),
            'platform.in'           => __('Platform chỉ cho phép android và ios'),
            'token.required' => __('Token là thông tin bắt buộc'),
            'imei.required'         => __('IMEI là thông tin bắt buộc'),
        ];
    }

    /**
     *  Filters to be applied to the input.
     *
     * @return array
     */
    public function filters()
    {
        return [
            'staff_id'       => 'strip_tags|trim',
            'platform'      => 'strip_tags|trim',
            'token'  => 'strip_tags|trim',
            'imei'          => 'strip_tags|trim',
        ];
    }
}
