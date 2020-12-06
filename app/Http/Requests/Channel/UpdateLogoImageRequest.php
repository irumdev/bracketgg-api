<?php

declare(strict_types=1);

namespace App\Http\Requests\Channel;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator as ValidContract;
use Symfony\Component\HttpFoundation\Response;

use App\Models\User;
use App\Models\Channel\Channel;

use App\Helpers\ValidMessage;
use App\Helpers\ResponseBuilder;

class UpdateLogoImageRequest extends FormRequest
{
    private User $user;
    private Channel $channel;
    private ResponseBuilder $responseBuilder;

    public const PROFILE_UPLOAD_FILE_IS_NOT_IMAGE = 1;
    public const PROFILE_UPLOAD_FILE_MIME_IS_NOT_MATCH = 2;
    public const PROFILE_UPLOAD_FILE_IS_LARGE = 3;
    public const PROFILE_UPLOAD_IS_NOT_FULL_UPLOADED_FILE = 4;

    public function __construct(ResponseBuilder $responseBuilder)
    {
        $this->responseBuilder = $responseBuilder;
        $this->user = Auth::user();
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $this->channel = $this->route('slug');
        return $this->user->can('updateChannel', [
            $this->channel,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'logo_image' => 'nullable|file|image|mimes:jpeg,jpg,png|max:2048',
        ];
    }

    protected function failedValidation(ValidContract $validator): void
    {
        $this->errorResponse(ValidMessage::first($validator));
    }

    protected function failedAuthorization(): void
    {
        $this->errorResponse([
            'code' => Response::HTTP_UNAUTHORIZED,
        ], Response::HTTP_UNAUTHORIZED);
    }


    private function errorResponse($errorMessage, int $httpStatus = Response::HTTP_UNPROCESSABLE_ENTITY): void
    {
        throw new HttpResponseException(
            $this->responseBuilder->fail($errorMessage, $httpStatus)
        );
    }

    public function messages(): array
    {
        return [
            'logo_image.image' => json_encode(['code' => self::PROFILE_UPLOAD_FILE_IS_NOT_IMAGE]),
            'logo_image.file' => json_encode(['code' => self::PROFILE_UPLOAD_IS_NOT_FULL_UPLOADED_FILE]),
            'logo_image.mimes' => json_encode(['code' => self::PROFILE_UPLOAD_FILE_MIME_IS_NOT_MATCH]),
            'logo_image.max' => json_encode(['code' => self::PROFILE_UPLOAD_FILE_IS_LARGE]),
        ];
    }
}
