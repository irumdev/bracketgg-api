<?php

declare(strict_types=1);

namespace App\Http\Requests\Channel;

use App\Models\User;
use Illuminate\Validation\Rule;
use App\Helpers\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Channel\BannerImage as ChannelBannerImage;
use App\Models\Channel\Channel;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator as ValidContract;
use App\Helpers\ValidMessage;
use Symfony\Component\HttpFoundation\Response;

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
    private Channel $channel;
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
        $this->canUpdate = $this->user->can('updateChannel', [
            $this->channel = $this->route('slug')
        ]);
        return $this->canUpdate;
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
                'channelHasOnlyOneBanner'
            ],
            'banner_image_id' => [
                'nullable',
                'numeric',
                Rule::exists((new ChannelBannerImage())->getTable(), 'id')->where(function (Builder $query) {
                    $query->where('channel_id', $this->channel->id);
                }),
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'banner_image.required' => json_encode(['code' => self::BANNER_UPLOAD_FILE_IS_NOT_ATTACHED]),
            'banner_image.image' => json_encode(['code' => self::BANNER_UPLOAD_FILE_IS_NOT_IMAGE]),
            'banner_image.file' => json_encode(['code'  => self::BANNER_UPLOAD_IS_NOT_FULL_UPLOADED_FILE]),
            'banner_image.mimes' => json_encode(['code' => self::BANNER_UPLOAD_FILE_MIME_IS_NOT_MATCH]),
            'banner_image.max' => json_encode(['code'   => self::BANNER_UPLOAD_FILE_IS_LARGE]),

            'banner_image_id.numeric' => json_encode(['code' => self::BANNER_IMAGE_ID_IS_NOT_NUMERIC]),
            'banner_image_id.exists' => json_encode(['code' => self::BANNER_IMAGE_ID_IS_NOT_EXISTS]),
            'banner_image.channel_has_only_one_banner' => json_encode(['code' => self::BANNER_UPLOAD_FILE_HAS_MANY_BANNER]),
        ];
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

    private function errorResponse($errorMessage, int $httpStatus = Response::HTTP_UNPROCESSABLE_ENTITY)
    {
        throw new HttpResponseException(
            $this->responseBuilder->fail($errorMessage, $httpStatus)
        );
    }
}
