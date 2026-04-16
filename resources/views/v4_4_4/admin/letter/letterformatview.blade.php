<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>
        {{ config('app.name') }} - {{ $letterdetails->letter_name ?? 'Letter' }} - Letter Format Preview
    </title>

     {{-- <!-- <link rel="stylesheet" href="{{ asset('admin/css/bootstrap.min.css') }}">  --> --}}
    <link rel="stylesheet" href="{{ public_path('admin/css/bootstrap.min.css') }}">
    <style>
        @page {
            margin-top: 170px;
            margin-right: 35px;
            margin-bottom: 170px;
            margin-left: 35px;
        }

        body {
            margin: 0;
            padding: 0;
        }

        .page-header {
            position: fixed;
            top: -150px;
            left: 0;
            right: 0;
            height: 150px;
            overflow: hidden;
        }

        .page-footer {
            position: fixed;
            bottom: -150px;
            left: 0;
            right: 0;
            height: 150px;
            overflow: hidden;
        }

        .letter-body {
            width: 100%;
            
        }

        .letter-body>*:first-child {
            margin-top: 0 !important;
        }

        .letter-body>*:last-child {
            margin-bottom: 0 !important;
        }
    </style>
</head>

<body>
    @php
       
    @endphp
    <div class="page-header">
        <table width="100%" style="border-collapse: collapse;">
            <tr>
              
                @if ($letterdetails->header_image)
                    <td width="{{ $letterdetails->header_width }}%" valign="middle">
                        <img src="{{ $letterdetails->header_image }}"
                            style="max-height: {{ $letterdetails->header_width * 3 }}px;">
                    </td>
                @endif

                <td width="{{ 100 - $letterdetails->header_width }}%"
                    style="text-align: {{ $letterdetails->header_align }};" valign="middle">
                    {!! $letterdetails->header_content !!}
                </td>
            </tr>
        </table>
    </div>

    <div class="page-footer">
        <table width="100%" style="border-collapse: collapse;">
            <tr>
                <td width="{{ 100 - $letterdetails->footer_width }}%"
                    style="text-align: {{ $letterdetails->footer_align }};" valign="middle">
                    {!! $letterdetails->footer_content !!}
                </td>
             
                @if ($letterdetails->footer_image)
                    <td width="{{ $letterdetails->footer_width }}%" valign="middle">
                        <img src="{{ $letterdetails->footer_image }}"
                            style="max-height: {{ $letterdetails->footer_width * 3 }}px;">
                    </td>
                @endif
            </tr>
        </table>
    </div>

    <div class="letter-body" id="letter-body">
        {!! $letterdetails->body_content !!}
    </div>
</body>

</html>
