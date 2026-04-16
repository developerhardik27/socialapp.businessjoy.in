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
</head>

<body>

    <div id="letter-header" class="d-flex   ">
        @if ($dataformate['header_image'])
            <div class="header-image"
                style="flex: 0 0 {{ $dataformate['header_width'] }}%; text-align: {{ $dataformate['header_align'] }};">
                
                <img src="{{ public_path($dataformate['header_image']) }}" class="img-fluid"
                    style="display: inline-block;  height: auto; max-height: {{ $dataformate['header_width'] * 3 }}px;">
                {{-- <img src="{{ asset($dataformate['header_image']) }}" class="img-fluid"
                    style="display: inline-block;  height: auto; max-height: {{ $dataformate['header_width'] * 3 }}px;"> --}}
            </div>
        @endif
        <div class="header-content flex-grow-1 ms-3"
            style="
                    @if ($dataformate['header_align'] == 'left') text-align: right; 
                    @elseif($dataformate['header_align'] == 'right') 
                        text-align: left; 
                    @elseif($dataformate['header_align'] == 'center') 
                        display: none; @endif
                ">
            {!! $dataformate['header_content'] !!}
        </div>
    </div>
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

    <div id="letter-footer">
        <div class="footer-content flex-grow-1 ms-3">
            {!! $dataformate['footer_content'] !!}
        </div>
        @if ($dataformate['footer_image'])
            <div class="footer-image"
                style="flex: 0 0 {{ $dataformate['footer_width'] }}%; text-align: {{ $dataformate['footer_align'] }};">

                <img src="{{ public_path($dataformate['footer_image']) }}" class="img-fluid"
                    style="display: inline-block;  height: auto; max-height: {{ $dataformate['footer_width'] * 3 }}px;">
                {{-- <img src="{{ asset($dataformate['footer_image']) }}" class="img-fluid"
                    style="display: inline-block;  height: auto; max-height: {{ $dataformate['footer_width'] * 3 }}px;"> --}}
            </div>
        @endif

    </div>
</body>

</html>
