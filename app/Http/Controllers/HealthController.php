<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\HealthRequest;
use App\Models\Health;
use App\Models\Contact;
use App\Models\Animal;
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
use App\Exceptions\AnimalNotFoundException;
use Illuminate\Validation\ValidationException;

class HealthController extends Controller
{
    public function storehealth(HealthRequest $request, $animal_id)
{
   // try {

    $validator = Validator::make($request->all(), [

    ]);


        $health = new Health();

        // Generate a UUID for the id field
        $health->id = Str::uuid();

        // Fill in the other health fields
        $health->fill($request->all());
        $health->animal_id = $animal_id;
        $health->user_id = auth()->user()->id;


        $health->save();

        return redirect()->route('Health.showhealth', ['animal_id' => $animal_id])
            ->with('success', 'health created successfully.');
   // } catch (ValidationException $e) {
        // If validation fails, redirect back with errors
        //return redirect()->back()
           // ->withErrors($e->errors(), 'requiredFields')
           // ->withInput();
   //}
}



    public function showhealth($animal_id)
    {
        // Fetch the authenticated user
        $user = auth()->user();

        // Fetch the animal and check if it exists
        $animal = Animal::find($animal_id);
        if (!$animal) {
            // Throw a custom exception if the animal is not found
            throw new AnimalNotFoundException($animal_id);
        }

        // Check if the animal's status is 'sold'
        if ($animal->status === 'sold') {
            return redirect()->route('index')->with('error', 'This animal has already been sold, and you cannot add healths.');
        }

        // Fetch healths related to the specified animal, ordered by created_at in descending order and paginate them
        $healths = Health::where('animal_id', $animal_id)
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        // Group healths by category in the controller
        $groupedhealths = $healths->groupBy('category');

        return view('Health.showhealth', compact('animal', 'user', 'healths', 'groupedhealths'));
    }

    public function health($animal_id)
    {
        // Retrieve the animal by ID
        $animal = Animal::find($animal_id);
        if (!$animal) {
            // Throw a custom exception if the animal is not found
            throw new AnimalNotFoundException($animal_id);
        }

        $animal->user_id = auth()->user()->id;

        $Contacts = Contact::where('contact_type', 'Veterinarian')->get();


        return view('Health.health', ['animal' => $animal,'animal_id' => $animal_id,'Contacts' => $Contacts]);
    }

    public function edithealth($animal_id, $health_id)
    {
        $animal = Animal::find($animal_id);
        if (!$animal) {
            // Throw a custom exception if the animal is not found
            throw new AnimalNotFoundException($animal_id);
        }

        // Find the health by ID or throw a ModelNotFoundException
        $health = Health::findOrFail($health_id);

        return view('Health.healthedit', ['animal' => $animal, 'health' => $health, 'animal_id' => $animal_id]);
    }

    // take care of the updating the animal treatment
    public function updatehealth(Request $request, $animal_id, $health_id)
    {
        // Validation rules for treatment update fields
        $validator = Validator::make($request->all(), [
            // ...
        ]);

        // If the validation fails, throw a ValidationException
        $validator->validate();

        // Find the health by ID or throw a ModelNotFoundException
        $health = Health::findOrFail($health_id);

        // Update the health fields
        $health->update($request->all());

        return redirect()->route('Health.showhealth', ['animal_id' => $animal_id])
            ->with('success', 'health updated successfully.');
    }


    public function deletehealth($animal_id, $health_id)
{
    $health = Health::findOrFail($health_id);
    $health->delete();

    return redirect()->route('Health.showhealth', ['animal_id' => $animal_id])
        ->with('success', 'health deleted successfully.');
}
}
