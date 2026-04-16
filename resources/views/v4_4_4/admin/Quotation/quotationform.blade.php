    @php
        $folder = session('folder_name');
    @endphp
    @extends($folder.'.admin.Layout.masterlayout')
    @section('page_title')
    {{ config('app.name') }} - Create New Quotation
    @endsection
    @section('title')
        Create Quotation
    @endsection

    @section('style')
    <style>
        .disableinput{
            border: none;
        }
        table input.form-control {
            width: auto;
            min-width: 100%;
        }

        table textarea.form-control{
            width: auto;
            min-width: 100%;
        }
    </style>
    <link rel="stylesheet" href="{{asset('admin/css/select2.min.css')}}">
    @endsection

    @section('form-content')
        <form id="quotationform" name="quotationform">
            @csrf
            <div class="form-group">
                <div class="form-row">
                    <div class="col-sm-4 mb-3" id="company_div"> 
                        <label for="company">Company</label>
                        <select class="form-control select2" id="company" name="company" required>
                            <option selected="" value='0'> Select company</option>
                            <option  value="add_company" > Add New Company </option> 
                        </select>
                        <span class="error-msg" id="error-company" style="color: red"></span>
                    </div> 
                    <div class="col-sm-4 mb-3">
                        <span class=" float-right mb-3 mr-2">
                            {{-- <button type="button" data-toggle="modal" data-target="#exampleModalScrollable"
                                class="btn btn-sm bg-primary "><i class="ri-add-fill"><span class="pl-1">Add
                                        customer</span></i>
                            </button> --}}
                        </span>
                        <label for="customer">Customer</label><span
                        style="color:red;">*</span>
                        <select class="form-control select2" id="customer" name="customer" required>
                            <option selected="" disabled=""> Select Customer</option>
                            <option  value="add_customer" > Add New Customer </option>
                        </select>
                        <span class="error-msg" id="error-customer" style="color: red"></span>
                    </div> 
                    @if (session('company_gst_no') && session('company_gst_no') != '')
                        <div class="col-sm-4 mb-3">
                            <label for="type">Tax-Type</label><span style="color:red;">*</span>
                            <select class="form-control" id="type" name="type" required>
                                <option disabled="">Select Type</option>
                                <option value="1" selected>GST</option>
                                <option value="2">Without GST</option>
                            </select>
                            <span class="error-msg" id="error-tax_type" style="color: red"></span>
                        </div> 
                    @else
                        <input type="hidden" id="type" name="type" value="2">    
                    @endif
                    <div class="col-sm-4 mb-3">
                        <label for="currency">Currency</label><span
                        style="color:red;">*</span>
                        <select class="form-control" id="currency" name="currency" required>
                            <option selected="" disabled=""> Select Currency</option>
                        </select>
                        <span class="error-msg" id="error-currency" style="color: red"></span>
                    </div> 
                    <div class="col-sm-4 mb-3" id="quotation_date_div">
                        <label for="quotation_date">Quotation Date</label>
                        {{-- <span style="color:red;">*</span> --}}
                        <input type="datetime-local" class="form-control" id="quotation_date" name="quotation_date">
                        <span class="error-msg" id="error-quotation_date" style="color: red"></span>
                    </div>
                    <div class="col-sm-4 mb-3" id="quotation_number_div">
                        <label for="quotation_number">Quotation Number</label> 
                        <input type="text" name="quotation_number" id="quotation_number" class="form-control" placeholder="Quotation Number">
                        <span class="error-msg" id="error-quotation_number" style="color: red"></span>
                    </div> 
                </div>
            </div> 
            <div id="table" class="table-editable" style="overflow-x:auto">
                
                <table id="data" class="table table-bordered  table-striped text-center producttable">
                    <thead>
                        <tr id="columnname" style="text-transform: uppercase">

                        
                        </tr>
                    </thead>
                    <tbody  id="add_new_div">
                    </tbody>
                    <tr>
                        <th class="newdivautomaticcolspan"><span class="add_div mb-3 mr-2">
                            <button type="button" data-toggle="tooltip" data-placement="bottom" data-original-title="Add New Row" class="btn btn-sm iq-bg-success">
                                <i class="ri-add-fill">
                                    <span class="pl-1"> Add New Item </span>
                                </i>
                            </button>
                        </span></th>
                    </tr>
                    <tr class="text-right">
                        <th class="automaticcolspan">Sub total</th>
                        <td id=""> 
                        <div class="d-flex justify-content-between">
                                <b>
                                    <span class="currentcurrencysymbol"></span>
                                </b>  
                                <input class="disableinput" type="number" step="any" name="total_amount" id="totalamount" readonly required>
                            </div>
                        </td>
                    </tr>
                    <tr id="sgstline" class="text-right" @style(['display:none' => (!session('company_gst_no') && session('company_gst_no') == '')])>
                        <th class="automaticcolspan">SGST <span id="sgstpercentage"></span></th>
                        <td>
                            <div class="d-flex justify-content-between">
                                <b><span class="currentcurrencysymbol"></span></b> <input class="disableinput" type="number" step="any" name="sgst" id="sgst" readonly required>
                            </div>
                        </td>
                    </tr>
                    <tr id="cgstline" class="text-right" @style(['display:none' => (!session('company_gst_no') && session('company_gst_no') == '')])>
                        <th class="automaticcolspan">CGST <span id="cgstpercentage"></span></th>
                        <td>
                            <div class="d-flex justify-content-between">
                                <b><span class="currentcurrencysymbol"></span></b> <input class="disableinput" type="number" step="any" name="cgst" id="cgst" readonly required>
                            </div>
                        </td>
                    </tr>
                    <tr id="gstline" class="text-right" @style(['display:none' => (!session('company_gst_no') && session('company_gst_no') == '')])>
                        <th class="automaticcolspan">Total GST <span id="gstpercentage"></span></th>
                        <td>
                            <div class="d-flex justify-content-between">
                                <b><span class="currentcurrencysymbol"></span></b> <input class="disableinput" type="number" step="any" name="gst" id="gst" readonly required>
                            </div>
                        </td>
                    </tr>
                    <tr id="roundoffline" class="text-right">
                        <th class="automaticcolspan">Roundoff</th>
                        <td>
                            <div class="d-flex justify-content-between">
                                <b>
                                    <span class="currentcurrencysymbol"></span>
                                </b> 
                                <input class="disableinput" type="text" name="roundoff" id="roundoff" readonly required>
                            </div>
                        </td>
                    </tr>
                    <tr id="grandtotalline" class="text-right">
                        <th class="automaticcolspan font-weight-bold">Total</th>
                        <td>
                            <div class="d-flex justify-content-between">
                                <b>
                                    <span class="currentcurrencysymbol"></span>
                                </b> 
                                <input class="disableinput" type="number" step="any" name="grandtotal" id="grandtotal" readonly required>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="form-group">
                <div class="form-row">
                    <div class="col-sm-12">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" name="notes" id="notes" rows="2"></textarea>
                        <span class="error-msg" id="error-notes" style="color: red"></span>
                    </div>
                </div>
            </div>
            <div class="form-group">
            <div class="form-row">
                    <div class="col-sm-12 d-flex flex-column flex-md-row justify-content-end">
                        <button type="submit" data-toggle="tooltip" data-placement="bottom" data-original-title="Submit Details" class="btn btn-primary m-0 my-1 mx-1" >Submit</button>
                        <button type="reset" data-toggle="tooltip" data-placement="bottom" data-original-title="Reset Details" class="btn iq-bg-danger my-1 mx-1">Reset</button>
                        <button id="cancelbtn" type="button" data-toggle="tooltip" data-placement="bottom" data-original-title="Cancel" class="btn btn-secondary my-1 mx-1">Cancel</button>
                    </div>
            </div>
            </div>
        </form>


        {{-- for add new customer direct from quotationform --}}
        <div class="modal fade" id="exampleModalScrollable" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalScrollableTitle">Add New Customer</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <table id="details" width='100%' class="table table-bordered table-responsive-sm table-responsive-md table-responsive-lg table-responsive-xl table-striped">
                            <form id="customerform">
                                @csrf
                                <div class="form-group">
                                    <div class="form-row">
                                        <div class="col-sm-6 mb-2">
                                            <input type="hidden" name="token" class="form-control"
                                                value="{{ session('api_token') }}" placeholder="token" required />
                                            <input type="hidden" value="{{ $user_id }}" class="form-control"
                                                name="user_id">
                                            <input type="hidden" value="{{ $company_id }}" class="form-control"
                                                name="company_id">
                                            <label for="firstname">FirstName</label><span class="withoutgstspan" style="color:red;">*</span>
                                            <input type="text" class="form-control withoutgstinput" id="firstname" name='firstname'
                                                placeholder="First name" required>
                                            <span class="modal-error-msg" id="modal-error-firstname"
                                                style="color: red"></span>
                                        </div>
                                        <div class="col-sm-6 mb-2">
                                            <label for="lastname">LastName</label>
                                            {{-- <span style="color:red;">*</span> --}}
                                            <input type="text" class="form-control" id="lastname" name='lastname'
                                                placeholder="Last name">
                                            <span class="modal-error-msg" id="modal-error-lastname"
                                                style="color: red"></span>
                                        </div> 
                                        <div class="col-sm-6 mb-2">
                                            <label for="company_name">Company Name</label>
                                            <span class="withgstspan" style="color:red;">*</span>
                                            <input type="text" class="form-control withgstiput" id="company_name" name='company_name'
                                                id="" placeholder="Company name">
                                            <span class="modal-error-msg" id="modal-error-company_name"
                                                style="color: red"></span>
                                        </div>
                                        <div class="col-sm-6 mb-2">
                                            <label for="gst_number">GST Number</label>
                                            {{-- <span class="withgstspan" style="color:red;">*</span> --}}
                                            <input type="text" class="form-control" name='gst_number' id="gst_number"
                                                placeholder="GST Number">
                                            <span class="modal-error-msg" id="modal-error-gst_number"
                                                style="color: red"></span>
                                        </div> 
                                        <div class="col-sm-6 mb-2">
                                            <label for="modal_email">Email</label>
                                            {{-- <span class="requiredinputspan" style="color:red;">*</span> --}}
                                            <input type="email" class="form-control requiredinput" name="email" id="modal_email"
                                                placeholder="Enter Email"/>
                                            <span class="modal-error-msg" id="modal-error-email" style="color: red"></span>
                                        </div>
                                        <div class="col-sm-6 mb-2">
                                            <label for="modal_exampleInputphone">Contact Number</label>
                                            {{-- <span class="requiredinputspan" style="color:red;">*</span> --}}
                                            <input type="tel" class="form-control requiredinput" name='contact_number'
                                                id="modal_exampleInputphone" placeholder="0123456789">
                                            <span class="modal-error-msg" id="modal-error-contact_number"
                                                style="color: red"></span>
                                        </div> 
                                        <div class="col-sm-6 mb-2">
                                            <label for="modal_country">Select Country</label>
                                            {{-- <span class="requiredinputspan" style="color:red;">*</span> --}}
                                            <select class="form-control requiredinput" name='country' id="modal_country">
                                                <option selected="" disabled="">Select your Country</option>
                                            </select>
                                            <span class="modal-error-msg" id="modal-error-country"
                                                style="color: red"></span>
                                        </div>
                                        <div class="col-sm-6 mb-2">
                                            <label for="modal_state">Select State</label>
                                            {{-- <span class="requiredinputspan" style="color:red;">*</span> --}}
                                            <select class="form-control requiredinput" name='state' id="modal_state">
                                                <option selected="" disabled="">Select your State</option>
                                            </select>
                                            <span class="modal-error-msg" id="modal-error-state"
                                                style="color: red"></span>
                                        </div> 
                                        <div class="col-sm-6 mb-2">
                                            <label for="modal_city">Select City</label>
                                            {{-- <span class="requiredinputspan" style="color:red;">*</span> --}}
                                            <select class="form-control requiredinput" name='city' id="modal_city">
                                                <option selected="" disabled="">Select your City</option>
                                            </select>
                                            <span class="modal-error-msg" id="modal-error-city"
                                                style="color: red"></span>
                                        </div>
                                        <div class="col-sm-6 mb-2">
                                            <label for="modal_pincode">Pincode</label>
                                            {{-- <span class="requiredinputspan" style="color:red;">*</span> --}}
                                            <input type="text" id="modal_pincode" name='pincode' class="form-control requiredinput"
                                                placeholder="Pin Code">
                                            <span class="modal-error-msg" id="modal-error-pincode"
                                                style="color: red"></span>
                                        </div> 
                                        <div class="col-sm-6 mb-2">
                                            <label for="house_no_building_name">House no./ Building Name</label>
                                            {{-- <span class="requiredinputspan" style="color:red;">*</span> --}}
                                            <textarea class="form-control requiredinput" name='house_no_building_name' id="house_no_building_name" rows="2" placeholder="e.g. 2nd floor/ 04 ABC Appartment"></textarea>
                                            <span class="modal-error-msg" id="modal-error-house_no_building_name" style="color: red"></span>
                                        </div>
                                        <div class="col-sm-6 mb-2">
                                            <label for="road_name_area_colony">Road Name/Area/Colony</label>
                                            {{-- <span class="requiredinputspan" style="color:red;">*</span> --}}
                                            <textarea class="form-control requiredinput" name='road_name_area_colony' id="road_name_area_colony" rows="2" placeholder="e.g. sardar patel road, jagatpur"></textarea>
                                            <span class="modal-error-msg" id="modal-error-road_name_area_colony" style="color: red"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary" id="modal_submitBtn">Save</button>
                                    <button id="modal_resetbtn" type="reset" class="btn iq-bg-danger mr-2">Reset</button>
                                    <button id="modal_cancelBtn" type="btn" class="btn btn-secondary">Cancel</button>
                                </div>
                            </form>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        {{-- ADD NEW COMPANY MODAL --}}
        <div class="modal fade" id="companyModalScrollable" tabindex="-1" role="dialog"
            aria-labelledby="companyModalScrollableTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="companyModalScrollableTitle">Add New Company</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="companyform" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="token" value="{{ session('api_token') }}">
                            <input type="hidden" name="user_id" value="{{ $user_id }}">
                            <input type="hidden" name="company_id" value="{{ $company_id }}">
                        
                            <div class="form-group">
                                <div class="form-row">

                                    <div class="col-sm-6 mb-2">
                                        <label for="company_name_input">Name</label>
                                        <span style="color:red;">*</span>
                                        <input type="text" class="form-control" id="company_name_input" name="name"
                                            placeholder="Company name" >
                                        <span class="company-error-msg" id="company-error-name" style="color:red;"></span>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <label for="company_email">Email</label>
                                        <input type="email" class="form-control" id="company_email" name="email"
                                            placeholder="Enter Company Email">
                                        <span class="company-error-msg" id="company-error-email" style="color:red;"></span>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <label for="company_contact_no">Contact Number</label>
                                        <input type="tel" class="form-control" id="company_contact_no" name="contact_number"
                                            placeholder="0123456789" >
                                        <span class="company-error-msg" id="company-error-contact_number" style="color:red;"></span>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <label for="company_alternative_number">Alternative Number</label>
                                        <input type="tel" class="form-control" id="company_alternative_number"
                                            name="alternative_number" placeholder="0123456789">
                                        <span class="company-error-msg" id="company-error-alternative_number" style="color:red;"></span>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <label for="company_gst_number">GST Number</label>
                                        <input type="text" class="form-control" id="company_gst_number" name="gst_number"
                                            placeholder="GST Number">
                                        <span class="company-error-msg" id="company-error-gst_number" style="color:red;"></span>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <label for="company_pan_number">PAN Number</label>
                                        <input type="text" class="form-control" id="company_pan_number" name="pan_number"
                                            placeholder="PAN Number">
                                        <span class="company-error-msg" id="company-error-pan_number" style="color:red;"></span>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <label for="company_modal_country">Select Country</label>
                                        <span style="color:red;">*</span>
                                        <select class="form-control" id="company_modal_country" name="country" >
                                            <option selected="" disabled="">Select your Country</option>
                                        </select>
                                        <span class="company-error-msg" id="company-error-country" style="color:red;"></span>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <label for="company_modal_state">Select State</label>
                                        <span style="color:red;">*</span>
                                        <select class="form-control" id="company_modal_state" name="state" >
                                            <option selected="" disabled="">Select your State</option>
                                        </select>
                                        <span class="company-error-msg" id="company-error-state" style="color:red;"></span>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <label for="company_modal_city">Select City</label>
                                        <span style="color:red;">*</span>
                                        <select class="form-control" id="company_modal_city" name="city" >
                                            <option selected="" disabled="">Select your City</option>
                                        </select>
                                        <span class="company-error-msg" id="company-error-city" style="color:red;"></span>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <label for="company_modal_pincode">Pincode</label>
                                        <input type="text" class="form-control" id="company_modal_pincode" name="pincode"
                                            placeholder="Pin Code" >
                                        <span class="company-error-msg" id="company-error-pincode" style="color:red;"></span>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <label for="company_house_no">House no./ Building Name</label>
                                        <textarea class="form-control" id="company_house_no" name="house_no_building_name"
                                            rows="2" placeholder="e.g. 2nd floor/ 04 ABC Appartment"></textarea>
                                        <span class="company-error-msg" id="company-error-house_no_building_name" style="color:red;"></span>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <label for="company_road_name">Road Name/Area/Colony</label>
                                        <textarea class="form-control" id="company_road_name" name="road_name_area_colony"
                                            rows="2" placeholder="e.g. sardar patel road, jagatpur"></textarea>
                                        <span class="company-error-msg" id="company-error-road_name_area_colony" style="color:red;"></span>
                                    </div>

                                    <div class="col-sm-12 mb-2">
                                        <label for="company_img">Company Logo</label><br>
                                        <input type="file" class="form-control-file" id="company_img" name="img">
                                        <p class="text-primary mb-0">Please select JPG, JPEG, or PNG smaller than 1 MB.</p>
                                        <span class="company-error-msg" id="company-error-img" style="color:red;"></span>
                                    </div>

                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary" id="company_submitBtn">Save</button>
                                <button type="reset" class="btn iq-bg-danger mr-2" id="company_resetbtn">Reset</button>
                                <button type="button" class="btn btn-secondary" id="company_cancelBtn">Cancel</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @push('ajax')
    <script src="{{asset('admin/js/select2.min.js')}}"></script>
    <script>
        $('document').ready(function() {
            var duplicateData = @json($duplicateData);
            let viewthirdpartycompanies = "{{ session('user_permissions.quotationmodule.thirdpartyquotation.view') == '1' }}";
            const API_TOKEN = "{{ session()->get('api_token') }}";
            const COMPANY_ID = "{{ session()->get('company_id') }}";
            const USER_ID = "{{ session()->get('user_id') }}";

            let allColumnData = [];
            let allColumnNames = [];
            let hiddencolumn = 0;
            let formula = [];
            let sgst, cgst, gst, currentcurrency, currentcurrencysymbol;
            var addname = 1;
          // ─── Helper: refresh tooltips ─────────────────────────────────────
            function managetooltip() {
                $('body').find('[data-toggle="tooltip"]').tooltip('dispose');
                $('body').find('[data-toggle="tooltip"]').tooltip();
            }

            // ─── Customer form dynamic required ──────────────────────────────
            $('.withgstspan').hide();

            $('#add_new_div').on('change', 'input, textarea', function() {
                $('#add_new_div input, #add_new_div textarea').each(function() {
                    $(this).attr('title', $(this).val());
                });
            });

            $('#company_name').on('change keyup', function() {
                var val = $(this).val();
                if (val != '') {
                    $('.withgstspan').show();
                    $('.withoutgstspan').hide();
                    $('.withgstinput').attr('required', true);
                    $('.withoutgstinput').removeAttr('required');
                } else {
                    $('.withgstspan').hide();
                    $('.withoutgstspan').show();
                    $('.withoutgstinput').attr('required', true);
                    $('.withgstinput').removeAttr('required');
                }
            });

            // ─── Fetch other settings (GST, quotation number/date) ────────────
            ajaxRequest('GET', "{{ route('getquotationoverduedays.index') }}", {
                token: API_TOKEN,
                company_id: COMPANY_ID,
                user_id: USER_ID
            }).done(function(response) {
                if (response.status == 200 && response.overdueday != '') {
                    var othersettingdata = response.overdueday[0];
                    sgst = othersettingdata['sgst'];
                    cgst = othersettingdata['cgst'];
                    gst  = othersettingdata['gst'];
                    manualquotationnumber = othersettingdata['quotation_number'];
                    manualquotationdate   = othersettingdata['quotation_date'];
                    thirdpartyquotation   = othersettingdata['third_party_quotation'];
                    totalgstpercentage    = sgst + cgst;

                    // Only set GST display if NOT duplicate (duplicate sets its own)
                    if (!duplicateData) {
                        updateGSTDisplay(sgst, cgst, totalgstpercentage);

                        if (gst != 0) {
                            $('#sgstline,#cgstline').hide();
                        } else {
                            $('#gstline').hide();
                        }

                        if (manualquotationnumber == 0) $('#quotation_number_div').hide();
                        if (manualquotationdate == 0)   $('#quotation_date_div').hide();
                        if (thirdpartyquotation == 0 || !viewthirdpartycompanies) $('#company_div').hide();
                    }

                } else if (response.status == 500) {
                    Toast.fire({ icon: "error", title: response.message });
                }
                loaderhide();
            }).fail(function(xhr) {
                loaderhide();
                handleAjaxError(xhr);
            });

            // ─── Helper: update GST % display ────────────────────────────────
            function updateGSTDisplay(sgst, cgst, totalgstpercentage) {
                $('#sgstpercentage').text(sgst % 1 === 0 ? `(${sgst}.00 %)` : `(${sgst} %)`);
                $('#cgstpercentage').text(cgst % 1 === 0 ? `(${cgst}.00 %)` : `(${cgst} %)`);
                $('#gstpercentage').text(totalgstpercentage % 1 === 0 ? `(${totalgstpercentage}.00 %)` : `(${totalgstpercentage} %)`);
            }

            // ─── Fetch formula ────────────────────────────────────────────────
            ajaxRequest('GET', "{{ route('quotationformula.index') }}", {
                token: API_TOKEN,
                company_id: COMPANY_ID,
                user_id: USER_ID
            }).done(function(response) {
                if (response.status == 200 && response.quotationformula != '') {
                    formula = response.quotationformula;
                } else if (response.status == 500) {
                    Toast.fire({ icon: "error", title: response.message });
                    loaderhide();
                }
            }).fail(function(xhr) {
                loaderhide();
                handleAjaxError(xhr);
            });

            // ─── Fetch column names and build table ───────────────────────────
            ajaxRequest('GET', "{{ route('quotation.columnname') }}", {
                token: API_TOKEN,
                company_id: COMPANY_ID,
                user_id: USER_ID
            }).done(function(response) {
                allColumnData = response.columnname;

                if (response.status == 200 && response.columnname != '') {
                    $.each(response.columnname, function(key, value) {
                        $.each(value, function(innerKey, innerValue) {
                            if (innerKey === 'column_name') {
                                allColumnNames.push(innerValue);
                            }
                        });
                    });

                    $('#columnname').prepend(
                        `${allColumnData.map(col => `<th style="width: ${col.column_width}%; ${col.is_hide ? 'display:none;' : ''}">${col.column_name}</th>`).join('')}
                        <th>Amount</th><th>Move</th><th>Action</th>`
                    );

                    // ── If duplicating: populate rows from duplicateData ──────
                    if (duplicateData) {
                        populateDuplicateData();
                    } else {
                        // Normal create: append one blank row
                        appendBlankRow();
                    }

                    managetooltip();

                } else if (response.status == 500) {
                    Toast.fire({ icon: "error", title: response.message });
                    loaderhide();
                } else {
                    $('#columnname').append(`<th>Name</th><th>Description</th><th>Quantity</th>`);
                }

                $('.automaticcolspan').attr('colspan', allColumnNames.length - hiddencolumn);
                $('.newdivautomaticcolspan').attr('colspan', allColumnNames.length - hiddencolumn + 3);

            }).fail(function(xhr) {
                loaderhide();
                handleAjaxError(xhr);
            });

            // ─── Populate duplicate data (runs after allColumnData is ready) ──
            function populateDuplicateData() {
                var quotationdetails = duplicateData.data.quotationdetails; // ✅ controller sends full data object
                var productdetails   = duplicateData.data.productdetails;

                // Header fields
                customers(quotationdetails.customer_id);

                // var third_party_quotation = quotationdetails.third_party_quotation;
                if (thirdpartyquotation  == 0 || !viewthirdpartycompanies) {
                    $('#company_div').hide();
                    if (viewthirdpartycompanies) companies();
                } else {
                    companies(quotationdetails.company_id);
                }
                const currencyid = quotationdetails.currency_id;
                console.log('currencyid', currencyid);
                $('#currency').val(currencyid);

                if ((quotationdetails.cgst === null || quotationdetails.cgst === 0) &&
                    (quotationdetails.sgst === null || quotationdetails.sgst === 0) &&
                    (quotationdetails.gst  === null || quotationdetails.gst  === 0)) {
                    $('#type').val(2);
                    $('#sgstline,#cgstline,#gstline').hide();
                } else {
                    $('#type').val(1);
                }

                $('#quotation_date').val(quotationdetails.quotation_date_formatted);
                $('#quotation_number').val(quotationdetails.quotation_number);
                $('#notes').val(quotationdetails.notes);

                // GST settings
                const gstsettings  = JSON.parse(quotationdetails.gstsettings);
                sgst               = parseFloat(gstsettings.sgst);
                cgst               = parseFloat(gstsettings.cgst);
                gst                = parseFloat(gstsettings.gst);
                totalgstpercentage = sgst + cgst;

                updateGSTDisplay(sgst, cgst, totalgstpercentage);

                if (gst != 0) {
                    $('#sgstline,#cgstline').hide();
                } else {
                    $('#gstline').hide();
                }

                // Product rows
                const targetRow    = $('#add_new_div');
                let dynamicidcount = 1;

                $.each(productdetails, function(key, value) {
                    targetRow.append(`
                        <tr class="iteam_row_${dynamicidcount}">
                            ${allColumnData.map(columnData => {
                                var columnName = columnData.column_name.replace(/\s+/g, '_');
                                var hiddenClass = columnData.is_hide === 1 ? 'd-none' : '';
                                if (columnData.column_type === 'time') {
                                    return `<td class="quotationsubmit ${hiddenClass}">
                                        <input type="time" name="${columnName}_${dynamicidcount}" id="${columnName}_${dynamicidcount}" value="${value[columnName] || ''}" class="form-control iteam_${columnName}">
                                    </td>`;
                                } else if (['number','percentage','decimal'].includes(columnData.column_type)) {
                                    return `<td class="quotationsubmit ${hiddenClass}">
                                        <input type="number" step="any" name="${columnName}_${dynamicidcount}" id="${columnName}_${dynamicidcount}" value="${value[columnName] || ''}" data-id="${dynamicidcount}" class="form-control iteam_${columnName} counttotal calculation" min=0>
                                    </td>`;
                                } else if (columnData.column_type === 'longtext') {
                                    return `<td class="quotationsubmit ${hiddenClass}">
                                        <textarea name="${columnName}_${dynamicidcount}" id="${columnName}_${dynamicidcount}" class="form-control iteam_${columnName}" rows="1">${value[columnName] || ''}</textarea>
                                    </td>`;
                                } else {
                                    return `<td class="quotationsubmit ${hiddenClass}">
                                        <input type="text" name="${columnName}_${dynamicidcount}" id="${columnName}_${dynamicidcount}" value="${value[columnName] || ''}" class="form-control iteam_${columnName}" placeholder="${columnData.column_name}">
                                    </td>`;
                                }
                            }).join('')}
                            <td>
                                <input type="number" step="any" value="${value.amount || ''}" data-id="${dynamicidcount}" class="form-control iteam_Amount changeprice calculation" id="Amount_${dynamicidcount}" name="Amount_${dynamicidcount}" min=0 required>
                            </td>
                            <td>
                                <span class="table-up"><a href="#!" class="indigo-text"><i class="fa fa-long-arrow-up" aria-hidden="true"></i></a></span>
                                <span class="table-down"><a href="#!" class="indigo-text"><i class="fa fa-long-arrow-down" aria-hidden="true"></i></a></span>
                            </td>
                            <td>
                                <span class="duplicate-row" data-id="${dynamicidcount}">
                                    <button type="button" data-toggle="tooltip" data-placement="bottom" data-original-title="Duplicate Row" class="btn iq-bg-primary btn-rounded btn-sm mx-0 my-1"><i class="ri-align-bottom"></i></button>
                                </span>
                                <span class="remove-row" data-id="${dynamicidcount}">
                                    <button type="button" data-toggle="tooltip" data-placement="bottom" data-original-title="Delete Row" class="btn iq-bg-danger btn-rounded btn-sm mx-0 my-1"><i class="ri-delete-bin-2-line"></i></button>
                                </span>
                            </td>
                        </tr>
                    `);

                    managetooltip();
                    addname = dynamicidcount;
                    dynamiccalculaton(`#Amount_${dynamicidcount}`);

                    currentcurrency       = $('#currency option:selected').data('currency');
                    currentcurrencysymbol = $('#currency option:selected').data('symbol');
                    $('.currentcurrencysymbol').text(currentcurrencysymbol);

                    dynamicidcount++;
                });
            }

            // ─── Append one blank row (normal create) ─────────────────────────
            function appendBlankRow() {
                $('#add_new_div').append(`
                    <tr class="iteam_row_1">
                        ${allColumnData.map(columnData => {
                            var columnName  = columnData.column_name.replace(/\s+/g, '_');
                            var hiddenClass = columnData.is_hide === 1 ? 'd-none' : '';
                            if (columnData.is_hide === 1) hiddencolumn++;
                            if (columnData.column_type === 'time') {
                                return `<td class="quotationsubmit ${hiddenClass}"><input type="time" name="${columnName}_1" id="${columnName}_1" class="form-control iteam_${columnName}"></td>`;
                            } else if (['number','percentage','decimal'].includes(columnData.column_type)) {
                                return `<td class="quotationsubmit ${hiddenClass}"><input type="number" step="any" name="${columnName}_1" id="${columnName}_1" data-id="1" class="form-control iteam_${columnName} counttotal calculation" min=0></td>`;
                            } else if (columnData.column_type === 'longtext') {
                                return `<td class="quotationsubmit ${hiddenClass}"><textarea name="${columnName}_1" id="${columnName}_1" class="form-control iteam_${columnName}" rows="1"></textarea></td>`;
                            } else {
                                return `<td class="quotationsubmit ${hiddenClass}"><input type="text" name="${columnName}_1" id="${columnName}_1" class="form-control iteam_${columnName}" placeholder="${columnData.column_name}"></td>`;
                            }
                        }).join('')}
                        <td><input type="number" step="any" data-id="1" class="form-control iteam_Amount changeprice calculation" id="Amount_1" name="Amount_1" min=0 required placeholder="Amount"></td>
                        <td>
                            <span class="table-up"><a href="#!" class="indigo-text"><i class="fa fa-long-arrow-up" aria-hidden="true"></i></a></span>
                            <span class="table-down"><a href="#!" class="indigo-text"><i class="fa fa-long-arrow-down" aria-hidden="true"></i></a></span>
                        </td>
                        <td>
                            <span class="duplicate-row" data-id="1">
                                <button type="button" data-toggle="tooltip" data-placement="bottom" data-original-title="Duplicate Row" data-id="1" class="btn iq-bg-primary btn-rounded btn-sm mx-0 my-1"><i class="ri-align-bottom"></i></button>
                            </span>
                            <span class="remove-row" data-id="1">
                                <button type="button" data-toggle="tooltip" data-placement="bottom" data-original-title="Delete Row" data-id="1" class="btn iq-bg-danger btn-rounded btn-sm mx-0 my-1"><i class="ri-delete-bin-2-line"></i></button>
                            </span>
                        </td>
                    </tr>
                `);
            }

            // ─── Move row up ──────────────────────────────────────────────────
            $("#table").on("click", ".table-up", function() {
                const $row = $(this).closest("tr");
                if ($row.index() !== 0) $row.prev().before($row);
            });

            // ─── Currency dropdown ────────────────────────────────────────────
            ajaxRequest('GET', "{{ route('country.index') }}", {
                token: API_TOKEN,
                company_id: COMPANY_ID,
                user_id: USER_ID
            }).done(function(response) {
                if (response.status == 200 && response.country != '') {
                    $.each(response.country, function(key, value) {
                        $('#currency').append(
                            `<option data-symbol='${value.currency_symbol}' data-currency='${value.currency}' value='${value.id}'>${value.country_name} - ${value.currency_name} - ${value.currency} - ${value.currency_symbol}</option>`
                        );
                    });
                    // Set currency after options are loaded
                    if (duplicateData) {
                        $('#currency').val(duplicateData.data.quotationdetails.currency_id);
                        currentcurrency       = $('#currency option:selected').data('currency');
                        currentcurrencysymbol = $('#currency option:selected').data('symbol');
                        $('.currentcurrencysymbol').text(currentcurrencysymbol);
                    }
                } else {
                    $('#currency').append(`<option disabled>No Data found</option>`);
                }
            }).fail(function(xhr) {
                loaderhide();
                handleAjaxError(xhr);
            });

            loaderhide();

            // ─── Customer dropdown ────────────────────────────────────────────
            function customers(customerid = 0) {
                loadershow();
                $('#customer').html(`
                    <option selected="" value=0 disabled="">Select Customer</option>
                    <option value="add_customer">Add New Customer</option>
                `);
                ajaxRequest('GET', "{{ route('customer.quotationcustomer') }}", {
                    token: API_TOKEN,
                    company_id: COMPANY_ID,
                    user_id: USER_ID
                }).done(function(response) {
                    if (response.status == 200 && response.customer != '') {
                        $.each(response.customer, function(key, value) {
                            const customerDetails = [value.firstname, value.lastname, value.company_name, value.contact_no, value.email].filter(Boolean).join(' - ');
                            $('#customer').append(`<option data-gstno='${value.gst_no}' value='${value.id}'>${customerDetails}</option>`);
                        });
                        $('#customer').val(customerid);
                        $('.select2').select2();

                        if (customerid != 0) {
                            loadershow();
                            let customerSearchUrl = "{{route('customer.search','__customerId__')}}".replace('__customerId__', customerid);
                            ajaxRequest('GET', customerSearchUrl, {
                                token: API_TOKEN,
                                company_id: COMPANY_ID,
                                user_id: USER_ID
                            }).done(function(response) {
                                if (response.status == 200 && response.customers != '') {
                                    var countryid = response.customer.country_id;
                                    if (countryid != null) {
                                        $('#currency').val(countryid);
                                        currentcurrency       = $('#currency option:selected').data('currency');
                                        currentcurrencysymbol = $('#currency option:selected').data('symbol');
                                        $('.currentcurrencysymbol').text(currentcurrencysymbol);
                                    }
                                } else if (response.status == 500) {
                                    Toast.fire({ icon: "error", title: response.message });
                                }
                                loaderhide();
                            }).fail(function(xhr) {
                                loaderhide();
                                handleAjaxError(xhr);
                            });
                        }
                    } else if (response.status == 500) {
                        Toast.fire({ icon: "error", title: response.message });
                    } else {
                        $('#customer').append(`<option disabled>No Data found</option>`);
                    }
                    loaderhide();
                }).fail(function(xhr) {
                    loaderhide();
                    handleAjaxError(xhr);
                });
            }

            customers();

            // ─── Company dropdown ─────────────────────────────────────────────
            function companies(companyid = 0) {
                loadershow();
                $('#company').html(`
                    <option selected="" value='0'>Select Company</option>
                    <option value="add_company">Add New Company</option>
                `);
                ajaxRequest('GET', "{{ route('quotation.companylist') }}", {
                    token: API_TOKEN,
                    company_id: COMPANY_ID,
                    user_id: USER_ID
                }).done(function(response) {
                    if (response.status == 200 && response.company != '') {
                        $.each(response.company, function(key, value) {
                            const companyDetails = [value.name, value.email, value.contact_no, value.alternative_number].filter(Boolean).join(' - ');
                            $('#company').append(`<option value='${value.id}'>${companyDetails}</option>`);
                        });
                        $('#company').val(companyid);
                        $('.select2').select2();
                    } else if (response.status == 500) {
                        Toast.fire({ icon: "error", title: response.message });
                    } else {
                        $('#company').append(`<option disabled>No Data found</option>`);
                    }
                    loaderhide();
                }).fail(function(xhr) {
                    loaderhide();
                    handleAjaxError(xhr);
                });
            }

            if (viewthirdpartycompanies) companies();

            // ─── Company modal: country/state/city ────────────────────────────
            ajaxRequest('GET', "{{ route('country.index') }}", { token: API_TOKEN })
                .done(function(response) {
                    if (response.status == 200) {
                        $.each(response.country, function(key, value) {
                            $('#company_modal_country').append(`<option value='${value.id}'>${value.country_name}</option>`);
                        });
                        $('#company_modal_country').val("{{ session('user')['country_id'] }}");
                        loadCompanyState();
                    }
                });

            function loadCompanyState(id = "{{ session('user')['country_id'] }}") {
                $('#company_modal_state').html(`<option selected="" disabled="">Select your State</option>`);
                let url = "{{route('state.search','id')}}".replace('id', id);
                ajaxRequest('GET', url, { token: API_TOKEN }).done(function(response) {
                    if (response.status == 200) {
                        $.each(response.state, function(key, value) {
                            $('#company_modal_state').append(`<option value='${value.id}'>${value.state_name}</option>`);
                        });
                        $('#company_modal_state').val("{{ session('user')['state_id'] }}");
                        loadCompanyCity();
                    }
                });
            }

            function loadCompanyCity(id = "{{ session('user')['state_id'] }}") {
                $('#company_modal_city').html(`<option selected="" disabled="">Select your City</option>`);
                let url = "{{route('city.search','id')}}".replace('id', id);
                ajaxRequest('GET', url, { token: API_TOKEN }).done(function(response) {
                    if (response.status == 200) {
                        $.each(response.city, function(key, value) {
                            $('#company_modal_city').append(`<option value='${value.id}'>${value.city_name}</option>`);
                        });
                        $('#company_modal_city').val("{{ session('user')['city_id'] }}");
                    }
                });
            }

            $('#company_modal_country').on('change', function() { loadCompanyState($(this).val()); });
            $('#company_modal_state').on('change',   function() { loadCompanyCity($(this).val()); });

            $('#company_cancelBtn').on('click', function() {
                $('#companyform')[0].reset();
                $('#companyModalScrollable').modal('hide');
                $('#company option:first').prop('selected', true);
            });

            $('#companyform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.company-error-msg').text('');
                let formdata = new FormData(this);
                $.ajax({
                    type: 'POST',
                    url: "{{ route('quotation.company.store') }}",
                    data: formdata,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status == 200) {
                            $('#companyform')[0].reset();
                            $('#companyModalScrollable').modal('hide');
                            companies(response.company_detail_id);
                            Toast.fire({ icon: "success", title: response.message });
                        } else {
                            Toast.fire({ icon: "error", title: response.message });
                        }
                        loaderhide();
                    },
                    error: function(xhr) {
                        loaderhide();
                        if (xhr.status === 422) {
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                $('#company-error-' + key).text(value[0]);
                            });
                        } else {
                            Toast.fire({ icon: "error", title: "An error occurred" });
                        }
                    }
                });
            });

            $('#company').on('change', function() {
                if ($(this).val() == 'add_company') $('#companyModalScrollable').modal('show');
                loaderhide();
            });

            // ─── Customer change ──────────────────────────────────────────────
            $('#customer').on('change', function() {
                loadershow();
                var customerid = $(this).val();
                if (customerid == 'add_customer') {
                    $('#exampleModalScrollable').modal('show');
                    return;
                }
                let customerSearchUrl = "{{route('customer.search','__customerId__')}}".replace('__customerId__', customerid);
                ajaxRequest('GET', customerSearchUrl, {
                    token: API_TOKEN, company_id: COMPANY_ID, user_id: USER_ID
                }).done(function(response) {
                    if (response.status == 200 && response.customers != '') {
                        var countryid = response.customer.country_id;
                        if (countryid != null) {
                            $('#currency').val(countryid);
                            currentcurrency       = $('#currency option:selected').data('currency');
                            currentcurrencysymbol = $('#currency option:selected').data('symbol');
                            $('.currentcurrencysymbol').text(currentcurrencysymbol);
                        }
                    } else if (response.status == 500) {
                        Toast.fire({ icon: "error", title: response.message });
                    }
                    loaderhide();
                }).fail(function(xhr) { loaderhide(); handleAjaxError(xhr); });
            });

            // ─── Currency change ──────────────────────────────────────────────
            $('#currency').on('change', function() {
                currentcurrency       = $('#currency option:selected').data('currency');
                currentcurrencysymbol = $('#currency option:selected').data('symbol');
                $('.currentcurrencysymbol').text(currentcurrencysymbol);
            });

            // ─── Add new row button ───────────────────────────────────────────
            $('.add_div').on('click', function() {
                addname++;
                adddiv();
            });

            function adddiv() {
                $('#add_new_div').append(`
                    <tr class="iteam_row_${addname}">
                        ${allColumnData.map(columnData => {
                            var columnName  = columnData.column_name.replace(/\s+/g, '_');
                            var hiddenClass = columnData.is_hide === 1 ? 'd-none' : '';
                            if (columnData.column_type === 'time') {
                                return `<td class="quotationsubmit ${hiddenClass}"><input type="time" name="${columnName}_${addname}" id="${columnName}_${addname}" class="form-control iteam_${columnName}"></td>`;
                            } else if (['number','percentage','decimal'].includes(columnData.column_type)) {
                                return `<td class="quotationsubmit ${hiddenClass}"><input type="number" step="any" name="${columnName}_${addname}" id="${columnName}_${addname}" data-id="${addname}" class="form-control iteam_${columnName} counttotal calculation" min=0></td>`;
                            } else if (columnData.column_type === 'longtext') {
                                return `<td class="quotationsubmit ${hiddenClass}"><textarea name="${columnName}_${addname}" id="${columnName}_${addname}" class="form-control iteam_${columnName}" rows="1"></textarea></td>`;
                            } else {
                                return `<td class="quotationsubmit ${hiddenClass}"><input type="text" name="${columnName}_${addname}" id="${columnName}_${addname}" class="form-control iteam_${columnName}" placeholder="${columnData.column_name}"></td>`;
                            }
                        }).join('')}
                        <td><input type="number" step="any" data-id="${addname}" id="Amount_${addname}" name="Amount_${addname}" class="form-control iteam_Amount changeprice calculation" placeholder="Amount" min=0 required></td>
                        <td>
                            <span class="table-up"><a href="#!" class="indigo-text"><i class="fa fa-long-arrow-up" aria-hidden="true"></i></a></span>
                            <span class="table-down"><a href="#!" class="indigo-text"><i class="fa fa-long-arrow-down" aria-hidden="true"></i></a></span>
                        </td>
                        <td>
                            <span class="duplicate-row" data-id="${addname}">
                                <button type="button" data-toggle="tooltip" data-placement="bottom" data-original-title="Duplicate Row" class="btn iq-bg-primary btn-rounded btn-sm mx-0 my-1"><i class="ri-align-bottom"></i></button>
                            </span>
                            <span class="remove-row" data-id="${addname}">
                                <button type="button" data-toggle="tooltip" data-placement="bottom" data-original-title="Delete Row" class="btn iq-bg-danger btn-rounded btn-sm mx-0 my-1"><i class="ri-delete-bin-2-line"></i></button>
                            </span>
                        </td>
                    </tr>
                `);
                managetooltip();
            }

            // ─── Duplicate row ────────────────────────────────────────────────
            $(document).on('click', '.duplicate-row', function() {
                var element = $(this);
                showConfirmationDialog(
                    'Are you sure?', 'to add duplicate column?', 'Yes', 'No, cancel', 'question',
                    () => {
                        addname++;
                        var id = element.data('id');
                        duplicatediv(id);
                        dynamiccalculaton(`#Amount_${addname}`);
                        managetooltip();
                    }
                );
            });

            function duplicatediv(id) {
                $('#add_new_div').append(`
                    <tr class="iteam_row_${addname}">
                        ${allColumnData.map(columnData => {
                            var columnName  = columnData.column_name.replace(/\s+/g, '_');
                            var hiddenClass = columnData.is_hide === 1 ? 'd-none' : '';
                            if (columnData.column_type === 'time') {
                                return `<td class="quotationsubmit ${hiddenClass}"><input type="time" name="${columnName}_${addname}" id="${columnName}_${addname}" value="${$('#'+columnName+'_'+id).val()||''}" class="form-control iteam_${columnName}"></td>`;
                            } else if (['number','percentage','decimal'].includes(columnData.column_type)) {
                                return `<td class="quotationsubmit ${hiddenClass}"><input type="number" step="any" name="${columnName}_${addname}" id="${columnName}_${addname}" value="${$('#'+columnName+'_'+id).val()||''}" data-id="${addname}" class="form-control iteam_${columnName} counttotal calculation" min=0></td>`;
                            } else if (columnData.column_type === 'longtext') {
                                return `<td class="quotationsubmit ${hiddenClass}"><textarea name="${columnName}_${addname}" id="${columnName}_${addname}" class="form-control iteam_${columnName}" rows="1">${$('#'+columnName+'_'+id).val()||''}</textarea></td>`;
                            } else {
                                return `<td class="quotationsubmit ${hiddenClass}"><input type="text" name="${columnName}_${addname}" id="${columnName}_${addname}" value="${$('#'+columnName+'_'+id).val()||''}" class="form-control iteam_${columnName}" placeholder="${columnData.column_name}"></td>`;
                            }
                        }).join('')}
                        <td><input type="number" step="any" data-id="${addname}" id="Amount_${addname}" value="${$('#Amount_'+id).val()||''}" name="Amount_${addname}" class="form-control iteam_Amount changeprice calculation" placeholder="Amount" min=0 required></td>
                        <td>
                            <span class="table-up"><a href="#!" class="indigo-text"><i class="fa fa-long-arrow-up" aria-hidden="true"></i></a></span>
                            <span class="table-down"><a href="#!" class="indigo-text"><i class="fa fa-long-arrow-down" aria-hidden="true"></i></a></span>
                        </td>
                        <td>
                            <span class="duplicate-row" data-id="${addname}">
                                <button type="button" data-toggle="tooltip" data-placement="bottom" data-original-title="Duplicate Row" class="btn iq-bg-primary btn-rounded btn-sm mx-0 my-1"><i class="ri-align-bottom"></i></button>
                            </span>
                            <span class="remove-row" data-id="${addname}">
                                <button type="button" data-toggle="tooltip" data-placement="bottom" data-original-title="Delete Row" class="btn iq-bg-danger btn-rounded btn-sm mx-0 my-1"><i class="ri-delete-bin-2-line"></i></button>
                            </span>
                        </td>
                    </tr>
                `);
                managetooltip();
            }

            // ─── Delete row ───────────────────────────────────────────────────
            $(document).on('click', '.remove-row', function() {
                var element = $(this);
                showConfirmationDialog(
                    'Are you sure?', 'to delete this?', 'Yes, delete', 'No, cancel', 'question',
                    () => {
                        managetooltip();
                        element.closest("tr").remove();
                    }
                );
            });

            // ─── GST type toggle ──────────────────────────────────────────────
            $('#type').on('change', function() {
                if ($(this).val() == 2) {
                    $('#sgstline,#cgstline,#gstline').hide();
                    var totalval    = parseFloat($('#totalamount').val()) || 0;
                    var grandtotalval = Math.round(totalval);
                    var roundoffval   = Math.abs(grandtotalval - totalval).toFixed(2);
                    $('#roundoff').val(grandtotalval >= totalval ? (roundoffval == 0 ? roundoffval : `+ ${roundoffval}`) : (roundoffval == 0 ? roundoffval : `- ${roundoffval}`));
                    $('#grandtotal').val(grandtotalval);
                    $('#sgst,#cgst,#gst').val(0);
                } else {
                    if (gst != 0) {
                        $('#sgstline,#cgstline').hide();
                        $('#gstline').show();
                    } else {
                        $('#sgstline,#cgstline').show();
                        $('#gstline').hide();
                    }
                    dynamiccalculaton();
                }
            });

            // ─── Form submit ──────────────────────────────────────────────────
            $('#quotationform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.error-msg').text('');
                ajaxRequest('POST', "{{ route('quotation.store') }}", {
                    data: collectquotationDetails(),
                    iteam_data: collectRowData(),
                    token: API_TOKEN,
                    company_id: COMPANY_ID,
                    user_id: USER_ID
                }).done(function(response) {
                    if (response.status === 200) {
                        Toast.fire({ icon: "success", title: response.message });
                        window.location = "{{ route('admin.quotation') }}";
                    } else {
                        Toast.fire({ icon: "error", title: response.message });
                    }
                }).fail(function(xhr) { loaderhide(); handleAjaxError(xhr); });
            });

            function collectRowData() {
                const iteam_data = [];
                $('tbody#add_new_div tr').each(function() {
                    const rowData  = {};
                    const rowNumber = $(this).attr('class').match(/\d+/)[0];
                    $.each(allColumnNames, function(key, columnName) {
                        const col = columnName.replace(/\s+/g, '_');
                        rowData[col] = $(this).find(`#${col}_${rowNumber}`).val();
                    }.bind(this));
                    rowData['amount'] = $(this).find(`#Amount_${rowNumber}`).val();
                    iteam_data.push(rowData);
                });
                return iteam_data;
            }

            function collectquotationDetails() {
                return {
                    company:          $('#company').val(),
                    country_id:       $('#country').val(),
                    user_id:          $('#created_by').val(),
                    company_id:       $('#company_id').val(),
                    quotation_date:   $('#quotation_date').val(),
                    quotation_number: $('#quotation_number').val(),
                    currency:         $('#currency').val(),
                    customer:         $('#customer').val(),
                    total_amount:     $('#totalamount').val(),
                    grandtotal:       $('#grandtotal').val(),
                    tax_type:         $('#type').val(),
                    notes:            $('#notes').val(),
                    gstsettings:      { sgst, cgst, gst },
                    ...getGSTValues()
                };
            }

            function getGSTValues() {
                return gst === 0
                    ? { sgst: $('#sgst').val(), cgst: $('#cgst').val() }
                    : { gst: $('#gst').val() };
            }

            // ─── Quotation number duplicate check ─────────────────────────────
            $('#quotation_number').on('blur', function() {
                $('.error-msg').text('');
                ajaxRequest('GET', "{{ route('quotation.checkquotationnumber') }}", {
                    quotation_number: $(this).val(),
                    token: API_TOKEN,
                    company_id: COMPANY_ID,
                    user_id: USER_ID
                }).fail(function(xhr) { loaderhide(); handleAjaxError(xhr); });
            });

            // ─── Cancel button ────────────────────────────────────────────────
            $('#cancelbtn').on('click', function() {
                loadershow();
                window.location.href = "{{route('admin.quotation')}}";
            });

            // ─── Dynamic calculation ──────────────────────────────────────────
            function dynamiccalculaton(targetdata) {
                var editid  = $(targetdata).data('id');
                var rowData = {};

                $.each(allColumnNames, function(key, value) {
                    var col = value.replace(/\s+/g, '_');
                    $(`table tr.iteam_row_${editid} td`).find(`input[type="number"]#${col}_${editid}`).each(function() {
                        rowData[value] = $(this).val();
                    });
                });

                var iteam_data = [rowData];

                function performCalculation(op, v1, v2) {
                    return op === '*' ? v1*v2 : op === '/' ? v1/v2 : op === '+' ? v1+v2 : op === '-' ? v1-v2 : 0;
                }

                formula.forEach(function(f) {
                    var v1  = parseFloat(iteam_data[0][f.first_column])  || 0;
                    var v2  = parseFloat(iteam_data[0][f.second_column]) || 0;
                    var out = performCalculation(f.operation, v1, v2);
                    iteam_data[0][f.output_column] = out.toFixed(2);
                    $(`#${f.output_column}_${editid}`).val(out.toFixed(2));
                });

                var total = 0;
                $('input.changeprice').each(function() { total += parseFloat($(this).val()) || 0; });
                total = total.toFixed(2);

                if (!isNaN(total)) {
                    $('#totalamount').val(total);
                    if ($('#type').val() == 1) {
                        var sgstvalue  = ((total * sgst) / 100).toFixed(2);
                        var cgstvalue  = ((total * cgst) / 100).toFixed(2);
                        if (gst == 0) {
                            $('#sgst').val(sgstvalue);
                            $('#cgst').val(cgstvalue);
                        } else {
                            $('#gst').val((parseFloat(sgstvalue) + parseFloat(cgstvalue)).toFixed(2));
                        }
                        var totalval      = parseFloat(total) + parseFloat(sgstvalue) + parseFloat(cgstvalue);
                        var grandtotalval = Math.round(totalval);
                        var roundoffval   = Math.abs(grandtotalval - totalval).toFixed(2);
                        $('#roundoff').val(grandtotalval >= totalval ? (roundoffval == 0 ? roundoffval : `+ ${roundoffval}`) : (roundoffval == 0 ? roundoffval : `- ${roundoffval}`));
                        $('#grandtotal').val(grandtotalval);
                    } else {
                        var totalval      = parseFloat(total);
                        var grandtotalval = Math.round(totalval);
                        var roundoffval   = Math.abs(grandtotalval - totalval).toFixed(2);
                        $('#roundoff').val(grandtotalval >= totalval ? (roundoffval == 0 ? roundoffval : `+ ${roundoffval}`) : (roundoffval == 0 ? roundoffval : `- ${roundoffval}`));
                        $('#grandtotal').val(grandtotalval);
                    }
                }
            }

            $(document).on('keyup change', '.calculation', function() {
                dynamiccalculaton(this);
            });

            // ─── Customer modal: country/state/city ───────────────────────────
            ajaxRequest('GET', "{{ route('country.index') }}", { token: API_TOKEN })
                .done(function(response) {
                    if (response.status == 200 && response.country != '') {
                        $.each(response.country, function(key, value) {
                            $('#modal_country').append(`<option value='${value.id}'>${value.country_name}</option>`);
                        });
                        $('#modal_country').val("{{ session('user')['country_id'] }}");
                        loadstate();
                    }
                    loaderhide();
                }).fail(function(xhr) { loaderhide(); handleAjaxError(xhr); });

            $('#modal_country').on('change', function() {
                loadershow();
                $('#modal_city').html(`<option selected="" disabled="">Select your city</option>`);
                loadstate($(this).val());
            });

            function loadstate(id = 0) {
                $('#modal_state').html(`<option selected="" disabled="">Select your State</option>`);
                var url = id == 0 ? "{{ route('state.search', session('user')['country_id']) }}" : "{{route('state.search','id')}}".replace('id', id);
                ajaxRequest('GET', url, { token: API_TOKEN }).done(function(response) {
                    if (response.status == 200 && response.state != '') {
                        $.each(response.state, function(key, value) {
                            $('#modal_state').append(`<option value='${value.id}'>${value.state_name}</option>`);
                        });
                        if (id == 0) {
                            $('#modal_state').val("{{ session('user')['state_id'] }}");
                            loadcity();
                        }
                    }
                    loaderhide();
                }).fail(function(xhr) { loaderhide(); handleAjaxError(xhr); });
            }

            $('#modal_state').on('change', function() { loadershow(); loadcity($(this).val()); });

            function loadcity(id = 0) {
                $('#modal_city').html(`<option selected="" disabled="">Select your City</option>`);
                var url = id == 0 ? "{{ route('city.search', session('user')['state_id']) }}" : "{{route('city.search','id')}}".replace('id', id);
                ajaxRequest('GET', url, { token: API_TOKEN }).done(function(response) {
                    if (response.status == 200 && response.city != '') {
                        $.each(response.city, function(key, value) {
                            $('#modal_city').append(`<option value='${value.id}'>${value.city_name}</option>`);
                        });
                        if (id == 0) $('#modal_city').val("{{ session('user')['city_id'] }}");
                    }
                    loaderhide();
                }).fail(function(xhr) { loaderhide(); handleAjaxError(xhr); });
            }

            $('#modal_cancelBtn').on('click', function() {
                $('#customerform')[0].reset();
                $('#exampleModalScrollable').modal('hide');
                $('#customer option:first').prop('selected', true);
            });

            $('#customerform').submit(function(event) {
                event.preventDefault();
                loadershow();
                $('.modal-error-msg').text('');
                let formdata = $(this).serialize() + '&customer_type=' + encodeURIComponent("{{ session('menu') }}");
                $.ajax({
                    type: 'POST',
                    url: "{{ route('customer.store') }}",
                    data: formdata,
                    success: function(response) {
                        if (response.status == 200) {
                            $('#customerform')[0].reset();
                            $('#exampleModalScrollable').modal('hide');
                            customers(response.customer_id);
                            Toast.fire({ icon: "success", title: response.message });
                        } else {
                            Toast.fire({ icon: "error", title: response.message });
                        }
                        loaderhide();
                    },
                    error: function(xhr) {
                        loaderhide();
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            var errorcontainer;
                            $.each(errors, function(key, value) {
                                $('#modal-error-' + key).text(value[0]);
                                errorcontainer = '#modal-error-' + key;
                            });
                            $('.modal-body').animate({ scrollTop: $(errorcontainer).position().top }, 1000);
                        } else {
                            Toast.fire({ icon: "error", title: "An error occurred" });
                        }
                    }
                });
            });

        });
    </script>
    @endpush
