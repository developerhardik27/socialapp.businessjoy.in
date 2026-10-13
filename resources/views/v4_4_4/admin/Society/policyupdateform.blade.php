@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')
@section('page_title')
    {{ config('app.name') }} - Update Policy
@endsection
@section('title')
    Update Policy
@endsection

@section('form-content')
    <form id="policyupdateform" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <div class="form-row">
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="policy_number">Policy Number:</label><span
                        style="color:red;"> *</span>
                    <input type="text" class="form-control" name="policy_number" id="policy_number"
                        placeholder="Policy Number" required />
                    <span class="error-msg" id="error-policy_number" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="date">Date:</label><span style="color:red;"> *</span>
                    <input type="date" class="form-control" name="date" id="date" required />
                    <span class="error-msg" id="error-date" style="color: red"></span>
                </div>
                <div class="col-sm-12 mb-2">
                    <label class="form-label" for="text">Text:</label><span style="color:red;"> *</span>
                    <textarea class="form-control" name="text" id="text" rows="4" placeholder="Policy Text"
                        required></textarea>
                    <span class="error-msg" id="error-text" style="color: red"></span>
                </div>
                <div class="col-sm-6 mb-2">
                    <label class="form-label" for="pdf_file">PDF File:</label>
                    <input type="file" class="form-control" name="pdf_file" id="pdf_file"
                        accept="application/pdf" />
                    <small class="text-muted">pdf only — uploading replaces the old file</small>
                    <div id="existingPdf" class="mt-2"></div>
                </div>
                <div class="col-sm-12">
                    <button type="reset" id="resetbtn" data-toggle="tooltip" data-placement="bottom"
                        data-original-title="Cancel" class="btn iq-bg-danger float-right">Cancel</button>
                    <button type="submit" data-toggle="tooltip" data-placement="bottom"
                        data-original-title="Update" class="btn btn-primary float-right my-0">Save</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('ajax')
    <script>
        $('document').ready(function() {
            loaderhide();
            var edit_id = @json($edit_id);

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

            // redirect on policy page
            $('#resetbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.societypolicy') }}";
            });

            // load old data
            function loaddata() {
                loadershow();
                let policyEditUrl = "{{ route('policy.edit', '__editId__') }}".replace('__editId__',
                    edit_id);
                $.ajax({
                    type: 'GET',
                    url: policyEditUrl,
                    data: {
                        token: "{{ session()->get('api_token') }}",
                        company_id: "{{ session()->get('company_id') }}",
                        user_id: "{{ session()->get('user_id') }}"
                    },
                    success: function(response) {
                        if (response.status == 200 && response.data) {
                            var data = response.data;
                            $('#policy_number').val(data.policy_number);
                            $('#text').val(data.text);
                            $('#date').val(data.date ? String(data.date).split('T')[0] : '');
                            var pdfs = toMediaList(data.pdf_file);
                            if (pdfs.length) {
                                $('#existingPdf').html('<b>Existing:</b> ' + pdfs.map(
                                    function(pdf, i) {
                                        return `<a href="${mediaUrl(pdf)}" target="_blank">PDF ${i+1}</a>`;
                                    }).join(', '));
                            }
                        } else {
                            Toast.fire({
                                icon: "error",
                                title: response.message || 'Something went wrong!'
                            });
                        }
                        loaderhide();
                    },
                    error: function(xhr) {
                        loaderhide();
                        handleAjaxError(xhr);
                    }
                });
            }

            loaddata();

            //submit form
            $('#policyupdateform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');

                var formData = new FormData(this);
                formData.append('token', "{{ session()->get('api_token') }}");
                formData.append('company_id', "{{ session()->get('company_id') }}");
                formData.append('user_id', "{{ session()->get('user_id') }}");
                formData.append('_method', 'PUT');

                let policyUpdateUrl = "{{ route('policy.update', '__editId__') }}".replace(
                    '__editId__', edit_id);

                $.ajax({
                    type: 'POST',
                    url: policyUpdateUrl,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        loaderhide();
                        if (response.status == 200) {
                            Toast.fire({
                                icon: "success",
                                title: response.message || 'Policy updated successfully!'
                            });
                            window.location = "{{ route('admin.societypolicy') }}";
                        } else {
                            Toast.fire({
                                icon: "error",
                                title: response.message || 'Something went wrong!'
                            });
                        }
                    },
                    error: function(xhr) {
                        loaderhide();
                        handleAjaxError(xhr);
                    }
                });
            });
        });
    </script>
@endpush
