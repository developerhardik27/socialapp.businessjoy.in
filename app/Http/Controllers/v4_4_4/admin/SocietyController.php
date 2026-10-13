<?php

namespace App\Http\Controllers\v4_4_4\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SocietyController extends Controller
{
    public $version;
    public function __construct()
    {
        // society module views exist only in v4_4_4
        $this->version = 'v4_4_4';
    }

    // ---------------- Family Relation ----------------
    public function familyrelation()
    {
        return view($this->version . '.admin.Society.familyrelation');
    }

    public function familyrelationcreate()
    {
        return view($this->version . '.admin.Society.familyrelationform', ['company_id' => Session::get('company_id')]);
    }

    public function familyrelationedit(string $id)
    {
        return view($this->version . '.admin.Society.familyrelationupdateform', ['company_id' => Session::get('company_id'), 'edit_id' => $id]);
    }

    // ---------------- Business Category ----------------
    public function businesscategory()
    {
        return view($this->version . '.admin.Society.businesscategory');
    }

    public function businesscategorycreate()
    {
        return view($this->version . '.admin.Society.businesscategoryform', ['company_id' => Session::get('company_id')]);
    }

    public function businesscategoryedit(string $id)
    {
        return view($this->version . '.admin.Society.businesscategoryupdateform', ['company_id' => Session::get('company_id'), 'edit_id' => $id]);
    }

    // ---------------- Business Sub Category ----------------
    public function businesssubcategory()
    {
        return view($this->version . '.admin.Society.businesssubcategory');
    }

    public function businesssubcategorycreate()
    {
        return view($this->version . '.admin.Society.businesssubcategoryform', ['company_id' => Session::get('company_id')]);
    }

    public function businesssubcategoryedit(string $id)
    {
        return view($this->version . '.admin.Society.businesssubcategoryupdateform', ['company_id' => Session::get('company_id'), 'edit_id' => $id]);
    }

    // ---------------- Family (with family persons) ----------------
    public function family()
    {
        return view($this->version . '.admin.Society.family');
    }

    public function familycreate()
    {
        return view($this->version . '.admin.Society.familyform', ['company_id' => Session::get('company_id')]);
    }

    public function familyedit(string $id)
    {
        return view($this->version . '.admin.Society.familyupdateform', ['company_id' => Session::get('company_id'), 'edit_id' => $id]);
    }

    // ---------------- Member ----------------
    public function member()
    {
        return view($this->version . '.admin.Society.member');
    }

    public function memberedit(string $id)
    {
        return view($this->version . '.admin.Society.memberupdateform', ['company_id' => Session::get('company_id'), 'edit_id' => $id]);
    }

    // ---------------- Biodata ----------------
    public function biodata()
    {
        return view($this->version . '.admin.Society.biodata');
    }

    public function biodatacreate()
    {
        return view($this->version . '.admin.Society.biodataform', ['company_id' => Session::get('company_id')]);
    }

    public function biodataedit(string $id)
    {
        return view($this->version . '.admin.Society.biodataupdateform', ['company_id' => Session::get('company_id'), 'edit_id' => $id]);
    }

    // ---------------- Karobari Member ----------------
    public function karobarimember()
    {
        return view($this->version . '.admin.Society.karobarimember');
    }

    public function karobarimembercreate()
    {
        return view($this->version . '.admin.Society.karobarimemberform', ['company_id' => Session::get('company_id')]);
    }

    // ---------------- Karobari Meeting ----------------
    public function karobarimeeting()
    {
        return view($this->version . '.admin.Society.karobarimeeting');
    }

    public function karobarimeetingcreate()
    {
        return view($this->version . '.admin.Society.karobarimeetingform', ['company_id' => Session::get('company_id')]);
    }

    public function karobarimeetingedit(string $id)
    {
        return view($this->version . '.admin.Society.karobarimeetingupdateform', ['company_id' => Session::get('company_id'), 'edit_id' => $id]);
    }

    // ---------------- Meeting Attendance ----------------
    public function attendance()
    {
        return view($this->version . '.admin.Society.meetingattendance');
    }

    public function attendancecreate()
    {
        return view($this->version . '.admin.Society.meetingattendanceform', ['company_id' => Session::get('company_id')]);
    }

    public function attendanceedit(string $id)
    {
        return view($this->version . '.admin.Society.meetingattendanceupdateform', ['company_id' => Session::get('company_id'), 'edit_id' => $id]);
    }

    // ---------------- Donation Type ----------------
    public function donationtype()
    {
        return view($this->version . '.admin.Society.donationtype');
    }

    public function donationtypecreate()
    {
        return view($this->version . '.admin.Society.donationtypeform', ['company_id' => Session::get('company_id')]);
    }

    public function donationtypeedit(string $id)
    {
        return view($this->version . '.admin.Society.donationtypeupdateform', ['company_id' => Session::get('company_id'), 'edit_id' => $id]);
    }

    // ---------------- Donor ----------------
    public function donor()
    {
        return view($this->version . '.admin.Society.donor');
    }

    public function donorcreate()
    {
        return view($this->version . '.admin.Society.donorform', ['company_id' => Session::get('company_id')]);
    }

    public function donoredit(string $id)
    {
        return view($this->version . '.admin.Society.donorupdateform', ['company_id' => Session::get('company_id'), 'edit_id' => $id]);
    }

    // ---------------- Post ----------------
    public function post()
    {
        return view($this->version . '.admin.Society.post');
    }

    public function postcreate()
    {
        return view($this->version . '.admin.Society.postform', ['company_id' => Session::get('company_id')]);
    }

    public function postedit(string $id)
    {
        return view($this->version . '.admin.Society.postupdateform', ['company_id' => Session::get('company_id'), 'edit_id' => $id]);
    }

    // ---------------- Job ----------------
    public function job()
    {
        return view($this->version . '.admin.Society.job');
    }

    public function jobcreate()
    {
        return view($this->version . '.admin.Society.jobform', ['company_id' => Session::get('company_id')]);
    }

    public function jobedit(string $id)
    {
        return view($this->version . '.admin.Society.jobupdateform', ['company_id' => Session::get('company_id'), 'edit_id' => $id]);
    }

    // ---------------- Event ----------------
    public function event()
    {
        return view($this->version . '.admin.Society.event');
    }

    public function eventcreate()
    {
        return view($this->version . '.admin.Society.eventform', ['company_id' => Session::get('company_id')]);
    }

    public function eventedit(string $id)
    {
        return view($this->version . '.admin.Society.eventupdateform', ['company_id' => Session::get('company_id'), 'edit_id' => $id]);
    }

    // ---------------- Application ----------------
    public function application()
    {
        return view($this->version . '.admin.Society.application');
    }

    public function applicationcreate()
    {
        return view($this->version . '.admin.Society.applicationform', ['company_id' => Session::get('company_id')]);
    }

    public function applicationedit(string $id)
    {
        return view($this->version . '.admin.Society.applicationupdateform', ['company_id' => Session::get('company_id'), 'edit_id' => $id]);
    }

    // ---------------- Policy ----------------
    public function policy()
    {
        return view($this->version . '.admin.Society.policy');
    }

    public function policycreate()
    {
        return view($this->version . '.admin.Society.policyform', ['company_id' => Session::get('company_id')]);
    }

    public function policyedit(string $id)
    {
        return view($this->version . '.admin.Society.policyupdateform', ['company_id' => Session::get('company_id'), 'edit_id' => $id]);
    }
}
