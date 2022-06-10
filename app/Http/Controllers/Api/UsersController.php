<?php

namespace App\Http\Controllers\Api;

use App\Couple;
use App\Http\Requests\Users\UpdateRequest;
use App\Jobs\Users\DeleteAndReplaceUser;
use App\User;
use App\UserMetadata;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;
use Storage;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Search user by keyword.
     *
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        
        $q = $request->get('q');
        $users = [];

        if ($q) {
            $users = User::with('father', 'mother')->where(function ($query) use ($q) {
                $query->where('name', 'like', '%'.$q.'%');
                $query->orWhere('nickname', 'like', '%'.$q.'%');
            })
                ->orderBy('name', 'asc')
                ->paginate(24);
        }
        dd($users) ;
        return view('users.search', compact('users'));
    }

    /**
     * Display the specified User.
     *
     * @param  \App\User  $user
     * @return \Illuminate\View\View
     */
    public function show(User $user)
    {
        $usersMariageList = $this->getUserMariageList($user);
        $allMariageList = $this->getAllMariageList();
        $malePersonList = $this->getPersonList(1);
        $femalePersonList = $this->getPersonList(2);
        dd($user) ;
        return view('users.show', [
            'user'             => $user,
            'usersMariageList' => $usersMariageList,
            'malePersonList'   => $malePersonList,
            'femalePersonList' => $femalePersonList,
            'allMariageList'   => $allMariageList,
        ]);
    }

    /**
     * Display the user's family chart.
     *
     * @param  \App\User  $user
     * @return \Illuminate\View\View
     */
    public function chart(User $user)
    {
        $father = $user->father_id ? $user->father : null;
        $mother = $user->mother_id ? $user->mother : null;

        $fatherGrandpa = $father && $father->father_id ? $father->father : null;
        $fatherGrandma = $father && $father->mother_id ? $father->mother : null;

        $motherGrandpa = $mother && $mother->father_id ? $mother->father : null;
        $motherGrandma = $mother && $mother->mother_id ? $mother->mother : null;

        $childs = $user->childs;
        $colspan = $childs->count();
        $colspan = $colspan < 4 ? 4 : $colspan;

        $siblings = $user->siblings();
        dd($user) ;
        return view('users.chart', compact(
            'user', 'childs', 'father', 'mother', 'fatherGrandpa',
            'fatherGrandma', 'motherGrandpa', 'motherGrandma',
            'siblings', 'colspan'
        ));
    }

    /**
     * Show user family tree.
     *
     * @param  \App\User  $user
     * @return \Illuminate\View\View
     */
    public function absoluteTree(User $user)
    {
        dd("tree" . $user) ;
        return view('users.tree', compact('user'));
    }


    public $grand_children = [] ;

    function findChilds(User $user){
        $childs = $user->childs;
        foreach($childs as $child){
            array_push($this->grand_children,$child);
            $this->findChilds( $child ) ;
        }
    }

    
    /**
     * Show user absolute family tree. 
     *
     * @param  \App\User  $user
     * @return \Illuminate\View\View
     */
    public function tree(User $user)
    {
        $fathers = [] ;
        $mothers = [] ;

        $f = $user->father;
        $m = $user->mother;

        while( !empty($f) ){
            array_push($fathers,$f) ;
            $f = $f->father;
        }

        while( !empty($m) ){
            array_push($mothers,$m) ;
            $m = $m->mother;
        }

        $this->findChilds($user) ;

        $tree = [
            'user' => $user,
            'fathers' => $fathers,
            'mothers' => $mothers,
            'children' => $this->grand_children
        ];

        return $tree;
    }
    


    /**
     * Show user grand family tree. 
     *
     * @param  \App\User  $user
     * @return \Illuminate\View\View
     */
    public function grandtree(User $user)
    {
        $fathers = [] ;

        $f = $user->father;
        $grand_father = $user ;

        while( !empty($f) ){
            array_push($fathers,$f) ;
            $f = $f->father;
            if(!empty($f)){
                $grand_father = $f;
            }
        }

        $this->findChilds($grand_father) ;

        $tree = [
            'user' => $user,
            'grand_father' => $grand_father,
            'children' => $this->grand_children
        ];

        return $tree;
    }

    
    /**
     * Show user death info.
     *
     * @param  \App\User  $user
     * @return \Illuminate\View\View
     */
    public function death(User $user)
    {
        $mapZoomLevel = config('leaflet.detail_zoom_level');
        $mapCenterLatitude = $user->getMetadata('cemetery_location_latitude');
        $mapCenterLongitude = $user->getMetadata('cemetery_location_longitude');
        dd($user) ;
        return view('users.death', compact('user', 'mapZoomLevel', 'mapCenterLatitude', 'mapCenterLongitude'));
    }

    /**
     * Show the form for editing the specified User.
     *
     * @param  \App\User  $user
     * @return \Illuminate\View\View
     */
    public function edit(User $user)
    {
        $this->authorize('edit', $user);

        $replacementUsers = [];
        if (request('action') == 'delete') {
            $replacementUsers = $this->getPersonList($user->gender_id);
        }

        $validTabs = ['death', 'contact_address', 'login_account'];

        $mapZoomLevel = config('leaflet.zoom_level');
        $mapCenterLatitude = $user->getMetadata('cemetery_location_latitude');
        $mapCenterLongitude = $user->getMetadata('cemetery_location_longitude');
        if ($mapCenterLatitude && $mapCenterLongitude) {
            $mapZoomLevel = config('leaflet.detail_zoom_level');
        }
        $mapCenterLatitude = $mapCenterLatitude ?: config('leaflet.map_center_latitude');
        $mapCenterLongitude = $mapCenterLongitude ?: config('leaflet.map_center_longitude');

        return view('users.edit', compact(
            'user', 'replacementUsers', 'validTabs', 'mapZoomLevel', 'mapCenterLatitude', 'mapCenterLongitude'
        ));
    }

    /**
     * Update the specified User in storage.
     *
     * @param  \App\Http\Requests\Users\UpdateRequest  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRequest $request, User $user)
    {
        //dd($request) ;
        $userAttributes = $request->validated();
        $user->update($userAttributes);
        $userAttributes = collect($userAttributes);

        $this->updateUserMetadata($user, $userAttributes);

        return response()->json([
            'success' => true,
            'message' => "user has been updated successfuly",
            'user' => $user
          ]);

        return redirect()->route('users.show', $user->id);
    }

    /**
     * Remove the specified User from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, User $user)
    {
        // dd($request) ;

        $this->authorize('delete', $user);

        if ($request->has('replace_delete_button')) {
            $attributes = $request->validate([
                'replacement_user_id' => 'required|exists:users,id',
            ], [
                'replacement_user_id.required' => __('validation.user.replacement_user_id.required'),
            ]);

            $this->dispatchNow(new DeleteAndReplaceUser($user, $attributes['replacement_user_id']));

            return response()->json([
                'success' => true,
                'message' => "user has been deleted successfuly"
              ]);

            return redirect()->route('users.show', $attributes['replacement_user_id']);
        }

        $request->validate([
            'user_id' => 'required',
        ]);

        if(!empty($request->get('selectedId'))){
            $user = User::find($request->get('selectedId'));
        }

        if ($request->get('user_id') == $user->id && $user->delete()) {
            return 200 ;
            return redirect()->route('users.search');
        }

        return 500 ;
        return back();
    }

    /**
     * Upload users photo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function photoUpload(Request $request, User $user)
    {
        // dd($request->file('photo'));
        // $request->validate([
        //     'photo' => 'required|image|max:200',
        // ]);
        // dd($user->photo_path) ;
        if (Storage::exists($user->photo_path)) {
            Storage::delete($user->photo_path);
        }

        $user->photo_path = $request->photo->store('images');
        $user->save();

        return response()->json([
            'success' => true,
            'message' => "user image has been updated successfuly",
            'user' => $user
          ]);

        return back();
    }

    /**
     * Get User list based on gender.
     *
     * @param int $genderId
     *
     * @return \Illuminate\Support\Collection
     */
    private function getPersonList(int $genderId)
    {
        return User::where('gender_id', $genderId)->pluck('nickname', 'id');
    }

    /**
     * Get marriage list of a user.
     *
     * @param \App\User $user
     *
     * @return array
     */
    private function getUserMariageList(User $user)
    {
        $usersMariageList = [];

        foreach ($user->couples as $spouse) {
            $usersMariageList[$spouse->pivot->id] = $user->name.' & '.$spouse->name;
        }

        return $usersMariageList;
    }

    /**
     * Get all marriage list.
     *
     * @return array
     */
    private function getAllMariageList()
    {
        $allMariageList = [];

        foreach (Couple::with('husband', 'wife')->get() as $couple) {
            $allMariageList[$couple->id] = $couple->husband->name.' & '.$couple->wife->name;
        }

        return $allMariageList;
    }

    private function updateUserMetadata(User $user, Collection $userAttributes)
    {
        foreach (User::METADATA_KEYS as $key) {
            if ($userAttributes->has($key) == false) {
                continue;
            }
            $userMeta = UserMetadata::firstOrNew(['user_id' => $user->id, 'key' => $key]);
            if (!$userMeta->exists) {
                $userMeta->id = Uuid::uuid4()->toString();
            }
            $userMeta->value = $userAttributes->get($key);
            $userMeta->save();
        }
    }
}
