<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>
        {{ config('app.name') }} - {{ $letterdetails->letter_name ?? 'Letter' }} - Letter Format Preview
    </title>

    {{-- <link rel="stylesheet" href="{{ asset('admin/css/bootstrap.min.css') }}"> --}}
    <link rel="stylesheet" href="{{ public_path('admin/css/bootstrap.min.css') }}">
    <style>
        div#letter-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        div#letter-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
    </style>
</head>

<body>

    <table width="100%" style="border-collapse: collapse;">
        <tr>
            @if ($letterdetails->header_image)
                <td width="{{ $letterdetails->header_width }}%" valign="middle">
                    <img src="{{ public_path($letterdetails->header_image) }}"
                        style="max-height: {{ $letterdetails->header_width * 3 }}px;">
                    {{-- <img src="{{ asset($letterdetails->header_image) }}"
                        style="max-height: {{ $letterdetails->header_width * 3 }}px;"> --}}
                </td>
            @endif

            <td width="{{ 100 - $letterdetails->header_width }}%"
                style="text-align: {{ $letterdetails->header_align }};" valign="middle">
                {!! $letterdetails->header_content !!}
            </td>
        </tr>
    </table>

    <hr>


    <div class="mt-3 letter-body" id="letter-body">
        {!! $letterdetails->body_content !!}
    </div>
    <hr>
    <table width="100%" style="border-collapse: collapse;">
        <tr>
            <td width="{{ 100 - $letterdetails->footer_width }}%"
                style="text-align: {{ $letterdetails->footer_align }};" valign="middle">
                {!! $letterdetails->footer_content !!}
            </td>

            @if ($letterdetails->footer_image)
                <td width="{{ $letterdetails->footer_width }}%" valign="middle">
                    <img src="{{ public_path($letterdetails->footer_image) }}"
                        style="max-height: {{ $letterdetails->footer_width * 3 }}px;">
                    {{-- <img src="{{ asset($letterdetails->footer_image) }}"
                        style="max-height: {{ $letterdetails->footer_width * 3 }}px;"> --}}
                </td>
            @endif
        </tr>
    </table>
</body>

</html>
