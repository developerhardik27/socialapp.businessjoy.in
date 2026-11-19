<?php

namespace App\Http\Controllers\v4_4_4\api;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShakhController extends commonController
{
    public $userId, $companyId, $masterdbname, $rp, $shakhModel, $familyPersonModel;
    public function __construct(Request $request)
    {

        $this->companyId = $request->company_id;
        $this->userId = $request->user_id;

        $this->dbname($this->companyId);
        $user_rp = DB::connection('dynamic_connection')->table('user_permissions')->where('user_id', $this->userId)->value('rp');

        if (empty($user_rp)) {
            $this->customerrorresponse();
        }

        $this->rp = json_decode($user_rp, true);

        $this->masterdbname = DB::connection()->getDatabaseName();
        $this->shakhModel = $this->getmodel('Shakh');
        $this->familyPersonModel = $this->getmodel('FamilyPerson');

    }
    /**
     * GET shakh - all logged-in users can read the master list.
     * Returns only id + name.
     */
    public function index(Request $request)
    {
        $shakhs = $this->shakhModel::where('is_deleted', 0)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return $this->successresponse(200, 'data', $shakhs);
    }
    /**
     * GET shakh/{id} - single shakh record (id + name).
     */
    public function show($id)
    {
        $shakh = $this->shakhModel::where('id', $id)->where('is_deleted', 0)
            ->select('id', 'name')->first();

        if (!$shakh) {
            return $this->successresponse(404, 'message', 'No such shakh found');
        }

        return $this->successresponse(200, 'data', $shakh);
    }
    /**
     * POST shakh/add - admin only (family members add permission).
     */
    public function store(Request $request)
    {
        if ($this->rp['societymodule']['familymembers']['add'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return $this->successresponse(422, 'message', $validator->errors()->first());
        }
        $name = trim($request->name);
        if ($name === '') {
            return $this->successresponse(422, 'message', 'Shakh name is required');
        }
        if ($this->shakhModel::where('is_deleted', 0)->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])->exists()) {
            return $this->successresponse(422, 'message', 'Shakh name already exists');
        }
        $shakh = $this->shakhModel::create([
            'name' => $name,
            'created_by' => $this->userId,
        ]);
        return $this->successresponse(200, 'data', $shakh);
    }
    /**
     * PUT shakh/{id} - admin only. Renames the shakh and keeps the
     * family_person.shakh text column in sync.
     */
    public function update(Request $request, $id)
    {
        if ($this->rp['societymodule']['familymembers']['edit'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return $this->successresponse(422, 'message', $validator->errors()->first());
        }
        $name = trim($request->name);
        if ($name === '') {
            return $this->successresponse(422, 'message', 'Shakh name is required');
        }
        $shakh = $this->shakhModel::where('id', $id)->where('is_deleted', 0)->first();
        if (!$shakh) {
            return $this->successresponse(404, 'message', 'No such shakh found');
        }
        if ($this->shakhModel::where('is_deleted', 0)
            ->where('id', '!=', $shakh->id)
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
            ->exists()) {
            return $this->successresponse(422, 'message', 'Shakh name already exists');
        }

        DB::connection('dynamic_connection')->beginTransaction();
        try {
            $shakh->update([
                'name' => $name,
                'updated_by' => $this->userId,
            ]);
            // keep the denormalized text column on family_person in sync
            $this->familyPersonModel::where('shakh_id', $shakh->id)
                ->where('is_deleted', 0)
                ->update(['shakh' => $name]);
            DB::connection('dynamic_connection')->commit();
        } catch (\Throwable $e) {
            DB::connection('dynamic_connection')->rollBack();
            return $this->successresponse(500, 'message', 'Something went wrong while updating shakh');
        }
        return $this->successresponse(200, 'data', $shakh->fresh());
    }
    /**
     * DELETE shakh/{id} - admin only (family members delete permission).
     * Soft deletes the shakh and clears shakh_id on family persons while
     * keeping their stored shakh name text for manual mapping.
     */
    public function destroy($id)
    {
        if ($this->rp['societymodule']['familymembers']['delete'] != 1) {
            return $this->successresponse(500, 'message', 'You are Unauthorized');
        }
        $shakh = $this->shakhModel::where('id', $id)->where('is_deleted', 0)->first();
        if (!$shakh) {
            return $this->successresponse(404, 'message', 'No such shakh found');
        }
        DB::connection('dynamic_connection')->beginTransaction();
        try {
            $this->familyPersonModel::where('shakh_id', $shakh->id)
                ->where('is_deleted', 0)
                ->update(['shakh_id' => null]);
            $shakh->update([
                'is_deleted' => 1,
                'updated_by' => $this->userId,
            ]);
            DB::connection('dynamic_connection')->commit();
        } catch (\Throwable $e) {
            DB::connection('dynamic_connection')->rollBack();
            return $this->successresponse(500, 'message', 'Something went wrong while deleting shakh');
        }
        return $this->successresponse(200, 'message', 'Shakh deleted successfully');
    }
}
