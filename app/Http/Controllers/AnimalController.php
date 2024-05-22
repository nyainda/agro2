<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Animal;
use App\Models\Contact;
use App\Models\AnimalContent;
use App\Models\Treat;
use App\Models\Feed;
use App\Models\Health;
use App\Models\Task;
use App\Models\Breeding;
use App\Models\Measurement;
use App\Models\YieldRecord;
use App\Models\Note;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
class AnimalController extends Controller
{
    public function index(Request $request)
{
    // Get the current user and any error message from the session
    $user = auth()->user();
    $error = session('error');

    // Fetch all animals that belong to the user and sort them by creation date
    $animals = Animal::where('user_id', $user->id)
        ->latest()
        ->paginate(5);

    // Initialize some variables to store the counts of different animal attributes
    $recordCount = $animals->total();
    $maleCount = 0;
    $femaleCount = 0;
    $purchased = 0;
    $raised = 0;
    $soldAnimal = 0;
    $goodHealthCount = 0; // Assuming 'good' is one of the health status values
    $totalAnimalsFeeding = 0; // Initializing totalAnimalsFeeding
    $totalAnimals = 0; // Initializing totalAnimals
    $notVaccinatedCount = 0;
    $partiallyVaccinatedCount = 0;
    $fullyVaccinatedCount = 0;


    // If the user has any animals, loop through them and update the counts accordingly
    if ($animals->isNotEmpty()) {
        foreach ($animals as $animal) {
            if ($animal->gender == 'Male') {
                $maleCount++;
            } elseif ($animal->gender == 'Female') {
                $femaleCount++;
            }

            if ($animal->raised_purchased == 'Raised') {
                $raised++;
            } elseif ($animal->raised_purchased == 'Purchased') {
                $purchased++;
            }

            if ($animal->status == 'sold') {
                $soldAnimal++;
            }

            // Check for 'good' health status
            if ($animal->health_status == 'good') {
                $goodHealthCount++;
            }

            // Count the total animals feeding
            $totalAnimalsFeeding += Feed::where('user_id', $user->id)
                ->where('animal_id', $animal->id)
                ->distinct('animal_id')
                ->count('animal_id');
// ...

$hasVaccination = Health::where('animal_id', $animal->id)
    ->whereIn('vaccination_status', ['partially_vaccinated', 'fully_vaccinated'])
    ->exists();

// Increment counts based on vaccination status
// Check if there's any vaccination record for the animal
if (!$hasVaccination) {
    $notVaccinatedCount++;
} else {
    // Fetch the latest vaccination record
    $latestVaccination = Health::where('animal_id', $animal->id)
        ->whereIn('vaccination_status', ['partially_vaccinated', 'fully_vaccinated'])
        ->orderByDesc('created_at')
        ->first();

    // Check if a vaccination record was found
    if ($latestVaccination) {
        // Categorize based on vaccination status
        switch ($latestVaccination->vaccination_status) {
            case 'partially_vaccinated':
                $partiallyVaccinatedCount++;
                break;
            case 'fully_vaccinated':
                $fullyVaccinatedCount++;
                break;
            default:
                $notVaccinatedCount++;
                break;
        }
    } else {
        // If there's no vaccination record found, increment notVaccinatedCount
        $notVaccinatedCount++;
    }
}

// ...

        }

        // Calculate the overall totalAnimals
        $totalAnimals = $femaleCount + $maleCount;
    }

    // Determine the overall health status
    $overallHealthStatus = 'Good'; // You can implement more sophisticated logic based on your data

    // Return the view with the variables
    return view('AnimalContent.index', compact('animals', 'error', 'recordCount', 'femaleCount', 'maleCount', 'user', 'raised', 'purchased', 'soldAnimal', 'totalAnimalsFeeding', 'totalAnimals', 'goodHealthCount', 'overallHealthStatus','notVaccinatedCount','partiallyVaccinatedCount', 'fullyVaccinatedCount'));
}




    public function create()
    {
        return view('AnimalContent.create');
    }

