<?php

namespace App\Http\Controllers\Admin;

use App\Business;
use App\Helpers\UniqueRandomChar;
use App\Http\Controllers\Controller;
use App\Project;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class BusinessController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function index()
    {
        $businesses=Business::with("users")->get();
        $users=User::all();
        return view('admin.v1.businesses.contractor.index',compact("businesses",'users'));
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function showCreateContractorView()
    {
        $businesses=Business::with("users")->get();
        return view('admin.v1.businesses.contractor.create',compact("businesses"));
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function showEditContractorView(Request $request)
    {
        $businesses=Business::with("users")->get();
        $business=Business::find($request->route('id'));
        return view('admin.v1.businesses.contractor.edit',compact("business",'businesses'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return JsonResponse
     */
    public function addUsers(Request $request)
    {
        $this->validate($request, [
            "users"=>"required|array",
            "users.*"=>"required|min:1",
            "business_id"=>"required",
        ]);
        try{
            //get user
            $business=Business::find($request->business_id);
            //attach businesses id
            $business->users()->sync($request->users);
            return response()->json([
            'success' => true,
            'message' => 'User(s) added successfully.'
        ], JsonResponse::HTTP_OK);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'errors' => ["exception" => [$e->getMessage()]],
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $rules = [
//            "user_id" => "required|exists:users,id",
            "name" => "required",
            "phone" => "nullable",
            "country" => "nullable",
            "city" => "nullable",
            "address" => "nullable",
            "email" => "nullable",
        ];
        $custom_msg= ['name.required' => 'Please enter business/organisation name'];
        $this->validate($request, $rules,$custom_msg);

        try {
            $business_code=UniqueRandomChar::businessCode();
            $request->request->add(["business_code"=>$business_code]);
            $business = Business::updateOrCreate(["name"=>$request->name,"phone"=>$request->phone],$request->only([
                "business_code",
                "name",
                "email",
                "phone",
                "country",
                "city",
                "address",
                "status",
                "type",
                "description"
            ]));
            //attach business to merchant
            //$business->users()->sync($request->user_id);
            return response()->json([
                'success' => true,
                'message' => 'Business added successfully.'
            ], JsonResponse::HTTP_OK);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'errors' => ["exception" => [$e->getMessage()]],
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Business  $business
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            "id"=>"required|exists:businesses",
            "name"=>"required"
        ]);
        try {
        Business::where("id",$request->id)->update($request->only(
            "name",
            "email",
            "phone",
            "country",
            "city",
            "address",
            "status",
            "type",
            "description"
           ));
            return response()->json([
                'success' => true,
                'message' => 'Business added successfully.'
            ], JsonResponse::HTTP_OK);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'errors' => ["exception" => [$e->getMessage()]],
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Business  $business
     * @return \Illuminate\Http\Response
     */
    public function destroy(Business $business)
    {
        //
    }
}