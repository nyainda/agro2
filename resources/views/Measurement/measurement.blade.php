
<x-app-layout title="measurements">


    <!-- Modal Content -->
    <div class="container mx-auto font-serif  mt-8 p-4 mb-4 bg-gray-20  dark:bg-gray-800 dark:rounded-lg shadow-lg">
        <!-- Modal Header -->

            <h3 class="text-lg dark:text-gray-200  font-semibold">New Measurement</h3>


        <!-- Modal Body -->
        <div class="p-4">
            <form action="{{ route('Measurement.storemeasurement', ['animal_id' => $animal->id]) }}" method="POST">
                @csrf

                <div class="mb-4 col-span-2">
                    <label for="type " class="block text-sm dark:text-gray-200 font-medium text-gray-600">Animal</label>
                    <select name="type" id="animalType" class="mt-1 p-2 border dark:text-gray-200 dark:bg-gray-700 rounded-md w-full " >
        <option value="{{$animal->type}}">{{$animal->type}}</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4 flex flex-col">
                        <label for="measurement_weight" class="text-gray-700 dark:text-gray-300 font-medium ">
                          Weight <span class="ml-1 text-sm">(kg)</span>
                        </label>
                        <div class="relative">
                          <input
                            class="form-input w-full py-2 px-4 rounded-md border border-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-300"
                            placeholder="Enter Weight"
                            max="100000"
                            step="any"
                            type="number"
                            name="weight"
                            id="measurement_weight"
                          />
                        </div>
                      </div>

                      <div class="mb-4">
                        <label for="measurement_height" class="text-gray-700 dark:text-gray-300 font-medium mb-4">
                          Height <span class="ml-1 text-sm">(meter)</span>
                        </label>
                        <div class="relative">
                          <input
                            class="form-input w-full py-2 px-4 rounded-md border border-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-300"
                            placeholder=""
                            step="any"
                            max="1000"
                            type="number"

                            name="height"
                            id="measurement_height"
                          />
                        </div>
                      </div>

                      <div class="mb-4">
                        <label for="measurement_condition_score" class="text-gray-700 dark:text-gray-300 font-medium mb-2">
                          Condition score <span class="ml-1 text-sm">(BCS)</span>
                        </label>
                        <div class="relative">
                          <input
                            class="form-input w-full py-2 px-4 rounded-md border border-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-300"
                            step=".5"
                            min="0"
                            max="100"

                            type="number"
                            name="condition_score"
                            id="measurement_condition_score"
                          />
                        </div>
                      </div>

                      <div class="mb-4">
                        <label for="measurement_temp" class="text-gray-700 dark:text-gray-300 font-medium mb-2">
                          Temperature <span class="ml-1 text-sm">(°C)</span>
                        </label>
                        <div class="relative">
                          <input
                            class="form-input w-full py-2 px-4 rounded-md border border-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-300"
                            step=".5"
                            min="0"
                            max="200"

                            type="number"
                            name="temp"
                            id="measurement_temp"
                          />
                        </div>
                      </div>

                      <div class="mb-4">
                        <label for="measurement_fec" class="text-gray-700 dark:text-gray-300 font-medium mb-2">
                          Fecal egg count <span class="ml-1 text-sm">(FEC)</span>
                        </label>
                        <div class="relative">
                          <input
                            class="form-input w-full py-2 px-4 rounded-md border border-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-300"
                            step="1"
                            min="0"
                            max="99999"

                            type="number"
                            name="fec"
                            id="measurement_fec"
                          />
                        </div>
                      </div>


                    <!-- Additional Fields -->
                    <div class="mb-4">
                        <label for="measurement_heart_rate" class="text-gray-700 dark:text-gray-300 font-medium mb-2">
                          Heart Rate <span class="ml-1 text-sm">(bpm)</span>
                        </label>
                        <div class="relative">
                          <input
                            class="form-input w-full py-2 px-4 rounded-md border border-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-300"
                            step="1"
                            min="0"
                            max="300"

                            type="number"
                            name="heart_rate"
                            id="measurement_heart_rate"
                          />
                        </div>
                      </div>
                      <div class="mb-4">
                        <label for="measurement_respiratory_rate" class="text-gray-700 dark:text-gray-300 font-medium mb-2">
                          Respiratory Rate <span class="ml-1 text-sm">(breaths/min)</span>
                        </label>
                        <div class="relative">
                          <input
                            class="form-input w-full py-2 px-4 rounded-md border border-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-300"
                            step="1"
                            min="0"
                            max="100"

                            type="number"
                            name="respiratory_rate"
                            id="measurement_respiratory_rate"
                          />
                        </div>
                      </div>
                      <div class="mb-4">
                        <label for="measurement_systolic_bp" class="text-gray-700 dark:text-gray-300 font-medium mb-2">
                          Systolic Blood Pressure <span class="ml-1 text-sm">(mmHg)</span>
                        </label>
                        <div class="relative">
                          <input
                            class="form-input w-full py-2 px-4 rounded-md border border-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-300"
                            step="1"
                            min="0"
                            max="300"

                            type="number"
                            name="systolic_bp"
                            id="measurement_systolic_bp"
                          />
                        </div>
                      </div>

                      <div class="mb-4">
                        <label for="measurement_diastolic_bp" class="text-gray-700 dark:text-gray-300 font-medium mb-2">
                          Diastolic Blood Pressure <span class="ml-1 text-sm">(mmHg)</span>
                        </label>
                        <div class="relative">
                          <input
                            class="form-input w-full py-2 px-4 rounded-md border border-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-300"
                            step="1"
                            min="0"
                            max="200"

                            type="number"
                            name="diastolic_bp"
                            id="measurement_diastolic_bp"
                          />
                        </div>
                      </div>
                      <div class="mb-4">
                        <label for="measurement_pulse_oximetry" class="text-gray-700 dark:text-gray-300 font-medium mb-2">
                          Pulse Oximetry (SpO2) <span class="ml-1 text-sm">(%)</span>
                        </label>
                        <div class="relative">
                          <input
                            class="form-input w-full py-2 px-4 rounded-md border border-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-300"
                            step=".1"
                            min="0"
                            max="100"

                            type="number"
                            name="pulse_oximetry"
                            id="measurement_pulse_oximetry"
                          />
                        </div>
                      </div>

                      <div class="mb-4">
                        <label for="measurement_date" class="text-gray-700 dark:text-gray-300 font-medium mb-2">
                          Measurement date
                        </label>
                        <input
                          class="form-input w-full py-2 px-4 rounded-md border border-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-300"

                          type="date"
                          name="date"
                          id="measurement_date"
                        />
                      </div>

                </div>
<hr class="mt-4">
                <div class="flex justify-end  mt-6">
                    <button type="button" class="px-3 py-2 text-sm mr-4 mb-4 dark:text-gray-100  tracking-wide text-white capitalize transition-colors duration-200 transform bg-red-500 rounded-md dark:bg-indigo-600 dark:hover:bg-indigo-700 dark:focus:bg-indigo-700 hover:bg-indigo-600 focus:outline-none focus:bg-indigo-500 focus:ring focus:ring-indigo-300 focus:ring-opacity-50">

                        <a href="{{ route('index')}}" class="btn btn-gray-500">Cancel</a>
                    </button>
                    <button type="submit" name="action" value="save"  class="px-3 btn btn-success mb-4 py-2 text-sm mr-4 tracking-wide text-white capitalize transition-colors duration-200 transform bg-indigo-500 rounded-md dark:bg-indigo-600 dark:hover:bg-indigo-700 dark:focus:bg-indigo-700 hover:bg-indigo-600 focus:outline-none focus:bg-indigo-500 focus:ring focus:ring-indigo-300 focus:ring-opacity-50">
                        Save
                    </button>
            </form>

    </div>


</x-app-layout>
