<?php
$dataformate = $data['dataformate'];
$letterdata = $data['letterdata'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config('app.name') }} - Letter</title>
    <!-- Favicon -->
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
            @if ($dataformate['header_image'])
                <td width="{{ $dataformate['header_width'] }}%" valign="middle">
                    <img src="{{ public_path($dataformate['header_image']) }}"
                        style="max-height: {{ $dataformate['header_width'] * 3 }}px;">
                    {{-- <img src="{{ asset($dataformate['header_image']) }}"
                        style="max-height: {{ $dataformate['header_width'] * 3 }}px;"> --}}
                </td>
            @endif

            <td width="{{ 100 - $dataformate['header_width'] }}%"
                style="text-align: {{ $dataformate['header_align'] }};" valign="middle">
                {!! $dataformate['header_content'] !!}
            </td>
        </tr>
    </table>

    <hr>

    @php
        $letterTemplate = $dataformate['body_content'];

        $data = json_decode($letterdata['letter_value']);

        foreach ($data as $key => $value) {
            $letterTemplate = str_replace('$' . $key, (string) $value, $letterTemplate);
        }

    @endphp
    <div class="mt-3 letter-body" id="letter-body">
        {!! $letterTemplate !!}
    </div>
    <hr>
    <table width="100%" style="border-collapse: collapse;">
        <tr>
            <td width="{{ 100 - $dataformate['footer_width'] }}%"
                style="text-align: {{ $dataformate['footer_align'] }};" valign="middle">
                {!! $dataformate['footer_content'] !!}
            </td>

            @if ($dataformate['footer_image'])
                <td width="{{ $dataformate['footer_width'] }}%" valign="middle">
                    <img src="{{ public_path($dataformate['footer_image']) }}"
                        style="max-height: {{ $dataformate['footer_width'] * 3 }}px;">
                    {{-- <img src="{{ asset($dataformate['footer_image']) }}"
                        style="max-height: {{ $dataformate['footer_width'] * 3 }}px;"> --}}
                </td>
            @endif
        </tr>
    </table>
</body>

</html>
