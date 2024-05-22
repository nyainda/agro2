<?php

namespace App\Http\Controllers;


use App\Models\Animal;
use App\Models\Supplier;
use App\Models\Contact;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;


class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexsupplier()
    {
        $suppliers = Supplier::latest()->get();
        return view('Supplier.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createsupplier($animal_id)
{

    $animal = Animal::find($animal_id);
    $Contacts = Contact::where('contact_type', 'Supplier')->get();
    $suppliers = Supplier::all();

    $contactNames = $Contacts->pluck('first_name', 'last_name')->toArray();

    return view('Supplier.create', compact('animal', 'Contacts', 'contactNames','suppliers'));
}


public function storesupplier(Request $request, $animal_id)
{
    // Validation rules for the form fields (you can customize these)
    $validator = Validator::make($request->all(), [

    ]);

    $supplier = new Supplier();

    $image = $request->file('photo');
    $slug =  Str::slug($request->input('name'));
    if (isset($image))
    {
        $currentDate = Carbon::now()->toDateString();
        $imageName = $slug.'-'.$currentDate.'-'.uniqid().'.'.$image->getClientOriginalExtension();
        if (!Storage::disk('public')->exists('supplier'))
        {
            Storage::disk('public')->makeDirectory('supplier');
        }
        $postImage = Image::make($image)->resize(480, 320)->stream();
        Storage::disk('public')->put('supplier/'.$imageName, $postImage);
    } else
    {
        $imageName = 'default.png';
    }



    // Generate a UUID for the id field
    $supplier->id = Str::uuid();

    // Fill in the other supplier fields
    $supplier->fill($request->all());
    $supplier->animal_id = $animal_id;
    $supplier->user_id = auth()->user()->id;
    $supplier->save();

    return redirect()->route('Supplier.show', ['animal_id' => $animal_id])
    ->with('success', 'supplier created successfully.');

}


public function showsupplier($animal_id)
{
    try {

        $user = auth()->user();
        $animal = Animal::find($animal_id);
        $suppliers = Supplier::with('animal')
             ->where('user_id',$user->id)
             ->where('animal_id',$animal_id)
             ->orderBy('created_at','desc')
             ->paginate(5);


        // Check if the animal's status is 'sold'
        if ($animal->status === 'sold') {
            return redirect()->route('index')->with('error', 'This animal has already been sold and cannot add meaasurement/edit.');
        }

        return view('Supplier.show', ['animal' => $animal, 'suppliers' => $suppliers,'user'=>$user]);
    } catch (\Exception $e) {
        return redirect()->route('index')->with('error', 'An error occurred while displaying the suppliers.');
    }
}



public function supplier($animal_id)
{
    try {
        // Retrieve the animal by ID
        $animal = Animal::find($animal_id);
        $animal->user_id = auth()->user()->id;
        if (!$animal) {
            // Redirect to the home page with a "not found" flash message
            return Redirect::route('index')->with('error', 'Animal not found.');
        }

        return view('Supplier.supplier', ['animal' => $animal]);
    } catch (\Exception $e) {
        return redirect()->route('index')->with('error', 'An error occurred while displaying the supplier form.');
    }
}

public function editsupplier($animal_id, $supplier_id)
{
$animal = Animal::find($animal_id);
try {
    $supplier = Supplier::findOrFail($supplier_id);
} catch (\Exception $e) {
    // Handle the exception here, for example, redirect to an error page
    return redirect()->route('index')->with('error', 'supplier not found.');
}

$Contacts = Contact::where('contact_type', 'Supplier')->get();

    // Extract contact names for datalist
$contactNames = $Contacts->pluck('first_name', 'last_name')->toArray();

return view('Supplier.edit', ['animal' => $animal, 'supplier' => $supplier, 'Contacts'=>$Contacts, 'contactNames'=>$contactNames]);
}

// take care of the updating the animal treatment
public function updatesupplier(Request $request, $animal_id, $supplier_id, Supplier $supplier)
{
$validator = Validator::make($request->all(), [

    // Validation rules for treatment update fields
]);

if ($validator->fails()) {
    return redirect()->back()
        ->withErrors($validator)
        ->withInput();
}
$image = $request->file('photo');
        $slug =  Str::slug($request->input('name'));
        if (isset($image))
        {
            $currentDate = Carbon::now()->toDateString();
            $imageName = $slug.'-'.$currentDate.'-'.uniqid().'.'.$image->getClientOriginalExtension();
            if (!Storage::disk('public')->exists('supplier'))
            {
                Storage::disk('public')->makeDirectory('supplier');
            }

            // delete old photo
            if (Storage::disk('public')->exists('supplier/'. $supplier->photo))
            {
                Storage::disk('public')->delete('supplier/'. $supplier->photo);
            }

            $postImage = Image::make($image)->resize(480, 320)->stream();
            Storage::disk('public')->put('supplier/'.$imageName, $postImage);
        } else
        {
            $imageName = $supplier->photo;
        }
$supplier = Supplier::findOrFail($supplier_id);
$supplier->update($request->all());

return redirect()->route('Supplier.show', ['animal_id' => $animal_id])
    ->with('success', 'Supplier updated successfully.');
}
// delete the animal treatment
public function deletesupplier($animal_id, $supplier_id)
{
$supplier = Supplier::findOrFail($supplier_id);
$supplier->delete();

return redirect()->route('Supplier.show', ['animal_id' => $animal_id])
    ->with('success', 'Supplier deleted successfully.');
}

}
