<?php 

namespace App\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;

trait PreventsRedirectWhenFailedTrait
{
    /**
     * Default self::failedValidation() Laravel behavior flag.
     *
     * @var bool
     */
    private $defaultFailedValidationRestored = false;

    /**
     * Check for validator success flag.
     *
     * @return bool
     */
    public function validatorPasses(): bool
    {
        return !$this->validatorFails();
    }

    /**
     * Check for validator fail flag.
     *
     * @return bool
     */
    public function validatorFails(): bool
    {
        return $this->getValidatorInstance()->fails();
    }

    /**
     * @return MessageBag
     */
    public function validatorErrors(): MessageBag
    {
        return $this->getValidatorInstance()->errors();
    }

    /**
     * Respond with validator errors in JSON format.
     *
     * @param  int  $code
     */
    public function respondWithErrorsJson(int $code = 422): void
    {
        if ($this->validatorFails()) {
            throw new HttpResponseException(
                response()->json(['errors' => $this->getValidatorInstance()->errors()], $code)
            );
        }
    }

    /**
     * Restore and apply default self::failedValidation() method behavior.
     *
     * @throws ValidationException
     */
    public function redirectWithErrors(): void
    {
        $this->defaultFailedValidationRestored = true;

        $this->failedValidation($this->getValidatorInstance());
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator): void
    {
        if ($this->defaultFailedValidationRestored) {
            throw (new ValidationException($validator))
                ->errorBag($this->errorBag)
                ->redirectTo($this->getRedirectUrl());
        }
    }
}