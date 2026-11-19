@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.mastertable')

@section('page_title')
    {{ config('app.name') }} - Job
@endsection
@section('table_title')
    Job
@endsection

@section('style')
    <style>
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

        .job-desc-cell {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
            vertical-align: middle;
        }

        .comment-item {
            border: 1px solid #e1ded9;
            border-radius: 5px;
            padding: 8px 10px;
            margin-bottom: 8px;
        }
    </style>
@endsection
@if (session('user_permissions.societymodule.job.add') == '1')
    @section('addnew')
        {{ route('admin.addsocietyjob') }}
    @endsection
    @section('addnewbutton')
        <button type="button" data-toggle="tooltip" data-placement="bottom" data-original-title="Add New Job"
            class="btn btn-sm btn-primary">
            <span class="">+ Add New</span>
        </button>
    @endsection
@endif
@section('table-content')
    <table id="data" class="table table-bordered display w-100 table-striped">
        <thead>
            <tr>
                <th>Id</th>
                <th>Title</th>
                <th>Type</th>
                <th>Company</th>
                <th>Posted By</th>
                <th>Likes</th>
                <th>Comments</th>
                <th>View</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="tabledata">

        </tbody>
    </table>

    <!-- Comments & Likes Modal -->
    <div class="modal fade" id="commentsModal" tabindex="-1" role="dialog" aria-labelledby="commentsModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="commentsModalTitle"><b>Job Comments</b></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center mb-3">
                        <button type="button" id="likeToggleBtn" class="btn btn-sm btn-outline-primary mr-2">
                            <i class="ri-thumb-up-line"></i> Like
                        </button>
                        <span id="likeCount" class="text-muted"></span>
                    </div>
                    <div id="commentsList"></div>
                    @if (session('user_permissions.societymodule.jobcomment.add') == '1')
                        <hr>
                        <form id="commentaddform">
                            <div class="form-group mb-2">
                                <label for="comment_person">Comment As (Family Person):</label>
                                <select id="comment_person" class="form-control form-control-sm">
                                    <option value="" selected>Select Person (Optional)</option>
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label for="comment_text">Comment:</label>
                                <textarea id="comment_text" class="form-control form-control-sm" rows="2"
                                    placeholder="Write a comment..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">Add Comment</button>
                        </form>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('ajax')
    <script>
        $('document').ready(function() {
            loaderhide();
            var global_response = '';
            var current_job_id = '';
            let table = '';

            function loadpersons() {
                $.ajax({
                    type: 'GET',
                    url: "{{ route('persons') }}",
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.data) {
                            $.each(response.data, function(key, person) {
                                $('#comment_person').append(
                                    `<option value="${person.id}">${person.full_name || person.first_name || ''} (${person.id})</option>`
                                );
                            });
                        }
                    }
                });
            }
            loadpersons();

            // load job data in table 
            function loaddata() {
                loadershow();
                table = $('#data').DataTable({
                    language: {
                        lengthMenu: '_MENU_ &nbsp;Entries per page'
                    },
                    destroy: true,
                    responsive: true,
                    processing: true,
                    serverSide: true,
                    ajax: {
                        type: "GET",
                        url: "{{ route('job.index') }}",
                        data: function(d) {
                            d.user_id = "{{ session()->get('user_id') }}";
                            d.company_id = "{{ session()->get('company_id') }}";
                            d.token = "{{ session()->get('api_token') }}";
                        },
                        dataSrc: function(json) {
                            if (json.status == 200 && json.data && json.data.length) {
                                global_response = json;
                                return json.data;
                            }

                            global_response = '';
                            // show api message (no data / unauthorized) inside the table instead of error toast
                            if (table && json.message) {
                                table.settings()[0].oLanguage.sEmptyTable = json.message;
                            }
                            return [];
                        },
                        complete: function() {
                            loaderhide();
                        },
                        error: function(xhr) {
                            global_response = '';
                            if (table) {
                                table.settings()[0].oLanguage.sEmptyTable =
                                    'Error loading data';
                            }
                        }
                    },
                    order: [
                        [0, 'desc']
                    ],
                    columns: [{
                            data: 'id',
                            name: 'id',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'title',
                            name: 'title',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'type',
                            name: 'type',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'company_name',
                            name: 'company_name',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'family_person_name',
                            name: 'family_person_name',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'total_likes',
                            name: 'total_likes',
                            orderable: false,
                            searchable: false,
                            defaultContent: '0'
                        },
                        {
                            data: null,
                            name: 'comments',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                return `<button type="button" data-jobid="${row.id}"
                                    class="comments-btn btn btn-info btn-rounded btn-sm my-0">
                                    ${row.total_comments || 0} <i class="ri-chat-3-line"></i>
                                </button>`;
                            }
                        },
                        {
                            data: 'id',
                            name: 'id',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                var actions = '-';
                                @if (session('user_permissions.societymodule.job.view') == '1')
                                    actions = ` 
                                        <span data-toggle="tooltip" data-placement="bottom" data-original-title="View Details">
                                            <button type="button"  data-view = '${row.id}' data-toggle="modal" data-target="#exampleModalScrollable" class="view-btn btn btn-info btn-rounded btn-sm my-0">
                                                <i class="ri-indent-decrease"></i>
                                            </button>
                                        </span>
                                    `;
                                @endif

                                return actions;
                            }
                        },
                        {
                            data: 'id',
                            name: 'id',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                let actionBtns = '';
                                @if (session('user_permissions.societymodule.job.edit') == '1')
                                    actionBtns += `
                                        <span data-toggle="tooltip" data-placement="bottom" data-original-title="Edit">
                                            <button type="button" data-id='${row.id}' class="edit-btn btn btn-success btn-rounded btn-sm my-0">
                                                <i class="ri-edit-fill"></i>
                                            </button>
                                        </span>
                                    `;
                                @endif
                                @if (session('user_permissions.societymodule.job.delete') == '1')
                                    actionBtns += `
                                        <span>
                                            <button data-toggle="tooltip" data-placement="bottom" data-original-title="Delete" type="button" data-id= '${row.id}' class=" del-btn btn btn-danger btn-rounded btn-sm my-0">
                                                <i  class="ri-delete-bin-fill"></i>
                                            </button>
                                        </span>
                                    `;
                                @endif

                                return actionBtns || '-';
                            }
                        }
                    ],

                    pagingType: "full_numbers",
                    drawCallback: function(settings) {
                        $('[data-toggle="tooltip"]').tooltip({
                            boundary: 'window',
                            offset: '0, 10'
                        });
                    }
                });

            }

            //call function for loaddata
            loaddata();

            function getJob(jobId) {
                var found = null;
                $.each(global_response.data || [], function(key, job) {
                    if (job.id == jobId) {
                        found = job;
                    }
                });
                return found;
            }

            // redirect to edit page
            $(document).on("click", ".edit-btn", function() {
                loadershow();
                var editid = $(this).data('id');
                let editUrl = "{{ route('admin.editsocietyjob', '__editId__') }}".replace('__editId__',
                    editid);
                window.location.href = editUrl;
            });

            // delete job             
            $(document).on("click", ".del-btn", function() {
                var deleteid = $(this).data('id');
                let jobDeleteUrl = "{{ route('job.destroy', '__deleteId__') }}".replace(
                    '__deleteId__', deleteid);
                var row = this;
                showConfirmationDialog(
                    'Are you sure?',
                    'to delete this record ?',
                    'Yes, delete',
                    'No, cancel',
                    'question',
                    () => {
                        loadershow();
                        ajaxRequest('PUT', jobDeleteUrl, {
                            token: "{{ session()->get('api_token') }}",
                            company_id: "{{ session()->get('company_id') }}",
                            user_id: "{{ session()->get('user_id') }}",
                        }).done(function(response) {
                            if (response.status == 200) {
                                Toast.fire({
                                    icon: "success",
                                    title: "succesfully deleted"
                                });
                                table.draw();
                            } else if (response.status == 500) {
                                Toast.fire({
                                    icon: "error",
                                    title: response.message
                                });
                            } else {
                                Toast.fire({
                                    icon: "error",
                                    title: "something went wrong!"
                                });
                            }
                            loaderhide();
                        }).fail(function(xhr) {
                            loaderhide();
                            handleAjaxError(xhr);
                        });
                    }
                );
            });

            // normalize media field (string / json string / array / double-encoded) into array of paths
            function toMediaList(value) {
                var list = [];
                function addItem(item, depth) {
                    if (!item || depth > 4) {
                        return;
                    }
                    if (typeof item === 'string') {
                        var t = $.trim(item);
                        t = t.replace(/&quot;/g, '"').replace(/&#34;|&#x22;/gi, '"')
                            .replace(/&#39;|&#x27;/gi, "'").replace(/&amp;/g, '&');
                        if (t.charAt(0) === '[' || t.charAt(0) === '"') {
                            try {
                                addItem(JSON.parse(t), depth + 1);
                                return;
                            } catch (e) {}
                        }
                        list.push(t.replace(/\\\//g, '/'));
                    } else if ($.isArray(item)) {
                        $.each(item, function(i, p) {
                            addItem(p, depth + 1);
                        });
                    } else {
                        list.push(String(item));
                    }
                }
                addItem(value, 0);
                return list;
            }

            // build usable url from stored path (collapse duplicate slashes)
            function mediaUrl(path) {
                path = String(path);
                if (/^https?:\/\//i.test(path)) {
                    return path;
                }
                return "{{ asset('') }}" + path.replace(/\/+/g, '/').replace(/^\//, '');
            }

            // view job data in pop-up
            $(document).on("click", ".view-btn", function() {
                $('#details').html('');
                var data = $(this).data('view');
                var job = getJob(data);
                if (job) {
                    var imageLink = '-';
                    var imgPath = toMediaList(job.image)[0] || '';
                    if (imgPath) {
                        imageLink =
                            `<a href="${mediaUrl(imgPath)}" target="_blank"><img src="${mediaUrl(imgPath)}" style="max-width:150px;max-height:150px;border-radius:5px;border:1px solid #ddd;" alt="Job Image"></a>`;
                    }
                    var salary = '-';
                    if (job.salary_from || job.salary_to) {
                        salary = (job.salary_from || '0') + ' - ' + (job.salary_to || '');
                    }
                    var address = [job.company_house_no_building_name, job.company_landmark, job
                        .company_area, job.company_pincode
                    ].filter(Boolean).join(', ') || '-';

                    var fields = {
                        'Title': job.title || '-',
                        'Type': job.type || '-',
                        'Description': job.description || '-',
                        'Company': job.company_name || '-',
                        'Company Address': address,
                        'Salary': salary,
                        'Posted By': job.family_person_name || '-',
                        'Total Likes': job.total_likes || 0,
                        'Total Comments': job.total_comments || 0,
                        'Image/PDF': imageLink,
                        'Created On': (job.created_at || '-').split('T')[0]
                    };
                    $.each(fields, function(label, value) {
                        $('#details').append(
                            `<tr><th>${label}</th><td>${value}</td></tr>`);
                    });
                }
            });

            // open comments modal
            $(document).on("click", ".comments-btn", function() {
                current_job_id = $(this).data('jobid');
                renderComments();
                $('#commentsModal').modal('show');
            });

            function renderComments() {
                var job = getJob(current_job_id);
                $('#commentsList').html('');
                if (!job) {
                    return;
                }
                $('#likeCount').text((job.total_likes || 0) + ' like(s)');
                $('#likeToggleBtn').toggleClass('btn-primary', !!job.is_like).toggleClass(
                    'btn-outline-primary', !job.is_like);
                var comments = job.comments || [];
                if (!comments.length) {
                    $('#commentsList').html('<p class="text-muted">No comments yet.</p>');
                }
                $.each(comments, function(key, comment) {
                    var actionBtns = '';
                    @if (session('user_permissions.societymodule.jobcomment.delete') == '1')
                        actionBtns +=
                            `<button type="button" data-commentid="${comment.comment_id}" class="del-comment-btn btn btn-danger btn-rounded btn-sm float-right"><i class="ri-delete-bin-fill"></i></button>`;
                    @endif
                    $('#commentsList').append(
                        `<div class="comment-item">
                            <b>${comment.family_person_name || 'Admin'}</b>
                            ${actionBtns}
                            <div>${comment.job_comment || ''}</div>
                        </div>`);
                });
            }

            // like/unlike job
            $('#likeToggleBtn').on('click', function() {
                if (!current_job_id) {
                    return;
                }
                loadershow();
                ajaxRequest('POST', "{{ route('job.joblike') }}", {
                    token: "{{ session()->get('api_token') }}",
                    company_id: "{{ session()->get('company_id') }}",
                    user_id: "{{ session()->get('user_id') }}",
                    job_id: current_job_id,
                    family_person_id: $('#comment_person').val() || ''
                }).done(function(response) {
                    if (response.status == 200) {
                        Toast.fire({
                            icon: "success",
                            title: response.message
                        });
                        var job = getJob(current_job_id);
                        if (job) {
                            job.is_like = !job.is_like;
                            job.total_likes = (job.total_likes || 0) + (job.is_like ? 1 : -1);
                        }
                        renderComments();
                        loaddata();
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: response.message || 'Something went wrong!'
                        });
                    }
                    loaderhide();
                }).fail(function(xhr) {
                    loaderhide();
                    handleAjaxError(xhr);
                });
            });

            // add comment
            $('#commentaddform').submit(function(event) {
                event.preventDefault();
                if (!current_job_id) {
                    return;
                }
                loadershow();
                var personId = $('#comment_person').val() || '';
                var commentText = $('#comment_text').val();
                ajaxRequest('POST', "{{ route('job.jobComment') }}", {
                    token: "{{ session()->get('api_token') }}",
                    company_id: "{{ session()->get('company_id') }}",
                    user_id: "{{ session()->get('user_id') }}",
                    job_id: current_job_id,
                    family_person_id: personId,
                    familyPersonId: personId,
                    comment: commentText
                }).done(function(response) {
                    if (response.status == 200) {
                        Toast.fire({
                            icon: "success",
                            title: response.message
                        });
                        $('#comment_text').val('');
                        var job = getJob(current_job_id);
                        if (job) {
                            job.comments = job.comments || [];
                            job.comments.push({
                                job_comment: commentText,
                                comment_id: '',
                                family_person_name: $('#comment_person option:selected')
                                    .text().replace(/\s*\(\d+\)\s*$/, '') || 'Admin'
                            });
                            job.total_comments = (job.total_comments || 0) + 1;
                        }
                        renderComments();
                        loaddata();
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: response.message || 'Something went wrong!'
                        });
                    }
                    loaderhide();
                }).fail(function(xhr) {
                    loaderhide();
                    handleAjaxError(xhr);
                });
            });

            // delete comment
            $(document).on("click", ".del-comment-btn", function() {
                var commentId = $(this).data('commentid');
                let commentDeleteUrl = "{{ route('job.deleteJobComment', '__deleteId__') }}"
                    .replace('__deleteId__', commentId);
                showConfirmationDialog(
                    'Are you sure?',
                    'to delete this comment ?',
                    'Yes, delete',
                    'No, cancel',
                    'question',
                    () => {
                        loadershow();
                        ajaxRequest('PUT', commentDeleteUrl, {
                            token: "{{ session()->get('api_token') }}",
                            company_id: "{{ session()->get('company_id') }}",
                            user_id: "{{ session()->get('user_id') }}",
                        }).done(function(response) {
                            if (response.status == 200) {
                                Toast.fire({
                                    icon: "success",
                                    title: "succesfully deleted"
                                });
                                var job = getJob(current_job_id);
                                if (job) {
                                    job.comments = (job.comments || []).filter(
                                        function(c) {
                                            return c.comment_id != commentId;
                                        });
                                    job.total_comments = Math.max(0, (job
                                        .total_comments || 1) - 1);
                                }
                                renderComments();
                                loaddata();
                            } else {
                                Toast.fire({
                                    icon: "error",
                                    title: response.message ||
                                        'something went wrong!'
                                });
                            }
                            loaderhide();
                        }).fail(function(xhr) {
                            loaderhide();
                            handleAjaxError(xhr);
                        });
                    }
                );
            });
        });
    </script>
@endpush
