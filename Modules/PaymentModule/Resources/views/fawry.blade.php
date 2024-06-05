<!DOCTYPE html>
<html lang="en">



 <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <!-- Import FawryPay CSS Library-->
    <link rel="stylesheet"
        href="https://atfawry.fawrystaging.com/atfawry/plugin/assets/payments/css/fawrypay-payments.css">
    <!-- Import FawryPay Staging JavaScript Library-->




</head>

<body>
    @php
    session(['payment_id'=>request()->payment_id]);
    @endphp



    <!-- FawryPay Checkout Button -->


<script type="text/javascript"
        src="https://atfawry.fawrystaging.com/atfawry/plugin/assets/payments/js/fawrypay-payments.js"></script>

<script src="https://cdn.jsdelivr.net/npm/js-sha256@0.11.0/src/sha256.min.js"></script>

  <script src="
https://cdn.jsdelivr.net/npm/js-sha256@0.11.0/src/sha256.min.js
"></script>

    <script>


        function checkout() {
            const configuration = {
                locale: "en", //default en
                mode: DISPLAY_MODE.SEPARATED, //required, allowed values [SEPARATED,POPUP, INSIDE_PAGE, SIDE_PAGE]
            };
            FawryPay.checkout(buildChargeRequest(), configuration);
        }


  //var x = "770000019015"+"{{strval($paramList['ORDER_ID'])}}"+"https://fawrydeveloper.com/"+"1234"+"1"+"{{strval($paramList['TXN_AMOUNT'])}}"+"dee548d0-39d2-446a-91b8-6b032b2226f1"
    var x = "770000019015"+"{{strval($paramList['ORDER_ID'])}}"+"{{ route('fawry.responseee') }}"+"1234"+"1"+"{{strval(number_format($paramList['TXN_AMOUNT'], 2, '.', ''))}}"+"dee548d0-39d2-446a-91b8-6b032b2226f1";
        function buildChargeRequest() {
            const chargeRequest = {"{{ $paramList['CUSTOMER_NAME'] }}",
                merchantCode: "770000019015",
              	merchantName:"ahmed",
                merchantRefNum: "{{strval($paramList['ORDER_ID'])}}",
                customerMobile: "{{$paramList['MSISDN']}}",
                customerEmail: "{{$paramList['EMAIL']}}",
                customerName: "{{ $paramList['CUSTOMER_NAME'] }}",
                paymentExpiry: "",
                customerProfileId: "",
                language: "en-gb",
              	logo:"",
                chargeItems: [{
                    itemId: "1234",
                    description: "1234",
                    price: "{{strval($paramList['TXN_AMOUNT'])}}",
                    quantity: "1",
                }],
                paymentMethod: '',
                returnUrl: "{{ route('fawry.responseee') }}",
               // signature: "56850f6c37b2c9ded5c33fb9b71bb03a3c98436f5b9fe1d03468d80fbe902a9c",
              signature: sha256(x)
            };
            return chargeRequest;
        };
  checkout();
    </script>

</body>

</html>
