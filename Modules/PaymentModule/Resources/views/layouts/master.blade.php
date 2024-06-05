<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Module PaymentModule</title>
        <!-- Import FawryPay CSS Library-->
<link rel="stylesheet" href="https://atfawry.fawrystaging.com/atfawry/plugin/assets/payments/css/fawrypay-payments.css">

        @stack('script')
    </head>
    <body>
        <script>
            localStorage.theme && document.querySelector('body').setAttribute("theme", localStorage.theme);
            localStorage.dir && document.querySelector('html').setAttribute("dir", localStorage.dir);
        </script>
        @yield('content')

    </body>
</html>