    public function show($id)
    {
        // Get the current user and the animal with the given id
        $user = auth()->user();
        $animal = Animal::find($id);

        // Check if the animal exists and belongs to the user
        if ($animal && $animal->user_id == $user->id) {
            // Get the treatment data for the animal
            //$treatment = Treat::where('animal_id', $id)->first();

            // Return the view with the data
            return view('AnimalContent.show', compact('animal',  'user'));
        } else {
            // Abort with an error message
            abort(403, 'Unauthorized or invalid animal ID provided.');
        }
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string',
            'type' => 'required|string',
            'breed' => 'nullable|string',
            'gender' => 'nullable|string',
            'keywords' => 'nullable|string',
            'internal_id' => 'nullable|string',
            'status' => 'required|string',
            'death_date' => 'nullable|date',
            'deceased_reason' => 'nullable|string',
            'is_neutered' => 'nullable|boolean',
            'is_breeding_stock' => 'nullable|boolean',
            'coloring' => 'nullable|string',
            'retention_score' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'body_condition_score' => 'nullable|numeric',
            'horn_length' => 'nullable|numeric',
            'tail_length_shape' => 'nullable|string',
            'fur_feather_scale_type' => 'nullable|string',
            'eye_color' => 'nullable|string',
            'beak_shape' => 'nullable|string',
            'tail_feather_pattern' => 'nullable|string',
            'saddle_markings' => 'nullable|string',
            'hoof_type' => 'nullable|string',
            'gait_or_movement' => 'nullable|string',
            'teeth_characteristics' => 'nullable|string',
            //
            'description' => 'nullable|string',

            'tag_number' => 'nullable|string',
            'color' => 'nullable|string',
            'location' => 'nullable|string',
            'electronic_id' => 'nullable|string',
            'other_tag' => 'nullable|string',
            'other_color' => 'nullable|string',
            'other_location' => 'nullable|string',
            'registry_number' => 'nullable|string',
            'tattoo_left' => 'nullable|string',
            'tattoo_right' => 'nullable|string',
            'photographs' => 'nullable|array',
            'photographs.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'dna_profile' => 'nullable|string',
            'scars' => 'nullable|string',

            'birth_date' => 'nullable|date',
            'dam' => 'nullable|string',
            'sire' => 'nullable|string',
            'birth_weight' => 'nullable|numeric',
            'weight_unit' => 'nullable|in:lbs,kg',
            'age_to_wean' => 'nullable|numeric',
            'date_weaned' => 'nullable|date',
            'birth_time' => 'nullable|date_format:H:i',
            'birth_status' => 'nullable|in:Natural,Assisted',
            'colostrum_intake' => 'nullable|numeric',
            'health_at_birth' => 'required|in:Healthy,Sick',
            'milk_feeding' => 'nullable|string',
            'vaccinations' => 'nullable|string',
            'breeder_info' => 'nullable|string',
           // 'raised_purchased' => 'required|in:Raised,Purchased',
           // 'birth_photos' => 'nullable|array',
            'birth_photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',





        ]);
        //$animal->raised_purchased = $request->input('raised_purchased');


        if ($validator->fails()) {
            return redirect()
                ->route('AnimalContent.create')
                ->withErrors($validator,'requiredFields')
                ->withInput();
        }

        $animal = new Animal();
       // $animal->name = $request->input('name', 'Default Name');
        if ($request->input('raised_purchased') === 'Purchased') {
            $animal->purchasedAnimal = $request->input('purchasedAnimal');
            $animal->purchaseDate = $request->input('purchaseDate');
            $animal->purchasePrice = $request->input('purchasePrice');
            $animal->vendor = $request->input('vendor');
            $animal->deficts = $request->input('deficts');
            $animal->treatments = $request->input('treatments');
            //$animal->healthStatus = $request->input('healthStatus');
        }
        // Fill the model with validated data
        $animal->fill($request->all());
        $animal->user_id = auth()->user()->id;
        // Save the animal to the database
        $animal->save();
        if ($request->hasFile('birth_photos')) {
            foreach ($request->file('birth_photos') as $photo) {
                $filename = $photo->getClientOriginalName();
                $path = $photo->storeAs('public/animal_images', $filename);
                $animal->photographs()->create(['path' => $path]);
            }
        }
        $action = $request->input('action');

        if ($action === 'create') {
            return redirect()->route('AnimalContent.show', ['id' => $animal->id])->with('success', 'Animal created successfully.');
        } elseif ($action === 'new_save') {
            return redirect()->route('index')->with('success', 'Animal saved successfully.');
        }
    }

