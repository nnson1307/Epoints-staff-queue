<?php
namespace MyCore\Http\Request;

use Illuminate\Foundation\Http\FormRequest;
use Waavi\Sanitizer\Laravel\SanitizesInput;

/**
 * Class BaseFormRequest
 * @package MyCore\Http\Request
 * @since Aug, 2019
 * @see https://medium.com/@kamerk22/the-smart-way-to-handle-request-validation-in-laravel-5e8886279271
 */
abstract class BaseFormRequest extends FormRequest
{
    use SanitizesInput;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    abstract public function authorize();

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    abstract public function rules();
}