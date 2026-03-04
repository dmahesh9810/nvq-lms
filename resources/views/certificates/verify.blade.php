<x-guest-layout>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-8 text-gray-900 text-center">
                    
                    <div class="flex justify-center mb-6">
                        <svg class="w-16 h-16 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                    </div>

                    <h2 class="text-3xl font-extrabold text-blue-900 mb-2">Verify Certificate</h2>
                    <p class="text-gray-500 mb-8">Enter the unique tracking number found at the bottom of the certificate.</p>
                    
                    <form method="POST" action="{{ route('verify.submit') }}" class="max-w-md mx-auto space-y-6">
                        @csrf
                        
                        <div>
                            <label for="certificate_number" class="sr-only">Certificate Number</label>
                            <input 
                                type="text" 
                                name="certificate_number" 
                                id="certificate_number" 
                                required
                                value="{{ old('certificate_number') }}"
                                placeholder="e.g. IQB-2026-A1B2C3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-center text-lg py-3 uppercase tracking-wider"
                            >
                            @error('certificate_number')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-lg font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                                Verify Now
                            </button>
                        </div>
                    </form>

                </div>
            </div>
            
            <div class="text-center text-sm text-gray-400">
                <p>IQBrave LMS &copy; {{ date('Y') }}</p>
            </div>
        </div>
    </div>
</x-guest-layout>
