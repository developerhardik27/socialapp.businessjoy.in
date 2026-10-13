@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.mastertable')

@section('page_title')
    {{ config('app.name') }} - Event
@endsection
@section('table_title')
    Event
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

        .comment-item {
            border: 1px solid #e1ded9;
            border-radius: 5px;
            padding: 8px 10px;
            margin-bottom: 8px;
        }
    </style>
@endsection
@if (session('user_permissions.societymodule.event.add') == '1')
    @section('addnew')
        {{ route('admin.addsocietyevent') }}
    @endsection
    @section('addnewbutton')
        <button type="button" data-toggle="tooltip" data-placement="bottom" data-original-title="Add New Event"
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
                <th>Event Date</th>
                <th>Type</th>
                <th>Status</th>
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
                    <h5 class="modal-title" id="commentsModalTitle"><b>Event Comments</b></h5>
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
                    @if (session('user_permissions.societymodule.eventcomment.add') == '1')
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
            var current_event_id = '';
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

            // load event data in table 
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
                        url: "{{ route('event.index') }}",
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
                            data: null,
                            name: 'event_date_from',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-',
                            render: function(data, type, row) {
                                var from = row.event_date_from ? String(row.event_date_from).split(
                                    'T')[0] : '';
                                var to = row.event_date_to ? String(row.event_date_to).split('T')[
                                    0] : '';
                                if (from && to && from != to) {
                                    return from + ' to ' + to;
                                }
                                return from || to || '-';
                            }
                        },
                        {
                            data: 'event_type',
                            name: 'event_type',
                            orderable: true,
                            searchable: true,
                            defaultContent: '-'
                        },
                        {
                            data: 'event_status',
                            name: 'event_status',
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
                                return `<button type="button" data-eventid="${row.id}"
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
                                @if (session('user_permissions.societymodule.event.view') == '1')
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
                                @if (session('user_permissions.societymodule.event.edit') == '1')
                                    actionBtns += `
                                        <span data-toggle="tooltip" data-placement="bottom" data-original-title="Edit">
                                            <button type="button" data-id='${row.id}' class="edit-btn btn btn-success btn-rounded btn-sm my-0">
                                                <i class="ri-edit-fill"></i>
                                            </button>
                                        </span>
                                    `;
                                @endif
                                @if (session('user_permissions.societymodule.event.delete') == '1')
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

            function getEvent(eventId) {
                var found = null;
                $.each(global_response.data || [], function(key, event) {
                    if (event.id == eventId) {
                        found = event;
                    }
                });
                return found;
            }

            // redirect to edit page
            $(document).on("click", ".edit-btn", function() {
                loadershow();
                var editid = $(this).data('id');
                let editUrl = "{{ route('admin.editsocietyevent', '__editId__') }}".replace('__editId__',
                    editid);
                window.location.href = editUrl;
            });

            // delete event             
            $(document).on("click", ".del-btn", function() {
                var deleteid = $(this).data('id');
                let eventDeleteUrl = "{{ route('event.destroy', '__deleteId__') }}".replace(
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
                        ajaxRequest('PUT', eventDeleteUrl, {
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

            // view event data in pop-up
            $(document).on("click", ".view-btn", function() {
                $('#details').html('');
                var data = $(this).data('view');
                var event = getEvent(data);
                if (event) {
                    var imageLinks = '-';
                    var videoLinks = '-';
                    var images = toMediaList(event.image || event.images);
                    var videos = toMediaList(event.video || event.videos);
                    if (images.length) {
                        imageLinks = images.map(function(img, i) {
                            return `<a href="${mediaUrl(img)}" target="_blank">
                                    <img src="${mediaUrl(img)}" style="max-width:120px;max-height:120px;border-radius:5px;margin:3px;border:1px solid #ddd;" alt="Image ${i+1}">
                                </a>`;
                        }).join(' ');
                    }
                    if (videos.length) {
                        videoLinks = videos.map(function(vid, i) {
                            return `<div class="mb-2"><video src="${mediaUrl(vid)}" controls style="max-width:100%;max-height:200px;"></video><br>
                                    <a href="${mediaUrl(vid)}" target="_blank">Open Video ${i+1}</a></div>`;
                        }).join(' ');
                    }

                    var venue = [event.building_name, event.landmark, event.area, event.pincode]
                        .filter(Boolean).join(', ') || '-';

                    var fields = {
                        'Title': event.title || '-',
                        'Description': event.description || '-',
                        'Event Date From': (event.event_date_from || '-').split('T')[0],
                        'Event Date To': (event.event_date_to || '-').split('T')[0],
                        'Event Time': ((event.event_time_from || '') + ' - ' + (event
                            .event_time_to || '')).trim(' -') || '-',
                        'Type': event.event_type || '-',
                        'Status': event.event_status || '-',
                        'Venue': venue,
                        'Posted By': event.family_person_name || '-',
                        'Total Likes': event.total_likes || 0,
                        'Total Comments': event.total_comments || 0,
                        'Images': imageLinks,
                        'Videos': videoLinks,
                        'Created On': (event.created_at || '-').split('T')[0]
                    };
                    $.each(fields, function(label, value) {
                        $('#details').append(
                            `<tr><th>${label}</th><td>${value}</td></tr>`);
                    });
                }
            });

            // open comments modal
            $(document).on("click", ".comments-btn", function() {
                current_event_id = $(this).data('eventid');
                renderComments();
                $('#commentsModal').modal('show');
            });

            function renderComments() {
                var event = getEvent(current_event_id);
                $('#commentsList').html('');
                if (!event) {
                    return;
                }
                $('#likeCount').text((event.total_likes || 0) + ' like(s)');
                $('#likeToggleBtn').toggleClass('btn-primary', !!event.is_like).toggleClass(
                    'btn-outline-primary', !event.is_like);
                var comments = event.comments || [];
                if (!comments.length) {
                    $('#commentsList').html('<p class="text-muted">No comments yet.</p>');
                }
                $.each(comments, function(key, comment) {
                    var actionBtns = '';
                    @if (session('user_permissions.societymodule.eventcomment.delete') == '1')
                        actionBtns +=
                            `<button type="button" data-commentid="${comment.comment_id}" class="del-comment-btn btn btn-danger btn-rounded btn-sm float-right"><i class="ri-delete-bin-fill"></i></button>`;
                    @endif
                    $('#commentsList').append(
                        `<div class="comment-item">
                            <b>${comment.family_person_name || 'Admin'}</b>
                            ${actionBtns}
                            <div>${comment.event_comment || ''}</div>
                        </div>`);
                });
            }

            // like/unlike event
            $('#likeToggleBtn').on('click', function() {
                if (!current_event_id) {
                    return;
                }
                loadershow();
                ajaxRequest('POST', "{{ route('event.eventlike') }}", {
                    token: "{{ session()->get('api_token') }}",
                    company_id: "{{ session()->get('company_id') }}",
                    user_id: "{{ session()->get('user_id') }}",
                    event_id: current_event_id,
                    family_person_id: $('#comment_person').val() || ''
                }).done(function(response) {
                    if (response.status == 200) {
                        Toast.fire({
                            icon: "success",
                            title: response.message
                        });
                        var event = getEvent(current_event_id);
                        if (event) {
                            event.is_like = !event.is_like;
                            event.total_likes = (event.total_likes || 0) + (event.is_like ? 1 :
                                -1);
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
                if (!current_event_id) {
                    return;
                }
                loadershow();
                var personId = $('#comment_person').val() || '';
                var commentText = $('#comment_text').val();
                ajaxRequest('POST', "{{ route('event.eventComment') }}", {
                    token: "{{ session()->get('api_token') }}",
                    company_id: "{{ session()->get('company_id') }}",
                    user_id: "{{ session()->get('user_id') }}",
                    event_id: current_event_id,
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
                        var event = getEvent(current_event_id);
                        if (event) {
                            event.comments = event.comments || [];
                            event.comments.push({
                                event_comment: commentText,
                                comment_id: '',
                                family_person_name: $('#comment_person option:selected')
                                    .text().replace(/\s*\(\d+\)\s*$/, '') || 'Admin'
                            });
                            event.total_comments = (event.total_comments || 0) + 1;
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
                let commentDeleteUrl = "{{ route('event.deleteEventComment', '__deleteId__') }}"
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
                                var event = getEvent(current_event_id);
                                if (event) {
                                    event.comments = (event.comments || []).filter(
                                        function(c) {
                                            return c.comment_id != commentId;
                                        });
                                    event.total_comments = Math.max(0, (event
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
