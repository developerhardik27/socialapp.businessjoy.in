@php
    $folder = session('folder_name');
@endphp

@extends($folder . '.admin.Layout.mastertable')

@section('page_title')
    {{ config('app.name') }} - Generate letter List
@endsection

@section('table_title')
    Generate letter List
@endsection

@section('style')
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/theme.css">
    <style>
        .letter-preview {
            width: 100%;
            border: 1px solid #000;
            padding: 20px;
            margin-top: 20px;
            background: #fff;
            box-sizing: border-box;
        }

        .preview-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .preview-section img {
            max-height: 80px;
            object-fit: contain;
        }

        .body-content {
            margin-bottom: 15px;
        }

        .flex-left {
            flex-direction: row;
        }

        .flex-right {
            flex-direction: row-reverse;
        }

        .flex-center {
            justify-content: center;
            text-align: center;
            flex-wrap: wrap;
        }

        .flex-center img,
        .flex-center .text {
            margin: 0 auto;
        }

        .text {
            max-width: 70%;
        }

        .ui-widget-header {
            background: #1518b117 !important;
            border: 1px solid #e1ded9 !important;
        }

        .btn-info {
            background-color: #253566 !important;
            border-color: #253566 !important;
            color: white;
        }

        .btn-info:hover {
            background-color: #39519b !important;
            color: rgb(255, 255, 255);
        }

        .btn-success {
            background-color: #67d5a5d9 !important;
            border-color: var(--iq-success) !important;
            color: black !important;
        }

        .btn-success:hover {
            background-color: #16d07ffa !important;
            border-color: var(--iq-success) !important;
            color: rgb(250, 250, 250) !important;
        }
    </style>
@endsection

@if (session('user_permissions.hrmodule.generate_letter.add') == '1' || $user_id == 1)
    @section('advancefilter')
        <div class="col-sm-12 text-right px-4">
            <button type="button"class="btn btn-sm btn-primary" data-toggle="modal" data-target="#generateletterModal"
                data-placement="bottom" data-original-title="Create new letter">
                <span> Generate Letter</span>
            </button>
        </div>
    @endsection
@endif

