@foreach($dataValues as $gateway)
                                                
<div class="col-12 col-md-6 mb-30">
    <div class="card">
        <div class="card-header">
            <h4 class="page-title">{{translate($gateway->key_name)}}</h4>
        </div>
        <div class="card-body p-30">
            <form
                action="{{route('admin.configuration.payment-set')}}"
                method="POST"
                id="{{$gateway->key_name}}-form"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @php($additional_data = $gateway['additional_data'] != null ? json_decode($gateway['additional_data']) : [])
                <div class="discount-type">
                    <div
                        class="d-flex align-items-center gap-4 gap-xl-5 mb-30">
                        <div class="custom-radio">
                            <input type="radio"
                                   id="{{$gateway->key_name}}-active"
                                   name="status"
                                   value="1" {{$dataValues->where('key_name',$gateway->key_name)->first()->live_values['status']?'checked':''}} {{$isPublished}}>
                            <label
                                for="{{$gateway->key_name}}-active">{{translate('active')}}</label>
                        </div>
                        <div class="custom-radio">
                            <input type="radio"
                                   id="{{$gateway->key_name}}-inactive"
                                   name="status"
                                   value="0" {{$dataValues->where('key_name',$gateway->key_name)->first()->live_values['status']?'':'checked'}} {{$isPublished}}>
                            <label
                                for="{{$gateway->key_name}}-inactive">{{translate('inactive')}}</label>
                        </div>
                    </div>

                    <div
                        class="payment--gateway-img justify-content-center d-flex align-items-center">
                        <img class="payment-image-preview"
                             id="{{$gateway->key_name}}-image-preview"
                             src="{{ onErrorImage(
                                    $additional_data != null ? $additional_data->gateway_image : '',
                                    asset('storage/app/public/payment_modules/gateway_image').'/' . ($additional_data != null ? $additional_data->gateway_image : ''),
                                    asset('public/assets/admin-module/img/placeholder.png') ,
                                    'payment_modules/gateway_image/'
                                 ) }}"
                             alt="{{ translate('image') }}">
                    </div>

                    <input name="gateway"
                           value="{{$gateway->key_name}}"
                           class="hide-div">

                    @php($mode=$dataValues->where('key_name',$gateway->key_name)->first()->live_values['mode'])
                    <div class="form-floating mb-30 mt-30">
                        <select
                            class="js-select theme-input-style w-100"
                            name="mode" {{$isPublished}}>
                            <option
                                value="live" {{$mode=='live'?'selected':''}}>{{translate('live')}}</option>
                            <option
                                value="test" {{$mode=='test'?'selected':''}}>{{translate('test')}}</option>
                        </select>
                    </div>

                    @php($skip=['gateway','mode','status'])
                    @foreach($dataValues->where('key_name',$gateway->key_name)->first()->live_values as $key=>$value)
                        @if(!in_array($key,$skip))
                            <div
                                class="form-floating mb-30 mt-30">
                                <input type="text"
                                       class="form-control"
                                       name="{{$key}}"
                                       placeholder="{{translate($key)}} *"
                                       value="{{env('APP_ENV')=='demo'?'':$value}}" {{$isPublished}}>
                                <label>{{translate($key)}}
                                    *</label>
                            </div>
                        @endif
                    @endforeach

                    @if($gateway['key_name'] == 'paystack')
                        <div class="form-floating mb-30 mt-30">
                            <input type="text"
                                   class="form-control"
                                   placeholder="{{translate('Callback Url')}} *"
                                   readonly
                                   value="{{env('APP_ENV')=='demo'?'': route('paystack.callback')}}" {{$isPublished}}>
                            <label>{{translate('Callback Url')}}
                                *</label>
                        </div>
                    @endif

                    <div class="form-floating gateway-title">
                        <input type="text" class="form-control"
                               id="{{$gateway->key_name}}-title"
                               name="gateway_title"
                               placeholder="{{translate('payment_gateway_title')}}"
                               value="{{$additional_data != null ? $additional_data->gateway_title : ''}}" {{$isPublished}}>
                        <label
                            for="{{$gateway->key_name}}-title"
                            class="form-label">{{translate('payment_gateway_title')}}</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="file" class="form-control"
                               name="gateway_image"
                               accept=".jpg, .png, .jpeg|image/*"
                               id="{{$gateway->key_name}}-image">
                    </div>

                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit"
                            class="btn btn--primary demo_check" {{$isPublished}}>
                        {{translate('update')}}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach