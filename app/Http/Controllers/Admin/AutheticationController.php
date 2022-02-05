<?php

    namespace App\Http\Controllers;
    use Illuminate\Contracts\View\Factory;
    use Illuminate\Database\QueryException;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    use Illuminate\View\View;
    use Tymon\JWTAuth\Exceptions\JWTException;
    use Tymon\JWTAuth\Facades\JWTAuth;
    use Illuminate\Foundation\Auth\AuthenticatesUsers;

    class AutheticationController extends Controller
    {

        public function __construct() {
            //$this->middleware('auth');
        }

        public function index(Request $request)
        {
            if(auth()->check()):
                return redirect(route('dashboard'));
             else:
                return view('auth.login');
           endif;
        }

        public function login(Request $request)
        {
            $request->validate([
                'email' => 'required',
                'password' => 'required',
            ]);
            $user = User::whereEmail($request->email)->where("status",1)->where("user_type","SYSTEMS")->first();
            if(empty($user)):
                return response()->json([
                    'success'=>false,
                    'errors'=>["errors"=>["Sorry, Your are not authorised to access. Please contact support for any assistance."]]
                ],JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            endif;
            try {
                $credentials = $request->only('email', 'password');
               // if ($token = JWTAuth::attempt($credentials)) :
                if (auth('web')->attempt($credentials)):
                    return response()->json([
                        'success'=>true,
                         "message"=>"Success",
                        "intended"=>"dashboard",
                    ],JsonResponse::HTTP_OK);
                    else:
                        return response()->json([
                            'success' => false,
                            'errors'=>["errors"=>["Invalid email or password."]]
                        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
                endif;
            } catch (JWTException $e) {
                // something went wrong
                return response()->json([
                    'success' => false,
                    'error' => 'could not create token'
                ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        public function logout(Request $request)
        {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/');
        }
        //display
        public function roles(Request $request)
        {
            if(auth()->check()):
                $roles=Role::with(["users","permissions"])->get();

                $permissions=Permission::with("roles")->get();
                return view('auth.roles_permissions.roles',compact("roles",'permissions'));
                else:
                return redirect(route('login'));
            endif;
        }
        //display
        public function usersView(Request $request)
        {
            if(auth()->check()):

                $users=User::with("roles")->where("user_type","SYSTEMS")->get();
                return view('auth.users.users',compact("users"));
            else:
                return redirect(route('login'));
            endif;
        }
        public function userCreateView(Request $request)
        {
            if(auth()->check()):
                $roles=Role::with(["users","permissions"])->get();

                return view('auth.users.create',compact("roles"));
            else:
                return redirect(route('login'));
            endif;
        }
        public function userEditView(Request $request)
        {
            if(auth()->check()):
                $user=User::with("roles")->where("user_type","SYSTEMS")->find($request->route("id"));
                $roles=Role::with(["users","permissions"])->get();
                return view('auth.users.edit',compact("user",'roles'));
            else:
                return redirect(route('login'));
            endif;
        }
        public function updateUser(Request $request)
        {
            $request->validate([
               'id' => 'required'
            ]);
            try {
                $request->request->remove('_token');
                $user = User::where('id', $request->request->get('id'))->where("user_type","SYSTEMS")->first();

                if (empty($user)):
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            "user"=>[
                                "User not found"
                            ]
                        ]
                    ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
                endif;

                $user->roles()->sync($request->roles);
                $request->request->remove('roles');
                User::where('id', $request->id)->update($request->only([
                    'firstname',
                    'middlename',
                    'surname',
                    'phone',
                    "email",
                    'status'
                ]));

                return response()->json([
                    'success' => true,
                    'message' => 'User update successfully',
                ], JsonResponse::HTTP_OK);

            } catch (QueryException $e) {
                // something went wrong
                return response()->json([
                    'success' => false,
                    'errors' => [
                        "users"=>[
                            $e->getMessage()
                        ]]
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

        }
        public function createUser(Request $request)
        {
            $this->validate($request, [
                "firstname" => "required|min:2|max:20|regex:/^([a-zA-Z]+)(\s[a-zA-Z]+)*$/",
                // "middlename" => "required",
                "surname" => "required|min:2|max:20|regex:/^([a-zA-Z]+)(\s[a-zA-Z]+)*$/",
                "phone" => "required|unique:users|numeric",
                "email" => "required|unique:users|email",
                'password' => 'min:6|required_with:password_confirmation|same:password_confirmation',
                'password_confirmation' => 'min:6',
                'roles' => 'required'
            ]);
            try {
                $request->request->add(['status' => 1, 'otp' => 0,'user_type'=>"SYSTEMS",'referral_code'=>'_','doc_no'=>$request->phone,'sub_county_id'=>1,'village'=>"Kilimani"]);
                $user = User::create($request->all(['firstname', 'middlename', 'surname', 'phone', 'email', "user_type",'password','referral_code',"sub_county_id","village","doc_no", 'status', 'otp']));
                //create roles
//                foreach ($request->roles as $role):
//                   $user->roles()->attach($role);
//                endforeach;
                $user->roles()->sync($request->request->get('roles'));
                return response()->json([
                    'success' => true, 'message' => 'User created successfully'
                ], JsonResponse::HTTP_OK);
            } catch (QueryException $e) {
                // something went wrong
                return response()->json([
                    'success' => false,
                    'errors' => ["users"=>[$e->getMessage()]]
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

        }
        public function changePassword (Request $request)
        {
            $this->validate($request, [
                'password' => 'min:6|required_with:password_confirmation|same:password_confirmation',
                'password_confirmation' => 'min:6',
            ]);
            //get me

            $user = User::find($request->route("id"));
            $new = bcrypt($request->password);
            try {
                User::where('email',$user->email)->update(['password' => $new]);
                return response()->json([
                    'success' => true,
                    'message' => 'User update successfully',
                ], JsonResponse::HTTP_OK);

            } catch (QueryException $e) {
                // something went wrong
                return response()->json([
                    'success' => false,
                    'errors' =>["exception"=>[ $e->getMessage()]]
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
        }
        public function  deleteUser(Request $request)
        {
            $this->validate($request, [
                'id' => 'required'
            ]);
            try {
                User::find($request->id)->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'User update successfully',
                ], JsonResponse::HTTP_OK);

            } catch (QueryException $e) {
                // something went wrong
                return response()->json([
                    'success' => false,
                    'errors' =>["exception"=>[ $e->getMessage()]]
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
        }
        //display
        public function createRoleView(Request $request)
        {
            if(auth()->check()):
                $roles=Role::with(["users","permissions"])->get();
                $permissions=Permission::with("roles")->get();
                return view('auth.roles_permissions.create-roles',compact("roles",'permissions'));
            else:
                return redirect(route('login'));
            endif;
        }
        //create roles
        public function createRole(Request $request)
        {
            $this->validate($request, [
                "name" => "required|unique:roles|max:20|min:2|regex:/^([a-zA-Z]+)(\s[a-zA-Z]+)*$/",
                "display_name" => "required|unique:roles|max:20|min:2|regex:/^([a-zA-Z]+)(\s[a-zA-Z]+)*$/",
                "description"=>"required",
                "permissions"=>"required"
            ]);

            try {
                $role = new Role;
                $role->name = $request->request->get('name'); // name of the new role
                $role->display_name = $request->request->get('display_name');; // display name of the new role
                $role->description = $request->request->get('description');
                $role->save();
                //assign role permisssion
//                foreach ($request->permissions as $id):
//                     $permission=Permission::find($id);
//                    $role->attachPermission($permission);
//                endforeach;
                $role->permissions()->sync($request->request->get('permissions'));

                return response()->json([
                    'success' => true, 'message' => 'Role created successfully',
                ], JsonResponse::HTTP_OK);
            } catch (QueryException $e) {
                // something went wrong
                return response()->json([
                    'success' => false,
                    'errors' => ["roles"=>[$e->getMessage()]]
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
        }
        public function roleEditView(Request $request)
        {
            if(auth()->check()):
                $permissions=Permission::with("roles")->get();
                $role=Role::with(["users","permissions"])->find($request->route("id"));
                return view('auth.roles_permissions.edit-roles',compact('role',"permissions"));
            else:
                return redirect(route('login'));
            endif;
        }
        public function editRole(Request $request)
        {
            $this->validate($request, [
                "id" => "required|exists:roles",
            ]);

            try {
                Role::where('id',$request->id)->update($request->only([
                    "name",
                    "display_name",
                    "description"
                ]));
                $role=Role::find($request->id);
                $role->permissions()->sync($request->request->get('permissions'));

                return response()->json([
                    'success' => true, 'message' => 'Role updated successfully',
                ], JsonResponse::HTTP_OK);
            } catch (QueryException $e) {
                // something went wrong
                return response()->json([
                    'success' => false,
                    'errors' => ["roles"=>[$e->getMessage()]]
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
        }
        public function  deleteRole(Request $request)
        {
            $this->validate($request, [
                'id' => 'required'
            ]);
            try {
                Role::find($request->id)->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Role delete successfully',
                ], JsonResponse::HTTP_OK);

            } catch (QueryException $e) {
                // something went wrong
                return response()->json([
                    'success' => false,
                    'errors' =>["exception"=>[ $e->getMessage()]]
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
        }
        //create permissions
        //display
        public function createPermissionView(Request $request)
        {
            if(auth()->check()):
                $roles=Role::with(["users","permissions"])->get();
                $permissions=Permission::with("roles")->get();
                return view('auth.roles_permissions.create-permission',compact("roles",'permissions'));
            else:
                return redirect(route('login'));
            endif;
        }
        public function createPermission(Request $request)
        {
            //to create permission, NB: kindly do some protective checking before saving, visit the Entrust documentation
            //for more available options
            $this->validate($request, [
                "name" => "required|unique:permissions|max:20|min:2",
                "display_name" => "required|unique:permissions|max:20|min:2",
                "description"=>"required"
            ]);
            try {
                $viewUsers = new Permission;
                $viewUsers->name = $request->request->get('name'); // name of the new role
                $viewUsers->display_name = $request->request->get('display_name');; // display name of the new role
                $viewUsers->description = $request->request->get('description');
                $viewUsers->save();

                return response()->json([
                    'success' => true, 'message' => 'Role created successfully'
                ], JsonResponse::HTTP_OK);
            } catch (QueryException $e) {
                // something went wrong
                return response()->json([
                    'success' => false,
                    'errors' => ["permission"=>[$e->getMessage()]]
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

        }

    }