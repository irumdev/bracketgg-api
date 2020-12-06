<?php

declare(strict_types=1);

namespace App\Http\Requests\Team;

use App\Models\User;
use App\Models\Team\Team;
use App\Helpers\ValidMessage;
use Illuminate\Validation\Rule;
use App\Helpers\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Team\BannerImage as TeamBannerImage;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator as ValidContract;

class UpdateBannerImageRequest extends FormRequest
{
    public const BANNER_UPLOAD_FILE_IS_NOT_IMAGE = 1;
    public const BANNER_UPLOAD_FILE_MIME_IS_NOT_MATCH = 2;
    public const BANNER_UPLOAD_FILE_IS_LARGE = 3;
    public const BANNER_UPLOAD_IS_NOT_FULL_UPLOADED_FILE = 4;
    public const BANNER_IMAGE_ID_IS_NOT_NUMERIC = 5;
    public const BANNER_IMAGE_ID_IS_NOT_EXISTS = 6;
    public const BANNER_UPLOAD_FILE_IS_NOT_ATTACHED = 7;
    public const BANNER_UPLOAD_FILE_HAS_MANY_BANNER = 8;

    private User $user;
    private Team $team;
    private bool $canUpdate;

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
        $this->canUpdate = $this->user->can('updateTeam', $this->team);
        return $this->canUpdate;
    }

    protected function failedAuthorization(): void
    {
        $this->errorResponse([
            'code' => Response::HTTP_UNAUTHORIZED,
        ], Response::HTTP_UNAUTHORIZED);
    }

    protected function failedValidation(ValidContract $validator): void
    {
        $this->errorResponse(ValidMessage::first($validator));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'banner_image' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,jpg,png',
                'max:2048',
                'teamHasOnlyOneBanner'
            ],
            'banner_image_id' => [
                'nullable',
                'numeric',
                Rule::exists((new TeamBannerImage())->getTable(), 'id')->where(function (Builder $query) {
                    $query->where('team_id', $this->team->id);
                }),
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'banner_image.required' => json_encode(['code' => self::BANNER_UPLOAD_FILE_IS_NOT_ATTACHED]),
            'banner_image.file' => json_encode(['code' => self::BANNER_UPLOAD_IS_NOT_FULL_UPLOADED_FILE]),
            'banner_image.image' => json_encode(['code' => self::BANNER_UPLOAD_FILE_IS_NOT_IMAGE]),
            'banner_image.mimes' => json_encode(['code' => self::BANNER_UPLOAD_FILE_MIME_IS_NOT_MATCH]),
            'banner_image.max' => json_encode(['code' => self::BANNER_UPLOAD_FILE_IS_LARGE]),
            'banner_image.team_has_only_one_banner' => json_encode(['code' => self::BANNER_UPLOAD_FILE_HAS_MANY_BANNER]),

            'banner_image_id.numeric' => json_encode(['code' => self::BANNER_IMAGE_ID_IS_NOT_NUMERIC]),
            'banner_image_id.exists' => json_encode(['code' => self::BANNER_IMAGE_ID_IS_NOT_EXISTS]),

        ];
    }

    private function errorResponse($errorMessage, int $httpStatus = Response::HTTP_UNPROCESSABLE_ENTITY): void
    {
        throw new HttpResponseException(
            $this->responseBuilder->fail($errorMessage, $httpStatus)
        );
    }
}
