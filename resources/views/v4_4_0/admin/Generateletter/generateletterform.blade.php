@extends(session('folder_name') . '.admin.Layout.masterlayout')

@section('page_title')
    {{ config('app.name') }} - Generate letter
@endsection

@section('title')
    Generate letter
@endsection

@section('form-content')
    @php
        $letter = session('letter_details');
        $employee = session('employee_details');
    @endphp

    <form id="genrateletterform" name="genrateletterform">
        <div class="container p-0 m-0">
            <div class="card shadow">
                @csrf
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mt-3">
                        <input type="hidden" name="token" class="form-control" value="{{ session('api_token') }}"
                            placeholder="token" required />
                        <input type="hidden" value="{{ $user_id }}" class="form-control" name="user_id">
                        <input type="hidden" value="{{ $company_id }}" class="form-control" name="company_id">
                        <input type="hidden" value="" class="form-control" name="letter_id" id="letter_id">
                        <input type="hidden" value="" class="form-control" name="emp_id" id="emp_id">
                        <input type="hidden" value="" class="form-control" name="data_variable" id="data_variable">
                        {{-- <input type="hidden" id="header_width" name="'header_width" value="{{ $letter->header_width }}">
                        <input type="hidden" id="header_image" name="'header_image" value="{{ $letter->header_image }}">
                        <input type="hidden" id="header_align" name="'header_align" value="{{ $letter->header_align }}">
                        <input type="hidden" id="header_content" name="'header_content" value="{{ $letter->header_content }}">
                        <input type="hidden" id="body_content" name="'body_content" value="">
                        <input type="hidden" id="footer_content" name="'footer_content" value="{{ $letter->footer_content }}">
                        <input type="hidden" id="footer_image" name="'footer_image" value="{{ $letter->footer_image }}">
                        <input type="hidden" id="footer_align" name="'footer_align" value="{{ $letter->footer_align }}">
                        <input type="hidden" id="footer_width" name="'footer_width" value="{{ $letter->footer_width }}"> --}}
                        @if ($letter->header_image)
                            <div class="header-image"
                                style="flex: 0 0 {{ $letter->header_width }}%; text-align: {{ $letter->header_align }};">

                                <img src="{{ asset($letter->header_image) }}" class="img-fluid"
                                    style="display: inline-block;  height: auto; max-height: {{ $letter->header_width * 3 }}px;">
                            </div>
                        @endif


                        <div class="header-content flex-grow-1 ms-3" style="text-align: {{ $letter->header_align }};">
                            {!! $letter->header_content !!}
                        </div>
                    </div>
                    <hr>


                    <div class="mt-3 letter-body">
                        {!! $letter->body_content !!}
                    </div>

                    <hr>
                    <div>
                        {!! $letter->footer_content !!}
                    </div>

                    {{-- FOOTER IMAGE --}}
                    @if ($letter->footer_image)
                        <div style="text-align: {{ $letter->footer_align }};">
                              <img src="{{ asset($letter->footer_image) }}" class="img-fluid"
                                    style="display: inline-block;  height: auto; max-height: {{ $letter->footer_width * 3 }}px;">
                        </div>
                    @endif

                </div>
                <div class="form-group">
                    <div class="form-row">
                        <div class="col-sm-12 mr-2">
                            <button id="cancelbtn" type="button" data-toggle="tooltip" data-placement="bottom"
                                data-original-title="Cancel"
                                class="btn btn-secondary btn-rounded float-right mr-2">Cancel</button>
                            <button type="reset" data-toggle="tooltip" data-placement="bottom"
                                data-original-title="Reset Details" class="btn iq-bg-danger float-right mr-2">Reset</button>
                            <button type="submit" data-toggle="tooltip" data-placement="bottom"
                                data-original-title="Submit Details"
                                class="btn btn-primary float-right my-0">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('ajax')
    <script src="{{ asset('admin/js/select2.min.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


    <script>
        var employeeDetails = {!! json_encode(session('employee_details')) !!};
        var letterDetails = {!! json_encode(session('letter_details')) !!};
        var letterContent = letterDetails.body_content;
        let inputData = {};
        $('#emp_id').val(employeeDetails.id);
        $('#letter_id').val(letterDetails.id);
        $(document).ready(function() {
            loaderhide();
            loadLetterVariables();

            // redirect on employee list page on click cancel btn
            $('#cancelbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.generateletter') }}";
            });
        });

        $(document).on('input', '.letter-input', function() {

            let key = $(this).attr('name');
            let value = $(this).val();

            inputData[key] = value;

            updateHiddenInput();
        });

        function updateHiddenInput() {
            $('#data_variable').val(JSON.stringify(inputData));
        }

        function loadLetterVariables() {
            $.ajax({
                url: "{{ route('lettervariablesetting.index') }}",
                type: "GET",
                data: {
                    user_id: "{{ session()->get('user_id') }}",
                    company_id: "{{ session()->get('company_id') }}",
                    token: "{{ session()->get('api_token') }}"
                },
                success: function(response) {
                    if (response.status) {
                        console.log(response.data);
                        // Replace variables dynamically
                        letterContent = replaceLetterVariables(letterContent, response.data, employeeDetails);
                        console.log(letterContent);

                        // Update letter body in HTML
                        $('.letter-body').html(letterContent);
                        updateHiddenInput();
                    }
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert('Something went wrong');
                }
            });
        }

        function replaceLetterVariables(letterContent, variableData, employeeDetails) {
            if (typeof letterContent !== 'string') {
                letterContent = String(letterContent);
            }

            function escapeRegExp(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            variableData.forEach(function(item) {
                console.log(item);

                // Correct: handle single variable string
                let filed_name = [item.variable.replace(/^\$/, '')];

                // Parse employee fields safely
                let fields = [];
                if (item.employee_fields) {
                    try {
                        fields = JSON.parse(item.employee_fields.replace(/&quot;/g, '"'));
                    } catch (e) {
                        console.warn("Invalid employee_fields JSON:", item.employee_fields);
                        fields = [item.employee_fields]; // fallback
                    }
                }

                let value = fields.map(f => employeeDetails[f] ?? f).join(' ');
                inputData[filed_name] = value;
                // Bootstrap styled input
                let inputHtml = `
            <input type="text"
                   class="form-control d-inline-block letter-input"
                   style="width: auto;"
                   name="${filed_name.join('_')}"
                   value="${value}" />`;

                let regex = new RegExp(escapeRegExp(item.variable.trim()), 'g');
                letterContent = letterContent.replace(regex, inputHtml);

            });
            return letterContent;
        }

        $('#genrateletterform').submit(function(event) {
            event.preventDefault();
            loadershow();
            updateHiddenInput();
            $('.modal-error-msg').text('');
            const formdata = $(this).serialize();
            $.ajax({
                type: 'POST',
                url: "{{ route('generateletter.store') }}",
                data: formdata,
                success: function(response) {
                    if (response.status == 200) {
                        Toast.fire({
                            icon: "success",
                            title: response.message
                        });
                        window.location = "{{ route('admin.generateletter') }}";
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: response.message
                        });
                    }
                    loaderhide();
                },
                error: function(xhr, status, error) { // if calling api request error 
                    loaderhide();
                    console.log(xhr
                        .responseText); // Log the full error response for debugging
                    handleAjaxError(xhr);
                }
            });
        })
    </script>
@endpush