@section('table-content')
    <table id="lettersTable" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Letter Name</th>
                <th>Emp Name</th>
                <th>Create Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <div class="modal fade" id="generateletterModal" tabindex="-1" role="dialog"
        aria-labelledby="generateletterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sl" role="document"> <!-- modal-xl for large modal -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="generateletterModalLabel"> Generate letter</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="GenerateletterForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="token" value="{{ session('api_token') }}" />
                        <input type="hidden" name="user_id" value="{{ session('user_id') }}" />
                        <input type="hidden" name="company_id" value="{{ session('company_id') }}" />

                        <div class="form-group">
                            <label for="employee_id">Employee</label><span style="color:red">*</span>
                            <select name="employee_id" id="employee_id" class="form-control select2">
                                <option value="">Select Employee</option>
                            </select>
                            <span class="error-msg" id="error-employee_id" style="color:red"></span>
                        </div>

                        <div class="form-group">
                            <label for="letter_id">Letter Name</label><span style="color:red">*</span>
                            <select name="letter_id" id="letter_id" class="form-control select2">
                                <option value="">Select Letter</option>
                            </select>
                            <span class="error-msg" id="error-letter_id" style="color:red"></span>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Generate Letter</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('ajax')
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

    <script>
        $(document).ready(function() {
            const params = new URLSearchParams({
                user_id: "{{ session()->get('user_id') }}",
                company_id: "{{ session()->get('company_id') }}",
                token: "{{ session()->get('api_token') }}"
            });

            function letterload() {
                table = $('#lettersTable').DataTable({
                    language: {
                        lengthMenu: '_MENU_ &nbsp;Entries per page'
                    },
                    destroy: true, // allows re-initialization
                    responsive: true,
                    processing: true,
                    serverSide: true,
                    ajax: {
                        type: "GET",
                        url: "{{ route('generateletter.index') }}",
                        data: function(d) {
                            d.user_id = "{{ session()->get('user_id') }}";
                            d.company_id = "{{ session()->get('company_id') }}";
                            d.token = "{{ session()->get('api_token') }}";
                        },
                        dataSrc: function(json) {
                            if (json.message) {
                                Toast.fire({
                                    icon: "error",
                                    title: json.message || 'Something went wrong!'
                                })
                            }

                            global_response = json;

                            return json.data;
                        },
                        complete: function() {
                            loaderhide();
                        },
                        error: function(xhr) {
                            global_response = '';
                            console.log(xhr.responseText);
                            Toast.fire({
                                icon: "error",
                                title: "Error loading data"
                            });
                        }
                    },
                    order: [
                        [0, 'desc']
                    ],
                    columns: [{
                            data: 'id',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-',
                            name: 'id'
                        },
                        {
                            data: 'letter_name',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-',
                            name: 'letter_name'
                        },
                        {
                            data: 'employee_name',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-',
                            name: 'employee_name'
                        },
                        {
                            data: 'created_at_formatted',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-',
                            name: 'created_at_formatted'
                        },
                        {
                            data: 'id',
                            name: 'id',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                let actionBtns = '';
                                let generatePdfUrl =
                                    "{{ route('letter.generatepdf', '__Id__') }}"
                                    .replace('__Id__', row.id);
                                actionBtns = `                                             
                                    <span data-toggle="tooltip" data-placement="left" data-original-title="Download letter Pdf">
                                        <a href=${generatePdfUrl} target='_blank' id='pdf'>
                                            <button type="button" class="download-btn btn btn-info btn-rounded btn-sm my-0" ><i class="ri-download-line"></i></button>
                                        </a>
                                    </span>
                                `;
                                @if (session('user_permissions.hrmodule.generate_letter.edit') == '1')
                                    let EditUrl =
                                        "{{ route('admin.generateletterupdateform', '__invoiceId__') }}"
                                        .replace('__invoiceId__', row.id);
                                    actionBtns += `
                                        <span>  
                                            <a href=${EditUrl}>
                                                <button type="button" data-id='${row.id}' data-toggle="tooltip" data-placement="bottom" data-original-title="Edit letter" class="edit-btn btn btn-success btn-rounded btn-sm my-0">
                                                    <i class="ri-edit-fill"></i>
                                                </button>
                                            </a>
                                        </span>
                                    `;
                                @endif

                                @if (session('user_permissions.hrmodule.generate_letter.delete') == '1')
                                    actionBtns += `
                                    <span class="" data-toggle="tooltip" data-placement="bottom" data-original-title="Delete letter">
                                        <button type="button" data-id='${data}' class="del-btn btn btn-danger btn-rounded btn-sm my-0">
                                            <i class="ri-delete-bin-fill"></i>
                                        </button>
                                    </span>
                                `;
                                @endif

                                return actionBtns;
                            }
                        }
                    ],

                    pagingType: "full_numbers",
                    drawCallback: function(settings) {
                        $('[data-toggle="tooltip"]').tooltip({
                            boundary: 'window',
                            offset: '0, 10' // Push tooltip slightly away from the button
                        });

                        // 👇 Jump to Page input injection
                        if ($('#jumpToPageWrapper').length === 0) {
                            let jumpHtml = `
                                    <div id="jumpToPageWrapper" class="d-flex align-items-center ml-3" style="gap: 5px;">
                                        <label for="jumpToPage" class="mb-0">Jump to page:</label>
                                        <input type="number" id="jumpToPage" min="1" class="dt-input" style="width: 80px;" />
                                        <button id="jumpToPageBtn" class="btn btn-sm btn-primary">Go</button>
                                    </div>
                                `;
                            $(".dt-paging").after(jumpHtml);
                        }


                        $(document).off('click', '#jumpToPageBtn').on('click', '#jumpToPageBtn',
                            function() {
                                let table = $('#data').DataTable();
                                // Check if table is initialized
                                if ($.fn.DataTable.isDataTable('#data')) {
                                    let page = parseInt($('#jumpToPage').val());
                                    let totalPages = table.page.info().pages;

                                    if (!isNaN(page) && page > 0 && page <= totalPages) {
                                        table.page(page - 1).draw('page');
                                    } else {
                                        Toast.fire({
                                            icon: "error",
                                            title: `Please enter a page number between 1 and ${totalPages}`
                                        });
                                    }
                                } else {

                                    Toast.fire({
                                        icon: "error",
                                        title: `DataTable not yet initialized.`
                                    });
                                }
                            }
                        );
                    }
                });

            }

            letterload();
            $('#generateletterModal').on('show.bs.modal', function() {
                loademp(); // Populates the #employee_id select
                loadLetterformodel(); // You would create a similar function for #letter_id
            });

            const editorConfig = {
                toolbar: [
                    'bold', 'italic', 'underline',
                    '|', 'fontSize', 'fontColor',
                    '|', 'bulletedList', 'numberedList',
                    '|', 'insertTable',
                    '|', 'undo', 'redo'
                ]
            };


            $('#generateletterModal').on('hidden.bs.modal', function() {
                $("#GenerateletterForm")[0].reset();
                $("#generateletterModalLabel").text('Create Letter');

            });


            function loademp() {
                loadershow();

                // Reset the select
                $('#employee_id').html(`
                    <option selected disabled value="0">Select Employee</option>
                `);

                // Prepare query parameters
                const params = new URLSearchParams({
                    user_id: "{{ session()->get('user_id') }}",
                    company_id: "{{ session()->get('company_id') }}",
                    token: "{{ session()->get('api_token') }}"
                });

                // Make the AJAX GET request with query params
                $.ajax({
                    url: "{{ route('employee.index') }}?" + params.toString(),
                    type: "GET",
                    success: function(response) {
                        if (response.status == 200 && response.data && response.data.length) {
                            $.each(response.data, function(key, value) {
                                const EmpDetails = [value.first_name, value.surname]
                                    .filter(Boolean)
                                    .join(' - ');

                                $('#employee_id').append(`
                                    <option value='${value.id}'>${EmpDetails}</option>
                                `);
                            });


                            $('#employee_id').select2();

                        } else if (response.status == 500) {
                            Toast.fire({
                                icon: "error",
                                title: response.message
                            });
                        } else {
                            $('#employee_id').append(`<option disabled>No Data found</option>`);
                        }
                        loaderhide();
                    },
                    error: function(xhr) {
                        loaderhide();
                        handleAjaxError(xhr);
                    }
                });
            }

            function loadLetterformodel() {
                loadershow();


                $('#letter_id').html(`
                    <option selected disabled value="0">Select Letter</option>
                `);


                const params = new URLSearchParams({
                    user_id: "{{ session()->get('user_id') }}",
                    company_id: "{{ session()->get('company_id') }}",
                    token: "{{ session()->get('api_token') }}"
                });


                $.ajax({
                    url: "{{ route('letter.index') }}?" + params.toString(),
                    type: "GET",
                    success: function(response) {
                        if (response.status == 200 && response.data && response.data.length) {
                            $.each(response.data, function(key, value) {
                                const LetterDetails = [value.letter_name]
                                    .filter(Boolean)
                                    .join(' - ');

                                $('#letter_id').append(`
                                <option value='${value.id}'>${LetterDetails}</option>
                            `);
                            });


                            // Initialize Select2 for search
                            $('#letter_id').select2();

                        } else if (response.status == 500) {
                            Toast.fire({
                                icon: "error",
                                title: response.message
                            });
                        } else {
                            $('#letter_id').append(`<option disabled>No Data found</option>`);
                        }
                        loaderhide();
                    },
                    error: function(xhr) {
                        loaderhide();
                        handleAjaxError(xhr);
                    }
                });
            }

            $(document).on("click", ".del-btn", function() {
                var deleteid = $(this).data('id');
                var row = this;
                showConfirmationDialog(
                    'Are you sure?', // Title
                    'to delete this record?', // Text
                    'Yes, delete', // Confirm button text
                    'No, cancel', // Cancel button text
                    'question', // Icon type (question icon)
                    () => {
                        // Success callback
                        loadershow();
                        let consignorDeleteUrl = "{{ route('generateletter.delete', '__deleteId__') }}"
                            .replace(
                                '__deleteId__', deleteid);
                        $.ajax({
                            type: 'PUT',
                            url: consignorDeleteUrl,
                            data: {
                                token: "{{ session()->get('api_token') }}",
                                company_id: "{{ session()->get('company_id') }}",
                                user_id: "{{ session()->get('user_id') }}",
                            },
                            success: function(response) {
                                loaderhide();
                                if (response.status == 200) {
                                    Toast.fire({
                                        icon: "success",
                                        title: response.message
                                    });
                                    table.draw();
                                } else if (response.status == 500) {
                                    Toast.fire({
                                        icon: "error",
                                        title: response.message
                                    });
                                }
                            },
                            error: function(xhr, status,
                                error) { // if calling api request error 
                                loaderhide();
                                console.log(xhr
                                    .responseText
                                ); // Log the full error response for debugging
                                handleAjaxError(xhr);
                            }
                        });
                    }
                );
            });

            $('#GenerateletterForm').on('submit', function(e) {
                e.preventDefault();
                loadershow();
                $('.error-msg').text('');

                let employeeId = $('#employee_id').val();
                let letterId = $('#letter_id').val();
                let hasError = false;

                if (!employeeId) {
                    $('#error-employee_id').text('Please select an Employee first.');
                    hasError = true;
                }

                if (!letterId) {
                    $('#error-letter_id').text('Please select a Letter first.');
                    hasError = true;
                }

                if (hasError) {
                    loaderhide();
                    return false;
                }

                var formData = new FormData(this);

                $.ajax({
                    url: "{{ route('admin.generateletter_session') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 200) {
                            window.location.href = "{{ route('admin.generateletterform') }}";
                        } else {
                            loaderhide();
                            Toast.fire({
                                icon: 'error',
                                title: response.message || 'Something went wrong'
                            });
                        }
                    },
                    error: function(xhr) {
                        loaderhide();
                        Toast.fire({
                            icon: 'error',
                            title: 'AJAX request failed'
                        });
                    }
                });
            });
        });
    </script>
@endpush
