@php
    $folder = session('folder_name');
@endphp

@extends($folder . '.admin.Layout.mastertable')

@section('page_title')
    {{ config('app.name') }} - Letters
@endsection

@section('table_title')
    Letters
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

@if (session('user_permissions.hrmodule.companiesholidays.add') == '1' || $user_id == 1)
    @section('advancefilter')
        <div class="col-sm-12 text-right px-4">
            <button type="button"class="btn btn-sm btn-primary" data-toggle="modal" data-target="#letterModal"
                data-placement="bottom" data-original-title="Create new letter">
                <span>+ Create Letter</span>
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
                <th>Header Align</th>
                <th>Header Width (%)</th>
                <th>Footer Align</th>
                <th>Footer Width (%)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <div class="modal fade" id="letterModal" tabindex="-1" role="dialog" aria-labelledby="letterModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document"> <!-- modal-xl for large modal -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="letterModalLabel">Create Letter</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="letterForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="token" value="{{ session('api_token') }}" />
                        <input type="hidden" name="user_id" value="{{ session('user_id') }}" />
                        <input type="hidden" name="company_id" value="{{ session('company_id') }}" />
                        <input type="hidden" name="edit_id" id="edit_id" value="" />

                        <div class="form-group">
                            <label for="letter_name">Letter Name</label><span class="text-danger">*</span>
                            <input type="text" name="letter_name" id="letter_name" class="form-control"
                                placeholder="Enter letter Name">
                            <span class="error-msg" id="error-letter_name" style="color:red"></span>
                        </div>


                        <div class="card mb-3">
                            <div class="card-header">Header</div>
                            <div class="card-body">
                                <div class="form-row mb-2">
                                    <div class="col-md-4">
                                        <label for="header_image">Header Image</label><span class="text-danger">*</span>
                                        <div
                                            class="input-group border rounded bg-white px-3 py-2 d-flex align-items-center">
                                            <input type="file" name="header_image" id="header_image">
                                        </div>
                                        <span class="error-msg" id="error-header_image" style="color:red"></span>
                                        <img id="headerPreview" src="" style="max-height:80px; display:none;">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="header_align">Alignment</label><span class="text-danger">*</span>
                                        <select name="header_align" id="header_align" class="form-control">
                                            <option value="left">Left</option>
                                            <option value="center">Center</option>
                                            <option value="right">Right</option>
                                        </select>
                                        <span class="error-msg" id="error-header_align" style="color:red"></span>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="header_width">Width (%)</label><span class="text-danger">*</span>
                                        <input type="number" name="header_width" id="header_width" class="form-control"
                                            value="30">
                                        <span class="error-msg" id="error-header_width" style="color:red"></span>
                                    </div>
                                </div>
                                <label for="header_content">Header Content</label><span class="text-danger">*</span>
                                <textarea name="header_content" id="header_content" placeholder="Enter header content" class="form-control"></textarea>
                                <span class="error-msg" id="error-header_content" style="color:red"></span>
                            </div>
                        </div>


                        <div class="card mb-3">
                            <div class="card-header">Body</div>
                            <div class="card-body">
                                <label for="body_content">Header Content</label><span class="text-danger">*</span>
                                <textarea name="body_content" id="body_content" placeholder="Enter body content" class="form-control"></textarea>
                                <span class="error-msg" id="error-body_content" style="color:red"></span>
                            </div>
                        </div>


                        <div class="card mb-3">
                            <div class="card-header">Footer</div>
                            <div class="card-body">
                                <div class="form-row mb-2">
                                    <div class="col-md-4">
                                        <label for="footer_image">Footer Image</label><span class="text-danger">*</span>
                                        <div
                                            class="input-group border rounded bg-white px-3 py-2 d-flex align-items-center">
                                            <input type="file" name="footer_image" id="footer_image">
                                        </div>
                                        <span class="error-msg" id="error-footer_image" style="color:red"></span>
                                        <img id="footerPreview" src="" style="max-height:80px; display:none;">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="footer_align">Alignment</label><span class="text-danger">*</span>
                                        <select name="footer_align" id="footer_align" class="form-control">
                                            <option value="left">Left</option>
                                            <option value="center">Center</option>
                                            <option value="right">Right</option>
                                        </select>
                                        <span class="error-msg" id="error-footer_align" style="color:red"></span>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="footer_width">Width (%)</label><span class="text-danger">*</span>
                                        <input type="number" name="footer_width" id="footer_width" class="form-control"
                                            value="30">
                                        <span class="error-msg" id="error-footer_width" style="color:red"></span>
                                    </div>
                                </div>
                                <label for="footer_content">Footer Content</label><span class="text-danger">*</span>
                                <textarea name="footer_content" id="footer_content" placeholder="Enter Footer content" class="form-control"></textarea>
                                <span class="error-msg" id="error-footer_content" style="color:red"></span>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save Letter</button>
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
                        url: "{{ route('letter.index') }}",
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
                            data: 'header_align',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-',
                            name: 'header_align'
                        },
                        {
                            data: 'header_width',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-',
                            name: 'header_width'
                        },
                        {
                            data: 'footer_align',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-',
                            name: 'footer_align'
                        },
                        {
                            data: 'footer_width',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-',
                            name: 'lastname'
                        },
                        {
                            data: 'id',
                            name: 'id',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                let actionBtns = '';
                                @if (session('user_permissions.hrmodule.letters.edit') == '1')
                                    actionBtns += `
                                        <span data-toggle="tooltip" data-placement="bottom" data-original-title="Edit letter">
                                            <button type="button" data-id='${data}' class="btn edit-btn btn-success btn-rounded btn-sm my-0">
                                                <i class="ri-edit-fill"></i>
                                            </button>
                                        </span>
                                    `;
                                @endif

                                @if (session('user_permissions.hrmodule.letters.delete') == '1')
                                    actionBtns += `
                                    <span class="" data-toggle="tooltip" data-placement="bottom" data-original-title="Delete letter Details">
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

            const editorConfig = {
                toolbar: [
                    'bold', 'italic', 'underline',
                    '|', 'fontSize', 'fontColor',
                    '|', 'bulletedList', 'numberedList',
                    '|', 'insertTable',
                    '|', 'undo', 'redo'
                ]
            };

            let headerEditor = null,
                bodyEditor = null,
                footerEditor = null;


            if (!headerEditor) {
                ClassicEditor
                    .create(document.querySelector('#header_content'))
                    .then(editor => {
                        headerEditor = editor;
                    })
                    .catch(console.error);
            }

            if (!bodyEditor) {
                ClassicEditor
                    .create(document.querySelector('#body_content'))
                    .then(editor => {
                        bodyEditor = editor;
                    })
                    .catch(console.error);
            }

            if (!footerEditor) {
                ClassicEditor
                    .create(document.querySelector('#footer_content'))
                    .then(editor => {
                        footerEditor = editor;
                    })
                    .catch(console.error);
            }

            $('#letterModal').on('hidden.bs.modal', function() {
                $("#letterForm")[0].reset();
                $("#letterModalLabel").text('Create Letter');
                $("#headerPreview").attr("src", "").hide();
                $("#footerPreview").attr("src", "").hide();
                headerEditor.setData('');
                bodyEditor.setData('');
                footerEditor.setData('');
            });

            function toBase64(file) {
                return new Promise((resolve, reject) => {
                    if (!file) return resolve('');
                    let reader = new FileReader();
                    reader.readAsDataURL(file);
                    reader.onload = () => resolve(reader.result);
                    reader.onerror = error => reject(error);
                });
            }

            function fetchLetter(editid) {
                loadershow();
                let url = "{{ route('letter.edit', ['id' => '__id__']) }}".replace('__id__', editid);
                $.ajax({
                    url: url,
                    method: "get",
                    data: {
                        user_id: "{{ session()->get('user_id') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        token: "{{ session()->get('api_token') }}"
                    },
                    success: function(response) {
                        if (response.status == 200) {
                            letterdata = response.data;
                            $("#letterModalLabel").text('Edit Letter');
                            $("#edit_id").val(editid);
                            $("#letter_name").val(letterdata.letter_name);
                            $("#header_align").val(letterdata.header_align);
                            $("#header_width").val(letterdata.header_width);
                            headerEditor.setData(letterdata.header_content ?? '');
                            bodyEditor.setData(letterdata.body_content ?? '');
                            footerEditor.setData(letterdata.footer_content ?? '');
                            $("#footer_align").val(letterdata.footer_align);
                            $("#footer_width").val(letterdata.footer_width);
                            if (letterdata.header_image) {
                                $("#headerPreview")
                                    .attr("src", "{{ asset('') }}" + letterdata.header_image)
                                    .show();
                            }
                            if (letterdata.footer_image) {
                                $("#footerPreview")
                                    .attr("src", "{{ asset('') }}" + letterdata.footer_image)
                                    .show();
                            }
                            $('#letterModal').modal('show');
                        } else {
                            Toast.fire({
                                icon: "error",
                                title: response.message || 'Something went wrong!'
                            });
                        }
                        loaderhide();
                    },
                    error: function(xhr) {
                        loaderhide()
                        handleAjaxError(xhr);
                    }
                })
            }

            $(document).on("click", ".edit-btn", function() {
                var editId = $(this).data('id');
                showConfirmationDialog(
                    'Are you want?', // Title
                    'to edit this letter?', // Text
                    'Yes, edit', // Confirm button text
                    'No, cancel', // Cancel button text
                    'question', // Icon type (question icon)
                    () => {
                        fetchLetter(editId);
                    }
                );
            });


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
                        let consignorDeleteUrl = "{{ route('letter.delete', '__deleteId__') }}"
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
                                if (response.status == 'success') {
                                    letterload();
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

            $('#letterForm').on('submit', async function(e) {
                e.preventDefault();
                loadershow()
                $('.error-msg').text(''); // Clear previous error messages
                var formData = new FormData(this);

                // CKEditor content
                formData.set('header_content', headerEditor.getData());
                formData.set('body_content', bodyEditor.getData());
                formData.set('footer_content', footerEditor.getData());

                let url = "{{ route('letter.store') }}";
                let type = "POST";

                const letterId = $("#letterForm #edit_id").val();

                if (letterId) {
                    url = "{{ route('letter.update', ['id' => '__id__']) }}".replace('__id__',
                        letterId);;
                }

                $.ajax({
                    url: url,
                    method: type,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        loaderhide();
                        if (response.status == 200) {
                            Toast.fire({
                                icon: "success",
                                title: response.message
                            });
                            $("#letterForm")[0].reset();
                            $('#letterModal').modal('hide');
                            table.draw();
                        } else {
                            Toast.fire({
                                icon: "error",
                                title: response.message
                            });
                        }

                    },
                    error: function(xhr) {
                        loaderhide()
                        handleAjaxError(xhr);
                    }
                });
            });
        });
    </script>
@endpush
