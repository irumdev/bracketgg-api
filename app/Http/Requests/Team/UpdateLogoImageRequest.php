<?php

declare(strict_types=1);

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\User;
use App\Models\Team\Team;

use App\Helpers\ValidMessage;
use App\Helpers\ResponseBuilder;

use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator as ValidContract;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class UpdateLogoImageRequest extends FormRequest
{
    private User $user;
    private Team $team;
    private ResponseBuilder $responseBuilder;

    public const LOGO_IS_NOT_FILE = 1;
    public const LOGO_IS_NOT_IMAGE = 2;
    public const LOGO_MIME_IS_NOT_MATCH = 3;
    public const LOGO_IS_IMAGE_IS_LARGE = 4;
    public const LOGO_IS_NOT_ATTACHED = 5;

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
        $this->team = $this->route('teamSlug');
        return $this->user->can('updateTeam', $this->team);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'logo_image' => 'required|file|mimes:jpeg,jpg,png|image|max:2048',
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

    public function messages(): array
    {
        return [
            'logo_image.required' => json_encode(['code' => self::LOGO_IS_NOT_ATTACHED]),
            'logo_image.file' => json_encode(['code' => self::LOGO_IS_NOT_FILE]),
            'logo_image.image' => json_encode(['code' => self::LOGO_IS_NOT_IMAGE]),
            'logo_image.mimes' => json_encode(['code' => self::LOGO_MIME_IS_NOT_MATCH]),
            'logo_image.max' => json_encode(['code' => self::LOGO_IS_IMAGE_IS_LARGE]),
        ];
    }

    private function errorResponse($errorMessage, int $httpStatus = Response::HTTP_UNPROCESSABLE_ENTITY): void
    {
        throw new HttpResponseException(
            $this->responseBuilder->fail($errorMessage, $httpStatus)
        );
    }
}
