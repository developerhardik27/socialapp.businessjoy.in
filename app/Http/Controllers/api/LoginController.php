<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use App\Models\company;
use App\Models\company_detail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    /**
     * Handle user login API request
     */
    public function login(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find user by email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'Email not registered'
            ], 404);
        }

        // Check if user is deleted
        if ($user->is_deleted == 1) {
            return response()->json([
                'status' => 403,
                'message' => 'Account has been deleted'
            ], 403);
        }

        // Check if user is active
        if ($user->is_active != 1) {
            return response()->json([
                'status' => 403,
                'message' => 'Account is inactive. Please contact support.'
            ], 403);
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 401,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Check user role
        if (!in_array($user->role, [1, 2, 3])) {
            return response()->json([
                'status' => 403,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Generate unique API token
        do {
            $api_token = Str::random(60);
        } while (User::where('api_token', $api_token)->exists());

        // Update user with new token
        $user->update([
            'api_token' => $api_token,
            'user_login' => 1
        ]);

        // Get company details
        $company = company::find($user->company_id);
        if (!$company) {
            return response()->json([
                'status' => 404,
                'message' => 'Company not found'
            ], 404);
        }

        // Switch to company database
        config(['database.connections.dynamic_connection.database' => $company->dbname]);
        DB::purge('dynamic_connection');
        DB::reconnect('dynamic_connection');

        // Get user permissions from dynamic database
        $permissions = [];
        try {
            $rpData = DB::connection('dynamic_connection')
                ->table('user_permissions')
                ->select('rp')
                ->where('user_id', $user->id)
                ->first();

            $permissions = $rpData ? json_decode($rpData->rp, true) : [];
        } catch (\Exception $e) {
            // Log error but continue
            \Log::error('Failed to fetch user permissions: ' . $e->getMessage());
        }

        // Get company details
        $company_details = null;
        if ($company->company_details_id) {
            $company_details = company_detail::find($company->company_details_id);
        }

        // Record login activity
        $this->recordLoginActivity($user, $request, 'api', 'success');

        return response()->json([
            'status' => 200,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->firstname . ' ' . $user->lastname,
                    'email' => $user->email,
                    'role' => $user->role,
                    'company_id' => $user->company_id,
                    'country_id' => $user->country_id,
                    'state_id' => $user->state_id,
                    'city_id' => $user->city_id,
                    'default_module' => $user->default_module,
                    'default_page' => $user->default_page,
                ],
                'company' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'app_version' => $company->app_version,
                    'dbname' => $company->dbname,
                ],
                'company_details' => $company_details ? [
                    'gst_no' => $company_details->gst_no,
                    'country_id' => $company_details->country_id,
                    'state_id' => $company_details->state_id,
                    'city_id' => $company_details->city_id,
                ] : null,
                'api_token' => $api_token,
                'permissions' => $permissions,
                'token_type' => 'Bearer',
                'expires_in' => config('sanctum.expiration', 525600) // minutes (1 year default)
            ]
        ]);
    }

    /**
     * Handle user logout API request
     */
    public function logout(Request $request)
    {
        $token = $request->bearerToken() ?? $request->input('api_token');

        if (!$token) {
            return response()->json([
                'status' => 401,
                'message' => 'Authorization token not provided'
            ], 200);
        }

        // Find user by token (check both api_token and super_api_token)
        $user = User::where('api_token', $token)
            ->orWhere('super_api_token', $token)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'Invalid or expired token'
            ], 200);
        }

        // Determine which token to revoke
        $updateField = $user->super_api_token === $token ? 'super_api_token' : 'api_token';

        // Update user login status and clear token
        User::where('id', $user->id)->update([
            'user_login' => 0,
            $updateField => null
        ]);

        // Record logout activity
        $this->recordLoginActivity($user, $request, 'api', 'logout');

        return response()->json([
            'status' => 200,
            'message' => 'Logged out successfully'
        ], 200);
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request)
    {
        $token = $request->bearerToken() ?? $request->input('api_token');

        if (!$token) {
            return response()->json([
                'status' => 401,
                'message' => 'Authorization token not provided'
            ], 200);
        }

        // Find user by token (check both api_token and super_api_token)
        $user = User::where('api_token', $token)
            ->orWhere('super_api_token', $token)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'Invalid or expired token'
            ], 200);
        }

        // Get company details
        $company = company::find($user->company_id);
        $company_details = null;
        
        if ($company && $company->company_details_id) {
            $company_details = company_detail::find($company->company_details_id);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Profile retrieved successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->firstname . ' ' . $user->lastname,
                    'email' => $user->email,
                    'role' => $user->role,
                    'company_id' => $user->company_id,
                    'country_id' => $user->country_id,
                    'state_id' => $user->state_id,
                    'city_id' => $user->city_id,
                    'default_module' => $user->default_module,
                    'default_page' => $user->default_page,
                    'is_active' => $user->is_active,
                    'created_at' => $user->created_at,
                ],
                'company' => $company ? [
                    'id' => $company->id,
                    'name' => $company->name,
                    'app_version' => $company->app_version,
                ] : null,
                'company_details' => $company_details ? [
                    'gst_no' => $company_details->gst_no,
                    'country_id' => $company_details->country_id,
                    'state_id' => $company_details->state_id,
                    'city_id' => $company_details->city_id,
                ] : null,
            ]
        ]);
    }

    /**
     * Refresh API token
     */
    public function refreshToken(Request $request)
    {
        $token = $request->bearerToken() ?? $request->input('api_token');

        if (!$token) {
            return response()->json([
                'status' => 401,
                'message' => 'Authorization token not provided'
            ], 200);
        }

        // Find user by token (check both api_token and super_api_token)
        $user = User::where('api_token', $token)
            ->orWhere('super_api_token', $token)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'Invalid or expired token'
            ], 200);
        }

        // Generate new API token
        do {
            $new_token = Str::random(60);
        } while (User::where('api_token', $new_token)->exists());

        // Determine which token field to update
        $updateField = $user->super_api_token === $token ? 'super_api_token' : 'api_token';

        // Update user token
        $user->update([$updateField => $new_token]);

        return response()->json([
            'status' => 200,
            'message' => 'Token refreshed successfully',
            'data' => [
                'api_token' => $new_token,
                'token_type' => 'Bearer',
                'expires_in' => config('sanctum.expiration', 525600)
            ]
        ]);
    }

    /**
     * Record login/logout activity
     */
    private function recordLoginActivity($user, $request, $via, $status)
    {
        try {
            $ip = $request->header('X-Forwarded-For') ?? $request->server('REMOTE_ADDR');
            
            // Get device and browser info
            $agent = new \Jenssegers\Agent\Agent();
            $device = $agent->isDesktop() ? 'Desktop' : ($agent->isMobile() ? 'Mobile' : 'Tablet');
            $browser = $agent->browser();

            // Get country from IP
            $country = 'Unknown';
            try {
                $client = new \GuzzleHttp\Client();
                $response = $client->get("http://ip-api.com/json/{$ip}");
                $data = json_decode($response->getBody(), true);
                $country = $data['status'] === 'fail' ? 'Unknown' : $data['country'];
            } catch (\Exception $e) {
                // Continue with Unknown country
            }

            \App\Models\user_activity::create([
                'user_id' => $user->id,
                'username' => $user->email,
                'ip' => $ip,
                'country' => $country,
                'device' => $device,
                'browser' => $browser,
                'status' => $status,
                'via' => $via,
                'company_id' => $user->company_id,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to record login activity: ' . $e->getMessage());
        }
    }
}
