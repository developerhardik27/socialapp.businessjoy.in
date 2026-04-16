@extends(session('folder_name') . '.admin.Layout.masterlayout')

@section('page_title')
    {{ config('app.name') }} - Update Letter
@endsection

@section('title')
    Update Letter
@endsection

@section('form-content')
    <form id="generateletterform" name="generateletterform">
        <div class="container p-0 m-0">
            <div class="card shadow">
                @csrf
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mt-3" id="letter-header">
                        <!-- Header will be loaded dynamically via JS -->
                    </div>

                    <hr>

                    <div class="mt-3 letter-body" id="letter-body">
                        <!-- Letter body loaded via JS -->
                    </div>

                    <hr>

                    <div id="letter-footer">
                        <!-- Letter footer loaded via JS -->
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-row">
                        <div class="col-sm-12 mr-2">
                            <button id="cancelbtn" type="button"
                                class="btn btn-secondary btn-rounded float-right mr-2">Cancel</button>
                            <button type="reset" class="btn iq-bg-danger float-right mr-2">Reset</button>
                            <button type="submit" class="btn btn-primary float-right my-0">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden Inputs -->
        <input type="hidden" name="token" value="{{ session('api_token') }}">
        <input type="hidden" name="user_id" value="{{ $user_id }}">
        <input type="hidden" name="company_id" value="{{ $company_id }}">
        <input type="hidden" name="letter_id" id="letter_id">
        <input type="hidden" name="data_formate_id" id="data_formate_id">
        <input type="hidden" name="emp_id" id="emp_id">
        <input type="hidden" name="data_variable" id="data_variable">
    </form>
@endsection

@push('ajax')
    <script src="{{ asset('admin/js/select2.min.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        let edit_id = @json($edit_id);
        let employeeDetails = {};
        let letterDetails = {};
        let letterContent = '';
        let inputData = {};

        $(document).ready(function() {
            loaderhide();

            // redirect on employee list page on click cancel btn
            $('#cancelbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.generateletter') }}";
            });

            if (edit_id) {
                loadEditData(edit_id);
            }
        });


        function loadEditData(edit_id) {
            $.ajax({
                url: "{{ route('generateletter.edit', '__editId__') }}".replace('__editId__', edit_id),
                type: "GET",
                data: {
                    user_id: "{{ $user_id }}",
                    company_id: "{{ $company_id }}",
                    token: "{{ session('api_token') }}",
                    edit_id: edit_id
                },
                success: function(response) {
                    if (response.status == 200) {
                        const emp_id = response.data.emp_id;
                        const letter_id = response.data.letter_id;
                        const data_formate_id = response.data.data_formate_id;
                        const letter_value = response.data.letter_value;

                        $('#letter_id').val(letter_id);
                        $('#data_formate_id').val(data_formate_id);
                        $('#emp_id').val(emp_id);

                        // Load employee and letter in parallel
                        $.when(loadEmployee(emp_id), loadLetter(data_formate_id)).done(function() {

                            if (letterContent && employeeDetails) {
                                letterContent = replaceLetterVariables(letterContent, letter_value,
                                    employeeDetails);
                                console.log(letterContent);
                                $('#letter-body').html(letterContent);
                            }
                        });
                    }
                },
                error: function(xhr) {
                    loaderhide();
                    console.error(xhr.responseText);
                    handleAjaxError(xhr);
                }
            });
        }

        function loadEmployee(emp_id) {
            return $.ajax({
                url: "{{ route('employee.edit', '__editId__') }}".replace('__editId__', emp_id),
                type: "GET",
                data: {
                    user_id: "{{ $user_id }}",
                    company_id: "{{ $company_id }}",
                    token: "{{ session('api_token') }}"
                },
                success: function(response) {
                    if (response.status == 200) {
                        employeeDetails = response.data;
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    handleAjaxError(xhr);
                }
            });
        }


        function loadLetter(data_formate_id) {
            return $.ajax({
                url: "{{ route('dataformate.show', '__editId__') }}".replace('__editId__', data_formate_id),
                type: "GET",
                data: {
                    user_id: "{{ $user_id }}",
                    company_id: "{{ $company_id }}",
                    token: "{{ session('api_token') }}"
                },
                success: function(response) {
                    if (response.status == 200) {
                        letterDetails = response.data;
                        letterContent = letterDetails.body_content;


                        if (letterDetails.header_image || letterDetails.header_content) {
                            let headerHtml = '';
                            if (letterDetails.header_image) {
                                headerHtml += `<div style="flex:0 0 ${letterDetails.header_width}%; text-align:${letterDetails.header_align};">
                                 <img src="{{ asset('') }}${letterDetails.header_image}" 
                                    class="img-fluid" 
                                    style="display: inline-block; height: auto; max-height: ${letterDetails.header_width * 3}px;">
                            </div>`;
                            }
                            if (letterDetails.header_content) {
                                headerHtml +=
                                    `<div class="flex-grow-1 ms-3">${letterDetails.header_content}</div>`;
                            }
                            $('#letter-header').html(headerHtml);
                        }

                        // Load footer
                        let footerHtml = letterDetails.footer_content || '';
                        if (letterDetails.footer_image) {
                            footerHtml += `
                            <div style="text-align: ${letterDetails.footer_align};">
                                <img src="{{ asset('') }}${letterDetails.footer_image}" 
                                    class="img-fluid" 
                                    style="display: inline-block; height: auto; max-height: ${letterDetails.footer_width * 3}px;">
                            </div>
                        `;
                        }
                        $('#letter-footer').html(footerHtml);
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    handleAjaxError(xhr);
                }
            });
        }


        function replaceLetterVariables(letterContent, letterValueObj, employeeDetails) {
            if (typeof letterContent !== 'string') letterContent = String(letterContent);

            function escapeRegExp(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }
            if (typeof letterValueObj === 'string') {
                try {
                    letterValueObj = JSON.parse(letterValueObj);
                } catch (e) {
                    console.error('Invalid JSON:', letterValueObj);
                }
            }

            for (let key in letterValueObj) {

                if (letterValueObj.hasOwnProperty(key)) {
                    let value = letterValueObj[key];

                    inputData[key] = value;

                    let inputHtml =
                        `<input type="text" class="form-control d-inline-block letter-input" style="width:auto;" name="${key}" value="${value}" />`;

                    let regex = new RegExp('\\$' + escapeRegExp(key), 'g');
                    letterContent = letterContent.replace(regex, inputHtml);
                }
            }

            $('#data_variable').val(JSON.stringify(inputData));
            return letterContent;
        }


        $(document).on('input', '.letter-input', function() {
            let key = $(this).attr('name');
            inputData[key] = $(this).val();
            $('#data_variable').val(JSON.stringify(inputData));
        });


        $('#generateletterform').submit(function(event) {
            event.preventDefault();
            loadershow();

            $.ajax({
                type: 'POST',
                url: "{{ route('generateletter.update', '__editId__') }}".replace('__editId__', edit_id),
                data: $(this).serialize(),
                success: function(response) {
                    loaderhide();
                    if (response.status == 200) {
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                        window.location = "{{ route('admin.generateletter') }}";
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: response.message
                        });
                    }
                },
                error: function(xhr) {
                    loaderhide();
                    console.error(xhr.responseText);
                    handleAjaxError(xhr);
                }
            });
        });
    </script>
@endpush
