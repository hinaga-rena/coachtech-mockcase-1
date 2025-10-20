<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    /**
     * 認可の確認
     */
    public function authorize(): bool
    {
        // コントローラで当事者チェックしているので true にしてOK
        return true;
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:400'],
            'image'   => ['nullable', 'image', 'mimes:jpeg,png'],
        ];
    }

    /**
     * エラーメッセージ
     */
    public function messages(): array
    {
        return [
            'content.required' => '本文を入力してください',
            'content.max'      => '本文は400文字以内で入力してください',
            'image.mimes'      => '「.png」または「.jpeg」形式でアップロードしてください',
        ];
    }

    /**
     * 属性名（日本語化）
     */
    public function attributes(): array
    {
        return [
            'content' => '本文',
            'image'   => '画像',
        ];
    }
}