/**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Animal  $animal
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Retrieve the animal with the given ID from the database
        $animal = Animal::where('id', $id)->where('user_id', Auth::id())->first();


        // If the animal does not exist or belongs to another user, abort with 403 Forbidden
        if (!$animal) {
            return abort(403);
        }

        // If the animal's status is 'sold', redirect with an error message
        if ($animal->status === 'sold') {
            return redirect()->route('index')->with('error', 'This animal has already been sold and cannot be edited.');
        }

        // Otherwise, return the view for editing the animal
        return view('AnimalContent.edit', compact('animal'));
    }



/**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Animal  $animal
     * @return \Illuminate\Http\Response
     */

     public function update(Request $request, $id)
     {
         // Validate the form data
         $validatedData = $request->validate([
            //basic infomatiom of animal
            'name' => 'nullable|string',
            'breed' => 'nullable|string',
            'keywords' => 'nullable|string',
            'internal_id' => 'nullable|string',
            'status' => 'nullable|string',
            'death_date' => 'nullable|date',
            'deceased_reason' => 'nullable|string',

             // Validation for physical characteristics
             'is_neutered' => 'boolean',
             'is_breeding_stock' => 'boolean',
             'coloring' => 'nullable|string',
             'retention_score' => 'nullable|numeric',
             'weight' => 'nullable|numeric',
             'height' => 'nullable|numeric',
             'body_condition_score' => 'nullable|numeric',
             'horn_length' => 'nullable|numeric',
             'tail_length_shape' => 'nullable|string',
             'fur_feather_scale_type' => 'nullable|string',
             'eye_color' => 'nullable|string',
             'beak_shape' => 'nullable|string',
             'tail_feather_pattern' => 'nullable|string',
             'saddle_markings' => 'nullable|string',
             'hoof_type' => 'nullable|string',
             'gait_or_movement' => 'nullable|string',
             'teeth_characteristics' => 'nullable|string',
             'description' => 'nullable|string',
             'tag_number' => 'nullable|string',
             'color' => 'nullable|string',
             'location' => 'nullable|string',
             'electronic_id' => 'nullable|string',
             'other_tag' => 'nullable|string',
             'other_color' => 'nullable|string',
             'other_location' => 'nullable|string',
             'registry_number' => 'nullable|string',
             'tattoo_left' => 'nullable|string',
             'tattoo_right' => 'nullable|string',
             'photographs' => 'nullable|array',
             'photographs.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
             'dna_profile' => 'nullable|string',
             'scars' => 'nullable|string',
             'birth_photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',


         ]);

         $animal = Animal::find($id);


         $animal->fill($validatedData);


         $animal->save();
         if ($request->hasFile('birth_photos')) {
            foreach ($request->file('birth_photos') as $birthPhoto) {
                $filename = $birthPhoto->getClientOriginalName();
                $birthPath = $birthPhoto->storeAs('public/animal_birth_images', $filename);
                // Assuming you have a relationship for birth photos, adjust accordingly
                $animal->birthPhotos()->create(['path' => $birthPath]);
            }
        }
         // Redirect back with a success message
         return redirect()->route('index')->with('success', 'Animal information updated successfully.');
     }

     public function delete($id)
    {

        $animal = Animal::find($id);
        $animal -> delete();

        return redirect()->route('index');
    }


// show All treatments

