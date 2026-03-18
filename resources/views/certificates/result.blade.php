<x-guest-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-8">
                    
                    <div class="flex items-center justify-between mb-8 border-b border-gray-100 pb-4">
                        <h2 class="text-2xl font-bold text-gray-800">Verification Result</h2>
                        <a href="{{ route('verify.form') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                            &larr; Search Again
                        </a>
                    </div>

                    @if(!$certificate)
                        <div class="rounded-md bg-red-50 p-6 text-center border border-red-200">
                            <div class="flex justify-center mb-4">
                                <svg class="h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-medium text-red-800 mb-2">Certificate Not Found</h3>
                            <p class="text-red-600 text-sm">
                                The tracking number provided does not match any records in our system. Please check the number and try again.
                            </p>
                        </div>
                    @else
                        
                        @if($certificate->status === 'revoked')
                            <div class="rounded-md bg-yellow-50 p-4 mb-6 border border-yellow-200 flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Status Alert</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p>This certificate exists in the system but has been marked as <strong>REVOKED</strong> by administration. Tests indicating authenticity for current usage may be void.</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="rounded-md bg-green-50 p-4 mb-6 border border-green-200 flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">Authentic Certificate</h3>
                                    <div class="mt-2 text-sm text-green-700">
                                        <p>This is a valid, officially issued certificate registered in the {{ config('app.name') }}.</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                            <dl class="divide-y divide-gray-200">
                                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5">
                                    <dt class="text-sm font-medium text-gray-500">Tracking Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0 font-mono font-bold tracking-wider">{{ $certificate->certificate_number }}</dd>
                                </div>
                                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5">
                                    <dt class="text-sm font-medium text-gray-500">Recipient Name</dt>
                                    <dd class="mt-1 text-lg font-semibold text-gray-900 sm:col-span-2 sm:mt-0">{{ $certificate->user->name }}</dd>
                                </div>
                                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5">
                                    <dt class="text-sm font-medium text-gray-500">Certification Target</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $certificate->course->title }}</dd>
                                </div>
                                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5">
                                    <dt class="text-sm font-medium text-gray-500">Date of Issue</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $certificate->issued_at->format('F j, Y') }}</dd>
                                </div>
                                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5">
                                    <dt class="text-sm font-medium text-gray-500">Current Status</dt>
                                    <dd class="mt-1 text-sm sm:col-span-2 sm:mt-0">
                                        @if($certificate->status === 'active')
                                            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                Active / Valid
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                                Revoked
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    @endif

                </div>
            </div>
            
            <div class="text-center text-sm text-gray-400">
                <p>{{ config('app.name') }} &copy; {{ date('Y') }}</p>
            </div>
        </div>
    </div>
</x-guest-layout>
