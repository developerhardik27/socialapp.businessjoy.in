@php
    $folder = session('folder_name');
@endphp

@extends($folder . '.admin.Layout.mastertable')

@section('page_title')
    {{ config('app.name') }} - Letter Formates List
@endsection

@section('table_title')
    Letter Formates List
@endsection

@section('style')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css" rel="stylesheet">
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
        .preview-section img { max-height: 80px; object-fit: contain; }
        .body-content        { margin-bottom: 15px; }
        .flex-left           { flex-direction: row; }
        .flex-right          { flex-direction: row-reverse; }
        .flex-center         { justify-content: center; text-align: center; flex-wrap: wrap; }
        .flex-center img,
        .flex-center .text   { margin: 0 auto; }
        .text                { max-width: 70%; }

        .ui-widget-header {
            background: #1518b117 !important;
            border: 1px solid #e1ded9 !important;
        }
        .btn-info            { background-color: #253566 !important; border-color: #253566 !important; color: white; }
        .btn-info:hover      { background-color: #39519b !important; color: #fff; }
        .btn-success         { background-color: #67d5a5d9 !important; border-color: var(--iq-success) !important; color: black !important; }
        .btn-success:hover   { background-color: #16d07ffa !important; border-color: var(--iq-success) !important; color: #fafafa !important; }

        /* ══ Modal fullscreen ══ */
        .modal-fullscreen-custom {
            width: 100vw;
            max-width: 100vw;
            height: 99vh;
            margin: 0;
            padding: 1rem;
        }
        .modal-fullscreen-custom .modal-content {
            height: 100vh;
            border-radius: 0;
            display: flex;
            flex-direction: column;
        }
        .modal-fullscreen-custom .modal-body {
            flex: 1 1 auto;
            overflow: hidden;
            height: 100%;
            padding: 0;
        }

        /* ══ Modal header action buttons ══ */
        .modal-header { display: flex; align-items: center; }
        .modal-header .modal-title { flex: 1; }
        .modal-header-actions {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-right: 8px;
        }
        .modal-header-actions .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #dee2e6;
            background: #f8f9fa;
            border-radius: 4px;
            color: #495057;
            font-size: 15px;
            transition: background .15s, color .15s;
        }
        .modal-header-actions .btn:hover { background: #e2e6ea; color: #212529; }
        .modal-header-actions .btn.active { background: #253566; color: #fff; border-color: #253566; }

        /* ══ Body container ══ */
        #modalBodyContainer {
            height: 100%;
            display: flex;
            flex-direction: row;
            overflow: hidden;
        }

        /* ══ Form wrapper — always scrollable ══ */
        #letterFormWrapper {
            flex: 1 1 auto;
            overflow-y: auto;
            padding: 15px;
            height: 100%;
            box-sizing: border-box;
            min-width: 0;
        }

        /* ══ Drag resizer handle ══ */
        #splitResizer {
            display: none;
            width: 6px;
            min-width: 6px;
            height: 100%;
            background: #dee2e6;
            cursor: col-resize;
            position: relative;
            z-index: 10;
            flex-shrink: 0;
            transition: background .15s;
        }
        #splitResizer:hover,
        #splitResizer.dragging { background: #253566; }
        #splitResizer::after {
            content: '⋮';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #aaa;
            font-size: 18px;
            pointer-events: none;
        }
        #splitResizer.dragging::after { color: #fff; }

        /* ══ Preview iframe wrapper ══ */
        #iframeWrapper {
            display: none;
            flex-shrink: 0;
            height: 100%;
            overflow: hidden;
            position: relative;
            min-width: 100px;
        }

        /* Split active state */
        #modalBodyContainer.split-screen #splitResizer,
        #modalBodyContainer.split-screen #iframeWrapper {
            display: block;
        }

        /* Loading overlay */
        #iframeWrapper .preview-loader {
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,.80);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            font-size: 14px;
            color: #555;
            gap: 6px;
            flex-direction: column;
        }
        #iframeWrapper .preview-loader i {
            font-size: 28px;
            color: #253566;
            animation: spin .8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        #iframeWrapper iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }

        /* ══ Width badge shown on resizer ══ */
        #resizerBadge {
            display: none;
            position: absolute;
            top: 12px;
            left: 50%;
            transform: translateX(-50%);
            background: #253566;
            color: #fff;
            font-size: 10px;
            padding: 1px 5px;
            border-radius: 3px;
            white-space: nowrap;
            pointer-events: none;
            z-index: 20;
        }

        /* ══════════════════════════════
           MOBILE  (≤ 768px)
        ══════════════════════════════ */
        @media (max-width: 768px) {
            .modal-fullscreen-custom {
                height: 100vh;
                padding: 0;
            }
            .modal-fullscreen-custom .modal-content {
                height: 100vh;
            }

            /* On mobile, split becomes VERTICAL (top/bottom) */
            #modalBodyContainer.split-screen {
                flex-direction: column;
            }
            #modalBodyContainer.split-screen #letterFormWrapper {
                width: 100% !important;
                height: 50% !important;
                flex: none;
            }
            #modalBodyContainer.split-screen #iframeWrapper {
                width: 100% !important;
                height: 50% !important;
                flex: none;
                border-left: none;
                border-top: 2px solid #dee2e6;
            }

            /* Resizer becomes horizontal on mobile */
            #modalBodyContainer.split-screen #splitResizer {
                width: 100%;
                height: 6px;
                min-width: unset;
                min-height: 6px;
                cursor: row-resize;
            }
            #splitResizer::after {
                content: '···';
                letter-spacing: 2px;
            }

            /* Hide split button on very small screens if needed */
            /* Uncomment below to hide split btn on mobile: */
            /* #splitScreenBtn { display: none !important; } */

            .modal-header-actions .btn { width: 28px; height: 28px; font-size: 13px; }
        }

        /* ══ Tablet (769–991px): allow split but smaller ══ */
        @media (min-width: 769px) and (max-width: 991px) {
            #modalBodyContainer.split-screen #letterFormWrapper {
                min-width: 200px;
            }
            #modalBodyContainer.split-screen #iframeWrapper {
                min-width: 150px;
            }
        }
    </style>
