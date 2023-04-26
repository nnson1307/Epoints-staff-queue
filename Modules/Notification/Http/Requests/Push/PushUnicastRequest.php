<?php
namespace Modules\Notification\Http\Requests\Push;

use MyCore\Http\Request\BaseFormRequest;

/**
 * Class RegisterTokenRequest
 * @package Modules\Notification\Http\Requests\Register
 * @author DaiDP
 * @since Aug, 2020
 */
class PushUnicastRequest extends BaseFormRequest
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
            'staff_id'       => 'required', //|int
            'detail_id'     => 'nullable',
            'title'         => 'required',
            'message'       => 'required',
            'avatar'        => 'nullable',
            'schedule'        => 'nullable',
            'background'        => 'nullable'
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
            'avatar'        => 'strip_tags|trim',
            'schedule'  => 'strip_tags|trim',
            'background'          => 'strip_tags|trim',
        ];
    }
}