public function showAllTreatments()
{
    try {
        $currentUser = auth()->user();

        // Retrieve user animals with pagination
        $animals = $this->getUserAnimals();

        // Retrieve user treatments
        $treatments = $this->getUserTreatments();

        // Group treatments by animal ID
        $animalTreatments = $this->groupTreatmentsByAnimalId($treatments);

        // Pass the data to the view
        return view('AnimalContent.showalltreatments', compact('animals', 'animalTreatments'));
    } catch (\Illuminate\Database\QueryException $e) {
        \Log::error('Database error: ' . $e->getMessage());
        return redirect()->route('index')->with('error', 'Sorry, we encountered a problem while retrieving the data. Please try again later.');
    }
}

private function getUserAnimals()
{
    return Animal::where('user_id', auth()->user()->id)
        ->orderBy('created_at', 'desc')
        ->paginate(5);
}

private function getUserTreatments()
{
    return Treat::where('user_id', auth()->user()->id)->get();
}

private function groupTreatmentsByAnimalId($treatments)
{
    $animalTreatments = [];

    foreach ($treatments as $treatment) {
        $animalId = $treatment->animal_id;

        if (!isset($animalTreatments[$animalId])) {
            $animalTreatments[$animalId] = [];
        }

        $animalTreatments[$animalId][] = $treatment;
    }

    return $animalTreatments;
}


// show All treatments

public function showAllFeedings()
{
    try {
        // Get the currently authenticated user
        $user = auth()->user();

        // Retrieve all feedings for the user
        $feedings = Feed::where('user_id', $user->id)->get();

        // Retrieve animals for the user, ordered by creation date in descending order and paginated
        $animals = Animal::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        // Create an associative array to group feedings by animal ID
        $animalFeedings = [];
        foreach ($feedings as $feeding) {
            $animalId = $feeding->animal_id;
            if (!isset($animalFeedings[$animalId])) {
                $animalFeedings[$animalId] = [];
            }
            $animalFeedings[$animalId][] = $feeding;
        }

        // Pass the data to the view
        return view('AnimalContent.showallfeedings', compact('animals', 'animalFeedings'));

    } catch (\Exception $e) {
        // Handle the error here and return an error view
        return redirect()->route('index')->with('error', 'Oops! Something went wrong. Please try again.');
    }
}


public function showAllMeasurements()
{
    try {
        // Get the authenticated user
        $user = auth()->user();

        // Retrieve all measurements for the user
        $measurements = Measurement::where('user_id', $user->id)->get();

        // Retrieve animals for the user, ordered by creation date in descending order, paginated
        $animals = Animal::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        // Group measurements by animal ID
        $animalMeasurements = [];
        foreach ($measurements as $measurement) {
            $animalId = $measurement->animal_id;
            $animalMeasurements[$animalId][] = $measurement;
        }

        // Pass data to the view
        return view('AnimalContent.showallmeasurements', compact('animals', 'animalMeasurements'));

    } catch (\Exception $e) {
        // Handle the error and redirect to the index page with an error message
        return redirect()->route('index')->with('error', 'Oops! Something went wrong. Please try again.');
    }
}



public function showAllYieldrecords()
{
    try {
        $user = auth()->user();

        // Retrieve all yield records for the user
        $yieldrecords = YieldRecord::where('user_id', $user->id)->get();

        // Retrieve animals for the user, ordered by creation date in descending order, paginated
        $animals = Animal::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        // Group yield records by animal ID
        $animalyieldrecords = [];
        foreach ($yieldrecords as $yieldrecord) {
            $animalId = $yieldrecord->animal_id;
            if (!isset($animalyieldrecords[$animalId])) {
                $animalyieldrecords[$animalId] = [];
            }
            $animalyieldrecords[$animalId][] = $yieldrecord;
        }

        // Pass data to the view
        return view('AnimalContent.showallyieldrecords', compact('animals', 'animalyieldrecords', 'user', 'yieldrecords'));

    } catch (\Exception $e) {
        // Handle the error and redirect to the index page with an error message
        return redirect()->route('index')->with('error', 'Oops! Something went wrong. Please try again.');
    }
}

