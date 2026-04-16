@php
    $folder = session('folder_name');
@endphp
@extends($folder . '.admin.Layout.masterlayout')

@section('page_title')
    {{ config('app.name') }} - Add New Expense
@endsection
@section('title')
    New Expense
@endsection


@section('form-content')
    <form id="expenseform" name="expenseform" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <div class="form-row">
                <div class="col-sm-6 mb-2">
                    <input type="hidden" name="company_id" class="form-control" value="{{ session('company_id') }}" required />
                    <input type="hidden" name="token" class="form-control" value="{{ session('api_token') }}" required />
                    <input type="hidden" name="user_id" class="form-control" value="{{ session('user_id') }}" required>
                    <label for="date">Date</label><span style="color:red;">*</span>
                    <input type="date" name="date" class="form-control" id="date" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" autocomplete="off" required/>
                    <span class="error-msg" id="error-date" style="color: red"></span> 
                </div>
                <div class="col-sm-6 mb-2">
                    <label for="amount">Amount</label><span style="color:red;">*</span>
                    <input type="amount" name="amount" class="form-control" id="amount"
                        placeholder="Enter amount" autocomplete="off" required/>
                    <span class="error-msg" id="error-amount" style="color: red"></span>
                </div>  
                <div class="col-sm-6 mb-2">
                    <label for="paid_to">Paid To</label>
                    <input type="text" name="paid_to" class="form-control" id="paid_to"
                        placeholder="paid to"/>
                    <span class="error-msg" id="error-paid_to" style="color: red"></span>
                </div>  
                <div class="col-sm-6 mb-2">
                    <label for="payment_type">Payment Type</label>
                    <select name="payment_type" class="form-control" id="payment_type">
                        <option value="" disabled>Select Type</option>
                        <option value="Cash">Cash</option>
                        <option value="Online">Online</option>
                    </select>    
                    <span class="error-msg" id="error-payment_type" style="color: red"></span>
                </div>  
                <div class="col-sm-12 mb-2">
                    <label for="description">Description</label>
                    <textarea id="description" type="text" name="description" class="form-control" placeholder="description"
                        ></textarea>
                    <span class="error-msg" id="error-description" style="color: red"></span>
                </div>    
                <div class="col-sm-12">
                    <button type="button" data-toggle="tooltip" data-placement="bottom" data-original-title="Cancel"
                        id="cancelbtn" class="btn btn-secondary float-right">
                        Cancel
                    </button>
                    <button type="reset" data-toggle="tooltip" data-placement="bottom" data-original-title="Reset"
                        class="btn iq-bg-danger float-right mr-2">
                        Reset
                    </button>
                    <button type="submit" data-toggle="tooltip" data-placement="bottom" data-original-title="Save"
                        class="btn btn-primary float-right my-0">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection


@push('ajax')
    <script>
        $('document').ready(function() {
            loaderhide();
            // companyId and userId both are required in every ajax request for all action *************
            // response status == 200 that means response succesfully recieved
            // response status == 500 that means database not found
            // response status == 422 that means api has not got valid or required data
   

            // redirect on coach list page on click cancel btn
            $('#cancelbtn').on('click', function() {
                loadershow();
                window.location.href = "{{ route('admin.expense') }}";
            });

            // submit coach form data
            $('#expenseform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');
                var formdata = new FormData($(this)[0]);
                $.ajax({
                    type: 'POST',
                    url: "{{ route('expense.store') }}",
                    data: formdata,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // Handle the response from the server
                        if (response.status == 200) {
                            // You can perform additional actions, such as showing a success message or redirecting the user
                            Toast.fire({
                                icon: "success",
                                title: response.message
                            });
                            window.location = "{{ route('admin.expense') }}";

                        } else if (response.status == 500) {
                            Toast.fire({
                                icon: "error",
                                title: response.message
                            });
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
                        console.log(xhr.responseText); // Log the full error response for debugging
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            let firstErrorElement = null;

                            $.each(errors, function(key, value) {
                                let errorElement = $('#error-' + key);
                                errorElement.text(value[0]);

                                // Capture the first error element
                                if (!firstErrorElement) {
                                    firstErrorElement = errorElement;
                                }
                            });

                            if (firstErrorElement) {
                                $('html, body').animate({
                                    scrollTop: firstErrorElement.offset().top - 100 // adjust for spacing
                                }, 800);
                            }
                        } else {
                            var errorMessage = "";
                            try {
                                var responseJSON = JSON.parse(xhr.responseText);
                                errorMessage = responseJSON.message || "An error occurred";
                            } catch (e) {
                                errorMessage = "An error occurred";
                            }
                            Toast.fire({
                                icon: "error",
                                title: errorMessage
                            });
                        }
                    }
                });
            })
        });
    </script>
@endpush