@endsection

@if (session('user_permissions.hrmodule.companiesholidays.add') == '1' || $user_id == 1)
    @section('advancefilter')
        <div class="col-sm-12 text-right px-4">
            <button type="button" class="btn btn-sm btn-primary"
                    data-toggle="modal" data-target="#letterModal"
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

    <!-- ══════════════════ MODAL ══════════════════ -->
    <div class="modal fade" id="letterModal" tabindex="-1" role="dialog"
         aria-labelledby="letterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-fullscreen-custom" role="document">
            <div class="modal-content">

                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="letterModalLabel">Create Letter</h5>

                    <div class="modal-header-actions">
                        <button id="splitScreenBtn" class="btn" data-original-title="Split Screen Preview" >
                            <i class="fa fa-columns"></i>
                        </button>
                        <button id="fullscreenBtn" class="btn" data-original-title="Toggle Fullscreen" >
                            <i class="ri-fullscreen-line"></i>
                        </button>
                    </div>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <div id="modalBodyContainer">

                        <!-- LEFT: form -->
                        <div id="letterFormWrapper">
                            <form id="letterForm" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="token"      value="{{ session('api_token') }}" />
                                <input type="hidden" name="user_id"    value="{{ session('user_id') }}" />
                                <input type="hidden" name="company_id" value="{{ session('company_id') }}" />
                                <input type="hidden" name="edit_id"    id="edit_id" value="" />

                                <div class="form-group">
                                    <label for="letter_name">Letter Name</label><span class="text-danger">*</span>
                                    <input type="text" name="letter_name" id="letter_name" class="form-control"
                                           placeholder="Enter letter Name" style="height:38px; border-radius:.25rem;">
                                    <span class="error-msg" id="error-letter_name" style="color:red"></span>
                                </div>

                                <!-- Header card -->
                                <div class="card mb-3">
                                    <div class="card-header">Header</div>
                                    <div class="card-body">
                                        <div class="form-row mb-2 align-items-start">
                                            <div class="col-md-4">
                                                <label for="header_image">Header Image</label>
                                                <label for="header_image" class="form-control d-flex align-items-center p-0 overflow-hidden mb-0"
                                                       style="height:38px; cursor:pointer;">
                                                    <span class="bg-light border-right px-3 h-100 d-flex align-items-center text-muted flex-shrink-0"
                                                          style="font-size:13px; white-space:nowrap;">Choose file</span>
                                                    <span id="headerFileName" class="px-2 text-muted text-truncate" style="font-size:13px;">No file chosen</span>
                                                    <input type="file" name="header_image" id="header_image"
                                                           accept=".jpg,.jpeg,.png,image/jpeg,image/png" style="display:none;">
                                                </label>
                                                <span class="error-msg" id="error-header_image" style="color:red"></span>
                                                <div id="headerPreviewWrapper" style="display:none; margin-top:5px;">
                                                    <div class="d-flex align-items-center" style="gap:8px;">
                                                        <img id="headerPreview" src=""
                                                             style="max-height:60px; border:1px solid #ddd; border-radius:4px;">
                                                        <button type="button" class="btn btn-danger btn-sm" id="removeHeaderImage">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </div>
                                                    <input type="hidden" name="remove_header_image" id="remove_header_image" value="0">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="header_align">Alignment</label>
                                                <select name="header_align" id="header_align" class="form-control">
                                                    <option value="left">Left</option>
                                                    <option value="center">Center</option>
                                                    <option value="right">Right</option>
                                                </select>
                                                <span class="error-msg" id="error-header_align" style="color:red"></span>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="header_width">Width (%)</label>
                                                <input type="number" name="header_width" id="header_width"
                                                       class="form-control" style="height:38px; border-radius:.25rem;" value="30">
                                                <span class="error-msg" id="error-header_width" style="color:red"></span>
                                            </div>
                                        </div>
                                        <label for="header_content">Header Content</label>
                                        <textarea name="header_content" id="header_content"
                                                  placeholder="Enter header content" class="form-control"></textarea>
                                        <span class="error-msg" id="error-header_content" style="color:red"></span>
                                    </div>
                                </div>

                                <!-- Body card -->
                                <div class="card mb-3">
                                    <div class="card-header">Body</div>
                                    <div class="card-body">
                                        <label for="body_content">Body Content</label>
                                        <textarea name="body_content" id="body_content"
                                                  placeholder="Enter body content" class="form-control"></textarea>
                                        <span class="error-msg" id="error-body_content" style="color:red"></span>
                                    </div>
                                </div>

                                <!-- Footer card -->
                                <div class="card mb-3">
                                    <div class="card-header">Footer</div>
                                    <div class="card-body">
                                        <div class="form-row mb-2 align-items-start">
                                            <div class="col-md-4">
                                                <label for="footer_image">Footer Image</label>
                                                <label for="footer_image" class="form-control d-flex align-items-center p-0 overflow-hidden mb-0"
                                                       style="height:38px; cursor:pointer;">
                                                    <span class="bg-light border-right px-3 h-100 d-flex align-items-center text-muted flex-shrink-0"
                                                          style="font-size:13px; white-space:nowrap;">Choose file</span>
                                                    <span id="footerFileName" class="px-2 text-muted text-truncate" style="font-size:13px;">No file chosen</span>
                                                    <input type="file" name="footer_image" id="footer_image"
                                                           accept=".jpg,.jpeg,.png,image/jpeg,image/png" style="display:none;">
                                                </label>
                                                <span class="error-msg" id="error-footer_image" style="color:red"></span>
                                                <div id="footerPreviewWrapper" style="display:none; margin-top:5px;">
                                                    <div class="d-flex align-items-center" style="gap:8px;">
                                                        <img id="footerPreview" src=""
                                                             style="max-height:60px; border:1px solid #ddd; border-radius:4px;">
                                                        <button type="button" class="btn btn-danger btn-sm" id="removeFooterImage">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </div>
                                                    <input type="hidden" name="remove_footer_image" id="remove_footer_image" value="0">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="footer_align">Alignment</label>
                                                <select name="footer_align" id="footer_align" class="form-control">
                                                    <option value="left">Left</option>
                                                    <option value="center">Center</option>
                                                    <option value="right">Right</option>
                                                </select>
                                                <span class="error-msg" id="error-footer_align" style="color:red"></span>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="footer_width">Width (%)</label>
                                                <input type="number" name="footer_width" id="footer_width"
                                                       class="form-control" style="height:38px; border-radius:.25rem;" value="30">
                                                <span class="error-msg" id="error-footer_width" style="color:red"></span>
                                            </div>
                                        </div>
                                        <label for="footer_content">Footer Content</label>
                                        <textarea name="footer_content" id="footer_content"
                                                  placeholder="Enter footer content" class="form-control"></textarea>
                                        <span class="error-msg" id="error-footer_content" style="color:red"></span>
                                    </div>
                                </div>

                                <input type="hidden" id="headerfullpath">
                                <input type="hidden" id="footerfullpath">

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Save Letter</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                            </form>
                        </div>

                        <!-- DRAG RESIZER (injected between form & preview) -->
                        <div id="splitResizer">
                            <span id="resizerBadge"></span>
                        </div>

                        <!-- RIGHT: preview iframe -->
                        <div id="iframeWrapper">
                            <iframe id="previewIframe"></iframe>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('ajax')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
    $(document).ready(function () {

        /* ═══════════════════════════════════════════
           Helpers
        ═══════════════════════════════════════════ */
        const isMobile = () => window.innerWidth <= 768;

        /* ═══════════════════════════════════════════
           DataTable
        ═══════════════════════════════════════════ */
        function letterload() {
            table = $('#lettersTable').DataTable({
                language: { lengthMenu: '_MENU_ &nbsp;Entries per page' },
                destroy: true,
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    type: "GET",
                    url: "{{ route('letter.index') }}",
                    data: function (d) {
                        d.user_id    = "{{ session()->get('user_id') }}";
                        d.company_id = "{{ session()->get('company_id') }}";
                        d.token      = "{{ session()->get('api_token') }}";
                    },
                    dataSrc: function (json) {
                        if (json.message) Toast.fire({ icon: "error", title: json.message || 'Something went wrong!' });
                        global_response = json;
                        return json.data;
                    },
                    complete: function () { loaderhide(); },
                    error: function () { Toast.fire({ icon: "error", title: "Error loading data" }); }
                },
                order: [[0, 'desc']],
                columns: [
                    { data: 'id',           orderable: true,  searchable: true,  defaultContent: '-', name: 'id' },
                    { data: 'letter_name',  orderable: true,  searchable: true,  defaultContent: '-', name: 'letter_name' },
                    { data: 'header_align', orderable: true,  searchable: true,  defaultContent: '-', name: 'header_align' },
                    { data: 'header_width', orderable: true,  searchable: true,  defaultContent: '-', name: 'header_width' },
                    { data: 'footer_align', orderable: true,  searchable: true,  defaultContent: '-', name: 'footer_align' },
                    { data: 'footer_width', orderable: true,  searchable: true,  defaultContent: '-', name: 'lastname' },
                    {
                        data: 'id', name: 'id', orderable: false, searchable: false,
                        render: function (data) {
                            let btns = '';
                            @if (session('user_permissions.hrmodule.letters.view') == '1')
                                let viewUrl = "{{ route('admin.letterfomateview', '__varId__') }}".replace('__varId__', data);
                                btns += `<span><a href="${viewUrl}" onclick="loadershow()">
                                    <button type="button" data-id="${data}" data-toggle="tooltip" data-placement="bottom"
                                        data-original-title="Preview Letter"
                                        class="btn view-btn btn-info btn-rounded btn-sm my-0">
                                        <i class="ri-indent-decrease"></i>
                                    </button></a></span>`;
                            @endif
                            @if (session('user_permissions.hrmodule.letters.edit') == '1')
                                btns += `<span data-toggle="tooltip" data-placement="bottom" data-original-title="Edit letter">
                                    <button type="button" data-id='${data}' class="btn edit-btn btn-success btn-rounded btn-sm my-0">
                                        <i class="ri-edit-fill"></i>
                                    </button></span>`;
                            @endif
                            @if (session('user_permissions.hrmodule.letters.delete') == '1')
                                btns += `<span data-toggle="tooltip" data-placement="bottom" data-original-title="Delete letter">
                                    <button type="button" data-id='${data}' class="del-btn btn btn-danger btn-rounded btn-sm my-0">
                                        <i class="ri-delete-bin-fill"></i>
                                    </button></span>`;
                            @endif
                            return btns;
                        }
                    }
                ],
                pagingType: "full_numbers",
                drawCallback: function () {
                    $('[data-toggle="tooltip"]').tooltip({ boundary: 'window', offset: '0, 10' });
                    if ($('#jumpToPageWrapper').length === 0) {
                        $(".dt-paging").after(`
                            <div id="jumpToPageWrapper" class="d-flex align-items-center ml-3" style="gap:5px;">
                                <label for="jumpToPage" class="mb-0">Jump to page:</label>
                                <input type="number" id="jumpToPage" min="1" class="dt-input" style="width:80px;" />
                                <button id="jumpToPageBtn" class="btn btn-sm btn-primary">Go</button>
                            </div>`);
                    }
                    $(document).off('click', '#jumpToPageBtn').on('click', '#jumpToPageBtn', function () {
                        if ($.fn.DataTable.isDataTable('#lettersTable')) {
                            let t = $('#lettersTable').DataTable();
                            let page = parseInt($('#jumpToPage').val());
                            let totalPages = t.page.info().pages;
                            (page > 0 && page <= totalPages)
                                ? t.page(page - 1).draw('page')
                                : Toast.fire({ icon: "error", title: `Enter page between 1 and ${totalPages}` });
                        }
                    });
                }
            });
        }
        letterload();

        /* ═══════════════════════════════════════════
           Summernote
        ═══════════════════════════════════════════ */
        function initSummernote() {
            $('#header_content, #body_content, #footer_content').summernote({
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font',  ['fontsize']],
                    ['color', ['color']],
                    ['para',  ['ul', 'ol', 'paragraph']],
                    ['insert',['table', 'hr']],
                    ['view',  ['fullscreen', 'codeview']]
                ],
                height: 150
            });
        }
        initSummernote();

        /* ═══════════════════════════════════════════
           Image helpers
        ═══════════════════════════════════════════ */
        function isAllowedImageType(file) {
            const mimes = ['image/jpeg', 'image/png'];
            const exts  = ['jpg', 'jpeg', 'png'];
            const ext   = (file.name || '').toLowerCase().split('.').pop();
            return mimes.includes(file.type) || exts.includes(ext);
        }

        $(document).on('click', '#removeHeaderImage', function () {
            $("#header_image").val('');
            $("#headerPreview").attr("src", "");
            $("#headerPreviewWrapper").hide();
            $("#remove_header_image").val("1");
            $('#headerFileName').text('No file chosen');
            schedulePreview();
        });

        $(document).on('click', '#removeFooterImage', function () {
            $("#footer_image").val('');
            $("#footerPreview").attr("src", "");
            $("#footerPreviewWrapper").hide();
            $("#remove_footer_image").val("1");
            $('#footerFileName').text('No file chosen');
            schedulePreview();
        });

        $('#header_image').on('change', function () {
            const file = this.files[0];
            if (!file) return;
            if (!isAllowedImageType(file)) {
                $(this).val(''); $('#headerFileName').text('No file chosen');
                Toast.fire({ icon: "error", title: "Only JPG, JPEG, PNG allowed." });
                return;
            }
            $('#headerFileName').text(file.name);
            const reader = new FileReader();
            reader.onload = e => {
                $("#headerPreview").attr("src", e.target.result);
                $("#headerPreviewWrapper").show();
                $("#remove_header_image").val("0");
                schedulePreview();
            };
            reader.readAsDataURL(file);
        });

        $('#footer_image').on('change', function () {
            const file = this.files[0];
            if (!file) return;
            if (!isAllowedImageType(file)) {
                $(this).val(''); $('#footerFileName').text('No file chosen');
                Toast.fire({ icon: "error", title: "Only JPG, JPEG, PNG allowed." });
                return;
            }
            $('#footerFileName').text(file.name);
            const reader = new FileReader();
            reader.onload = e => {
                $("#footerPreview").attr("src", e.target.result);
                $("#footerPreviewWrapper").show();
                $("#remove_footer_image").val("0");
                schedulePreview();
            };
            reader.readAsDataURL(file);
        });

        /* ═══════════════════════════════════════════
           DEBOUNCED PREVIEW (300 ms)
        ═══════════════════════════════════════════ */
        let previewTimer = null;

        function schedulePreview() {
            if (!isSplitScreen) return;
            clearTimeout(previewTimer);
            showPreviewLoader(true);
            previewTimer = setTimeout(renderPreview, 300);
        }

        function showPreviewLoader(show) {
            let $loader = $('#iframeWrapper .preview-loader');
            if (show) {
                if (!$loader.length) {
                    $('#iframeWrapper').append(
                        `<div class="preview-loader">
                            <i class="ri-loader-4-line"></i>
                            <span>Updating preview…</span>
                        </div>`
                    );
                }
            } else {
                $loader.remove();
            }
        }

        function renderPreview() {
            if (!isSplitScreen) return;
            const formData = new FormData(document.getElementById('letterForm'));
            formData.set('header_content', $('#header_content').summernote('code'));
            formData.set('body_content',   $('#body_content').summernote('code'));
            formData.set('footer_content', $('#footer_content').summernote('code'));

            if (!$('#header_image')[0].files[0]) formData.append('header_image', $('#headerfullpath').val());
            if (!$('#footer_image')[0].files[0]) formData.append('footer_image', $('#footerfullpath').val());

            fetch('{{ route("admin.letter.preview") }}', { method: 'POST', body: formData })
                .then(res => res.blob())
                .then(blob => {
                    $('#previewIframe').attr('src', URL.createObjectURL(blob));
                    showPreviewLoader(false);
                })
                .catch(() => showPreviewLoader(false));
        }

        function bindPreviewEvents() {
            $('#letterFormWrapper')
                .off('input.preview change.preview')
                .on('input.preview change.preview', 'input:not([type=file]), select', schedulePreview);
            $('#header_content, #body_content, #footer_content')
                .off('summernote.change.preview')
                .on('summernote.change.preview', schedulePreview);
        }

        /* ═══════════════════════════════════════════
           DRAGGABLE RESIZER
        ═══════════════════════════════════════════ */
        let isSplitScreen   = false;
        // Default widths (percent)
        let formWidthPct    = 55;
        let previewWidthPct = 45;

        function applyWidths(formPct) {
            if (isMobile()) return; // mobile uses CSS fixed 50/50 vertical split
            const previewPct = 100 - formPct;
            $('#letterFormWrapper').css('width', formPct + '%');
            $('#iframeWrapper').css('width', previewPct + '%');
            $('#resizerBadge').text(Math.round(formPct) + '% | ' + Math.round(previewPct) + '%');
        }

        // Mouse / Touch drag on resizer
        const $resizer = $('#splitResizer');

        $resizer.on('mousedown touchstart', function (e) {
            if (isMobile()) return; // vertical split on mobile — skip horizontal drag
            e.preventDefault();

            $resizer.addClass('dragging');
            $('#resizerBadge').show();
            // Prevent iframe from capturing mouse events during drag
            $('#previewIframe').css('pointer-events', 'none');

            const $container   = $('#modalBodyContainer');
            const containerW   = $container[0].getBoundingClientRect().width;
            const startX       = e.type === 'touchstart' ? e.originalEvent.touches[0].clientX : e.clientX;
            const startFormW   = $('#letterFormWrapper')[0].getBoundingClientRect().width;

            function onMove(ev) {
                const clientX = ev.type === 'touchmove'
                    ? ev.originalEvent.touches[0].clientX
                    : ev.clientX;
                const delta   = clientX - startX;
                let newFormW  = startFormW + delta;

                // Clamp: form min 25%, max 75%
                const minPx = containerW * 0.34;
                const maxPx = containerW * 0.66;
                newFormW    = Math.min(Math.max(newFormW, minPx), maxPx);

                formWidthPct    = (newFormW / containerW) * 100;
                previewWidthPct = 100 - formWidthPct;
                applyWidths(formWidthPct);
            }

            function onUp() {
                $resizer.removeClass('dragging');
                $('#resizerBadge').hide();
                $('#previewIframe').css('pointer-events', '');
                $(document).off('mousemove.resizer touchmove.resizer');
                $(document).off('mouseup.resizer touchend.resizer');
            }

            $(document).on('mousemove.resizer touchmove.resizer', onMove);
            $(document).on('mouseup.resizer touchend.resizer', onUp);
        });

        /* ═══════════════════════════════════════════
           SPLIT SCREEN TOGGLE
        ═══════════════════════════════════════════ */
        $('#splitScreenBtn').on('click', function () {
            const $btn       = $(this);
            const $container = $('#modalBodyContainer');

            if (!isSplitScreen) {
                $container.addClass('split-screen');
                $btn.addClass('active').attr('title', 'Close Preview');
                isSplitScreen = true;

                // Apply saved widths (desktop only)
                if (!isMobile()) applyWidths(formWidthPct);

                bindPreviewEvents();
                renderPreview();
            } else {
                clearTimeout(previewTimer);
                $container.removeClass('split-screen');
                $btn.removeClass('active').attr('title', 'Split Screen Preview');
                isSplitScreen = false;

                // Reset inline widths set by drag
                $('#letterFormWrapper').css('width', '');
                $('#iframeWrapper').css('width', '');
                $('#previewIframe').attr('src', '');

                $('#letterFormWrapper').off('input.preview change.preview');
                $('#header_content, #body_content, #footer_content').off('summernote.change.preview');
            }
        });

        // On window resize: reapply widths if split is open
        $(window).on('resize.split', function () {
            if (isSplitScreen && !isMobile()) {
                applyWidths(formWidthPct);
            }
            // On mobile always clear inline widths (CSS handles it)
            if (isMobile() && isSplitScreen) {
                $('#letterFormWrapper').css({ width: '', height: '' });
                $('#iframeWrapper').css({ width: '', height: '' });
            }
        });

        /* ═══════════════════════════════════════════
           FULLSCREEN
        ═══════════════════════════════════════════ */
        const $fsBtn = $('#fullscreenBtn');

        $fsBtn.on('click', function () {
            const el = document.getElementById('letterModal');
            if (!document.fullscreenElement) {
                (el.requestFullscreen || el.webkitRequestFullscreen || el.msRequestFullscreen).call(el);
            } else {
                (document.exitFullscreen || document.webkitExitFullscreen || document.msExitFullscreen).call(document);
            }
        });

        document.addEventListener('fullscreenchange', function () {
            if (document.fullscreenElement) {
                $fsBtn.html('<i class="ri-fullscreen-exit-line"></i>').addClass('active').attr('title', 'Exit Fullscreen');
            } else {
                $fsBtn.html('<i class="ri-fullscreen-line"></i>').removeClass('active').attr('title', 'Toggle Fullscreen');
            }
        });

        /* ═══════════════════════════════════════════
           MODAL RESET
        ═══════════════════════════════════════════ */
        $('#letterModal').on('hidden.bs.modal', function () {
            if (isSplitScreen) $('#splitScreenBtn').trigger('click');
            if (document.fullscreenElement) document.exitFullscreen();

            $("#letterForm")[0].reset();
            $("#letterModalLabel").text('Create Letter');
            $("#edit_id").val('');

            $("#headerPreview").attr("src", "");
            $("#headerPreviewWrapper").hide();
            $("#remove_header_image").val("0");
            $('#headerFileName').text('No file chosen');
            $('#headerfullpath').val('');

            $("#footerPreview").attr("src", "");
            $("#footerPreviewWrapper").hide();
            $("#remove_footer_image").val("0");
            $('#footerFileName').text('No file chosen');
            $('#footerfullpath').val('');

            $('#header_content').summernote('code', '');
            $('#body_content').summernote('code', '');
            $('#footer_content').summernote('code', '');

            // Reset widths to default
            formWidthPct    = 55;
            previewWidthPct = 45;
        });

        /* ═══════════════════════════════════════════
           FETCH for edit
        ═══════════════════════════════════════════ */
        let letterdata = {};
        let fullPathheaderpath, fullPathfooterpath;

        function fetchLetter(editid) {
            loadershow();
            const url = "{{ route('letter.edit', ['id' => '__id__']) }}".replace('__id__', editid);
            $.ajax({
                url: url, method: "get",
                data: {
                    user_id:    "{{ session()->get('user_id') }}",
                    company_id: "{{ session()->get('company_id') }}",
                    token:      "{{ session()->get('api_token') }}"
                },
                success: function (response) {
                    if (response.status == 200) {
                        letterdata = response.data;
                        $("#letterModalLabel").text('Edit Letter');
                        $("#edit_id").val(editid);
                        $("#letter_name").val(letterdata.letter_name);
                        $("#header_align").val(letterdata.header_align);
                        $("#header_width").val(letterdata.header_width);
                        $('#header_content').summernote('code', letterdata.header_content ?? '');
                        $('#body_content').summernote('code',   letterdata.body_content   ?? '');
                        $('#footer_content').summernote('code', letterdata.footer_content ?? '');
                        $("#footer_align").val(letterdata.footer_align);
                        $("#footer_width").val(letterdata.footer_width);

                        if (letterdata.header_image) {
                            fullPathheaderpath = letterdata.header_image;
                            $("#headerPreview").attr("src", "{{ asset('') }}" + fullPathheaderpath);
                            $("#headerPreviewWrapper").show();
                            $("#remove_header_image").val("0");
                            $('#headerFileName').text(fullPathheaderpath.split('/').pop());
                            $('#headerfullpath').val(fullPathheaderpath);
                        }
                        if (letterdata.footer_image) {
                            fullPathfooterpath = letterdata.footer_image;
                            $("#footerPreview").attr("src", "{{ asset('') }}" + fullPathfooterpath);
                            $("#footerPreviewWrapper").show();
                            $("#remove_footer_image").val("0");
                            $('#footerFileName').text(fullPathfooterpath.split('/').pop());
                            $('#footerfullpath').val(fullPathfooterpath);
                        }

                        $('#letterModal').modal('show');
                        if (isSplitScreen) setTimeout(renderPreview, 150);
                    } else {
                        Toast.fire({ icon: "error", title: response.message || 'Something went wrong!' });
                    }
                    loaderhide();
                },
                error: function (xhr) { loaderhide(); handleAjaxError(xhr); }
            });
        }

        /* ═══════════════════════════════════════════
           Edit / Delete
        ═══════════════════════════════════════════ */
        $(document).on("click", ".edit-btn", function () {
            const editId = $(this).data('id');
            showConfirmationDialog('Are you sure?', 'to edit this letter?', 'Yes, edit', 'No, cancel', 'question',
                () => fetchLetter(editId));
        });

        $(document).on("click", ".del-btn", function () {
            const deleteid = $(this).data('id');
            showConfirmationDialog('Are you sure?', 'to delete this record?', 'Yes, delete', 'No, cancel', 'question',
                () => {
                    loadershow();
                    const url = "{{ route('letter.delete', '__deleteId__') }}".replace('__deleteId__', deleteid);
                    $.ajax({
                        type: 'PUT', url: url,
                        data: { token: "{{ session()->get('api_token') }}", company_id: "{{ session()->get('company_id') }}", user_id: "{{ session()->get('user_id') }}" },
                        success: function (response) {
                            loaderhide();
                            response.status == 200 ? table.draw() : Toast.fire({ icon: "error", title: response.message });
                        },
                        error: function (xhr) { loaderhide(); handleAjaxError(xhr); }
                    });
                });
        });

        /* ═══════════════════════════════════════════
           FORM SUBMIT
        ═══════════════════════════════════════════ */
        $('#letterForm').on('submit', function (e) {
            e.preventDefault();
            loadershow();
            $('.error-msg').text('');

            const formData = new FormData(this);
            formData.set('header_content', $('#header_content').summernote('code'));
            formData.set('body_content',   $('#body_content').summernote('code'));
            formData.set('footer_content', $('#footer_content').summernote('code'));

            const letterId = $("#edit_id").val();
            let url = letterId
                ? "{{ route('letter.update', ['id' => '__id__']) }}".replace('__id__', letterId)
                : "{{ route('letter.store') }}";

            $.ajax({
                url: url, method: 'POST',
                data: formData, processData: false, contentType: false,
                success: function (response) {
                    loaderhide();
                    if (response.status == 200) {
                        Toast.fire({ icon: "success", title: response.message });
                        $('#letterModal').modal('hide');
                        table.draw();
                    } else {
                        Toast.fire({ icon: "error", title: response.message });
                    }
                },
                error: function (xhr) { loaderhide(); handleAjaxError(xhr); }
            });
        });

    }); // end ready
    </script>
@endpush