public function showAllnotes()
{
    try {
        $user = auth()->user();

        // Retrieve all notes for the user
        $notes = Note::where('user_id', $user->id)->get();

        // Retrieve animals for the user, ordered by creation date in descending order, paginated
        $animals = Animal::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        // Group notes by animal ID
        $animalnotes = [];
        foreach ($notes as $note) {
            $animalId = $note->animal_id;
            if (!isset($animalnotes[$animalId])) {
                $animalnotes[$animalId] = [];
            }
            $animalnotes[$animalId][] = $note;
        }

        // Pass data to the view
        return view('AnimalContent.showallnotes', compact('animals', 'animalnotes', 'user', 'notes'));

    } catch (\Exception $e) {
        // Log the error
        \Log::error($e);
        // Handle the error and redirect to the index page with an error message
        return redirect()->route('index')->with('error', 'Oops! Something went wrong. Please try again.');
    }
}

public function showAlltasks()
{
    try {
        $user = auth()->user();

        // Retrieve all tasks for the user
        $tasks = Task::where('user_id', $user->id)->get();

        // Retrieve animals for the user, ordered by creation date in descending order, paginated
        $animals = Animal::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        // Group tasks by animal ID
        $animaltasks = [];
        foreach ($tasks as $task) {
            $animalId = $task->animal_id;
            if (!isset($animaltasks[$animalId])) {
                $animaltasks[$animalId] = [];
            }
            $animaltasks[$animalId][] = $task;
        }

        // Pass data to the view
        return view('Task.showalltasks', compact('animals', 'animaltasks', 'user', 'tasks'));

    } catch (\Exception $e) {
        // Log the error
        \Log::error($e);
        // Handle the error and redirect to the index page with an error message
        return redirect()->route('index')->with('error', 'Oops! Something went wrong. Please try again.');
    }
}

public function showAllcontact()
{
    try {
        $user = auth()->user();

        // Retrieve all contacts for the user
        $contacts = Contact::where('user_id', $user->id)->get();

        // Retrieve animals for the user, ordered by creation date in descending order, paginated
        $animals = Animal::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        // Group contacts by animal ID
        $animalcontacts = [];
        foreach ($contacts as $contact) {
            $animalId = $contact->animal_id;
            if (!isset($animalcontacts[$animalId])) {
                $animalcontacts[$animalId] = [];
            }
            $animalcontacts[$animalId][] = $contact;
        }

        // Pass data to the view
        return view('Task.showallcontact', compact('animals', 'animalcontacts', 'user', 'contacts'));

    } catch (\Exception $e) {
        // Log the error
        \Log::error($e);
        // Handle the error and redirect to the index page with an error message
        return redirect()->route('index')->with('error', 'Oops! Something went wrong. Please try again.');
    }
}

public function showAllHealths()
{
    try {
        // Get the authenticated user
        $user = auth()->user();

        // Retrieve animals for the user, ordered by creation date in descending order, paginated
        $animals = Animal::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        // Pass data to the view if no animals are found
        if ($animals->isEmpty()) {
            return view('AnimalContent.showallhealths', compact('animals'))->with('error', 'No animals found.');
        }

        // Retrieve health records for the user's animals
        $healths = Health::whereIn('animal_id', $animals->pluck('id'))->get();

        // Group health records by animal ID
        $animalHealths = [];
        foreach ($healths as $health) {
            $animalId = $health->animal_id;
            $animalHealths[$animalId][] = $health;
        }

        // Pass data to the view
        return view('AnimalContent.showallhealths', compact('animals', 'animalHealths'));

    } catch (\Exception $e) {
        // Handle the error and redirect to the index page with an error message
        return redirect()->route('index')->with('error', 'Oops! Something went wrong. Please try again.');
    }
}

public function showVaccinatedAnimals($status)
{
    // Retrieve animals based on vaccination status along with their health records
    $animals = Animal::whereHas('Health', function ($query) use ($status) {
        $query->where('vaccination_status', $status);
    })->with('Health')->get();

    return view('animals.show', compact('animals', 'status'));
}


}
