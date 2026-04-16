@php
    $folder = session('folder_name');
@endphp

@extends($folder . '.admin.Layout.mastertable')

@section('page_title')
    {{ config('app.name') }} - Letter Variable Setting
@endsection

@section('table_title')
    Letter Variable Setting
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

@if (session('user_permissions.hrmodule.letter_variable_setting.add') == '1')
    @section('addnew')
        {{ route('admin.letter_variable_settingform') }}
    @endsection
    @section('addnewbutton')
        <button class="btn btn-sm btn-primary">
            <span class="" data-toggle="tooltip" data-placement="bottom"
                data-original-title="Create New Letter Variable Setting">+ Create
                New</span>
        </button>
    @endsection
@endif

@section('table-content')
    <table id="lettersTable" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Create</th>
                <th>variable</th>
                <th>description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
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


            function letter_variable_load() {
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
                        url: "{{ route('lettervariablesetting.index') }}",
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
                            data: 'created_by',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-',
                            name: 'created_by',
                            render: function(data, type, row) {
                                if (data == 1) {
                                    return 'Default';
                                } else {
                                    return 'Manual';
                                }
                            }
                        }, {
                            data: 'variable',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-',
                            name: 'variable'
                        },
                        {
                            data: 'description',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-',
                            name: 'description'
                        },
                        {
                            data: 'id',
                            name: 'id',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                let actionBtns = '';
                                if (row.created_by != 1) {
                                    @if (session('user_permissions.hrmodule.letter_variable_setting.edit') == '1')
                                        let variableEditUrl =
                                            "{{ route('admin.letter_variable_settingupdateform', '__varId__') }}"
                                            .replace('__varId__', data);
                                        actionBtns += `
                                    <span>
                                        <a href="${variableEditUrl}">
                                            <button type="button"
                                                data-id="${data}"
                                                data-toggle="tooltip"
                                                data-placement="bottom"
                                                data-original-title="Edit Letter Variable Setting"
                                                class="edit-btn btn btn-success btn-rounded btn-sm my-0">
                                                <i class="ri-edit-fill"></i>
                                            </button>
                                        </a>
                                    </span>
                                `;
                                    @endif


                                    @if (session('user_permissions.hrmodule.letter_variable_setting.delete') == '1')
                                        actionBtns += `
                                    <span data-toggle="tooltip"
                                        data-placement="bottom"
                                        data-original-title="Delete Letter Variable Setting">
                                        <button type="button"
                                                data-id="${data}"
                                                class="del-btn btn btn-danger btn-rounded btn-sm my-0">
                                            <i class="ri-delete-bin-fill"></i>
                                        </button>
                                    </span>
                                `;
                                    @endif


                                } else {
                                    return '<b>You cannot edit or delete</b>';
                                }
                                return actionBtns || '';
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

            letter_variable_load();

            const editorConfig = {
                toolbar: [
                    'bold', 'italic', 'underline',
                    '|', 'fontSize', 'fontColor',
                    '|', 'bulletedList', 'numberedList',
                    '|', 'insertTable',
                    '|', 'undo', 'redo'
                ]
            };
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
                        let consignorDeleteUrl =
                            "{{ route('lettervariablesetting.delete', '__deleteId__') }}"
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
                                        title: response.message ||
                                            "succesfully deleted"
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

        });
    </script>
@endpush
