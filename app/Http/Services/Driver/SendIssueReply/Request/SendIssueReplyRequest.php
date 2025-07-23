<?php
namespace App\Http\Services\Driver\SendIssueReply\Request;

use App\Http\Core\Request\BaseRequest;

class SendIssueReplyRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }



    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return
        [
            'content'=>'required',
            'image'=>'nullable',
            'issueId'=>'required',
        ];
    }

}
