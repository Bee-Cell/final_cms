<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Role;
use App\Photo;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UserEditRequest;
use Illuminate\Support\Facades\Session;

class AdminUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //  List of Blogs
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    //new
    public function create()
    {
        //  Show new blog form
        //returns list of items from the database id whould be secondd parameters.

        //roles to pass the roles from database
        $roles = Role::pluck("name", "id")->all();

        return view('admin.users.new', compact("roles"));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        //Create the New blog from the form and redirects somewhere
        // return $request->all(); // the see the post value from subbmiit

       $input = $request->all();

        //cehck file pull out name and append with time and move to tje image location
        if($file = $request->file("photo_id")){
            $name = time().$file->getClientOriginalName();
            $file->move("images", $name);
            $photo = Photo::create(["file_path" => $name ]);

            $input["photo_id"] = $photo->id;
        }

        if(trim($request->password) == ""){
            $input = $request->except("password");
        }
        else{
           $input["password"] = bcrypt($request->password); 
        }
        
        User::create($input);
        Session::flash("inserted_user" , "New User is succesfully created");

       return redirect(route("users.index"));

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //Show info about a single blog
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //show the form with particular blog with old value
        // return "hello from edit controller with user_id:".$id;

        $user = User::findOrFail($id);
        $roles = Role::pluck("name", "id")->all();
        return view('admin.users.edit', compact("user", "roles"));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserEditRequest $request, $id)
    {
        //Update form with new value; update the particularblog and then redirect somewhere
        $user = User::findOrFail($id);
        $input = $request->all(); //requested get in array form

        if($file = $request->file("photo_id")){
            unlink(public_path().$user->photo->file_path); //php function
            $name = time().$file->getClientOriginalName();
            $file->move("images", $name);

            //find the particular phtot and update
            $photo = Photo::findOrFail($user->photo_id);
            $photo->file_path = $name;
            $photo->save();
            $input["photo_id"] = $photo->id;
        }

        if( trim($request->password) == "" ){
           $input["password"] = $user->password;
        }
        else{
            $input["password"] = bcrypt($request->password); 
        }
        
        $user->update($input);
        Session::flash("updated_user" , "Existing User is succesfully Updated");
        return redirect(route("users.index"));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //delete the particular blog

        //for displaying we can use from the request->sesion >>> inject request or session() >>>global
        $user = User::findOrFail($id);
        unlink(public_path().$user->photo->file_path); //php function
        
        if(!empty($user->photo_id)){
            $photo = Photo::findOrFail($user->photo_id);
            $photo->delete();
        }

        $user->delete();
        //The Global Session Helper
        Session::flash("deleted_user" , "The Selected User is deleted");

        return redirect(route("users.index"));

    }
}
