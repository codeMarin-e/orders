<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use App\Models\Cart;
use App\Models\CartProduct;

class OrderRequest extends FormRequest
{

    private $mergeReturn = [];

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $chOrder = request()->route('chOrder');
        $chOrder->loadMissing('products.owner');
        $rules = [
            'products.*.price' => [ 'numeric' ],
            'products.*.quantity' => [ 'numeric', function($attribute, $value, $fail) use ($chOrder) {
                $attributeParts = explode('.', $attribute);
                $cartProductId = $attributeParts[1];
                if(!($cartProduct = $chOrder->products->find($cartProductId))) {
                    return $fail(trans('admin/orders/validation.order_product_not_found'));
                }
                if(!$cartProduct->owner) return;
                if(!$cartProduct->checkQuantity($value)) {
                    return $fail(trans('admin/orders/validation.not_enough_quantity'));
                }
            }],

            'facAddr.fname' => ['nullable'],
            'facAddr.lname' => ['nullable'],
            'facAddr.email' => ['nullable', 'email'],
            'facAddr.phone' => ['nullable'],
            'facAddr.street' => ['nullable'],
            'facAddr.city' => ['nullable'],
            'facAddr.country' => ['nullable'],
            'facAddr.postcode' => ['nullable'],
            'facAddr.company' => ['nullable'],
            'facAddr.orgnum' => ['nullable'],

            'delAddr.fname' => ['nullable'],
            'delAddr.lname' => ['nullable'],
            'delAddr.email' => ['nullable', 'email'],
            'delAddr.phone' => ['nullable'],
            'delAddr.street' => ['nullable'],
            'delAddr.city' => ['nullable'],
            'delAddr.country' => ['nullable'],
            'delAddr.postcode' => ['nullable'],
            'delAddr.company' => ['nullable'],
            'delAddr.orgnum' => ['nullable'],

            'set_status' => [function($attribute, $value, $fail) {
                if(!isset(Cart::$statuses[ $value ])) {
                    return $fail(trans("admin/orders/validation.no_such_status"));
                }
            }]
        ];

        // @HOOK_REQUEST_RULES

        return $rules;
    }

    public function messages() {
        $return = Arr::dot((array)trans('admin/orders/validation'));

        // @HOOK_REQUEST_MESSAGES

        return $return;
    }

    public function validationData() {
        $inputBag = 'order';
        $this->errorBag = $inputBag;
        $inputs = $this->all();
        if(!isset($inputs[$inputBag])) {
            throw new ValidationException(trans('admin/orders/validation.no_inputs') );
        }
        $chOrder = request()->route('chOrder');
        $chOrder->loadMissing('products.owner');
        foreach((array)$inputs[$inputBag]['products'] as $cartProductId => $cartProductData) {
            $inputs[$inputBag]['products'][$cartProductId]['price'] = (float)str_replace(',', '.', $inputs[$inputBag]['products'][$cartProductId]['price']);
            $inputs[$inputBag]['products'][$cartProductId]['quantity'] = (float)str_replace(',', '.', $inputs[$inputBag]['products'][$cartProductId]['quantity']);
        }

        // @HOOK_REQUEST_PREPARE

        $this->replace($inputs);
        request()->replace($inputs); //global request should be replaced, too
        return $inputs[$inputBag];
    }

    public function validated($key = null, $default = null) {
        $validatedData = parent::validated($key, $default);

        // @HOOK_REQUEST_VALIDATED

        if(is_null($key)) {

            // @HOOK_REQUEST_AFTER_VALIDATED

            return array_merge($validatedData, $this->mergeReturn);
        }

        // @HOOK_REQUEST_AFTER_VALIDATED_KEY

        return $validatedData;
    }
}
