<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Incident;

class IncidentController extends Controller
{
    public function index() {
    	$incidents = Incident::orderBy('date', 'desc')->orderBy('created_at', 'desc')->get();
    	return view('incidents.index', compact('incidents'));
    }


    public function show(Incident $incident) {
    	return view('incidents.show', compact('incident'));
    }


    public function create() {
    	return view('incidents.create');
    }


    public function store(Request $request) {
        // validate the request input
        $rules = [
            'date' => 'required',
            'title' => 'required',
            'description' => 'required',
            'userId' => 'required|unique:incidents'
        ];
        $this->validate($request, $rules);


        // store it in a new instance of Incident
        $incident = new Incident;
        $incident->date = $request->date;
        $incident->title = $request->title;
        $incident->description = $request->description;
        $incident->user_id = $request->userId;
        $incident->patron_name = ($request->patronName ?: null);
        $incident->patron_description = ($request->patronDescription ?: null);
        

        // validate and upload the patron photo
        if ($request->hasFile('patronPicture') && $request->file('patronPicture')->isValid()) {
            // create a unique name for the file
            $filename = uniqid('img_') . '.' . $request->patronPicture->getClientOriginalExtension();
            // move the file to the assests directory
            if ($request->file('patronPicture')->move(public_path('images/patrons/'), $filename)) {
                // save the path to our instance of incident
                $incident->patron_photo = $filename;
            }
        }

        // save it to the database
        if($incident->save()) {
            return redirect("incidents/$incident->id");
        }
    }
}
