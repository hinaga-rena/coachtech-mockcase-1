<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    /**
     * 認可
     */
    public function authorize(): bool
    {
        // 関係者チェックはコントローラで行うので true でOK
        return true;
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:400'],
            // ← image ルールは外し、mimes だけにする（jpg も許可）
            'image'   => ['nullable', 'mimes:jpg,jpeg,png'],
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
     * 属性名
     */
    public function attributes(): array
    {
        return [
            'content' => '本文',
            'image'   => '画像',
        ];
    }
}