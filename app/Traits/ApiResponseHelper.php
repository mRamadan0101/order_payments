<?php

namespace App\Traits;

use Response;

trait ApiResponseHelper
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var array
     */
    protected $body;

    /**
     * Set response data.
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->body['data'] = $data;

        return $this;
    }

    public function setError($error)
    {
        $this->body['status'] = 'error';
        $this->body['message'] = $error;

        return $this;
    }

    public function setSuccess($message)
    {
        $this->body['status'] = 'success';
        $this->body['message'] = $message;

        return $this;
    }

    public function setCode($code)
    {
        $this->body['code'] = $code;

        return $this;
    }

    public function send()
    {
        return response()->json($this->body, $this->body['code'] ?? 200);
    }

    public function sendCollection($collection, $code)
    {
        return response()->json($collection, 200);
    }

    public function validate_api($inputs, $rules)
    {
        $validator = \Validator::make($inputs, $rules);

        return $validator;
    }
}
