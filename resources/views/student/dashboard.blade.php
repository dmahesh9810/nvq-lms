<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Analytics Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- High Level KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Enrolled Courses -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Enrolled Courses</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ $courses->count() }}</div>
                </div>

                <!-- Lessons Completed -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-indigo-500">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Lessons Completed</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $totalLessonsCompleted }} <span class="text-lg text-gray-400 font-normal">/ {{ $totalLessons }}</span>
                    </div>
                </div>

                <!-- Quizzes Passed -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-500">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Quizzes Passed</div>
                    <div class="mt-2 text-3xl font-bold text-green-600">
                        {{ $totalQuizzesPassed }} <span class="text-lg text-gray-400 font-normal">/ {{ $totalQuizzes }}</span>
                    </div>
                </div>

                <!-- Certificates Earned -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-500">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Certificates Earned</div>
                    <div class="mt-2 text-3xl font-bold text-yellow-600">{{ $certificates->count() }}</div>
                </div>
            </div>

            <!-- Main Dashboard Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Left Column (Wider): Courses & Quizzes -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- My Enrolled Courses -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">My Active Courses</h3>
                        
                        @forelse($courses as $course)
                            @php
                                $progress = $courseProgress[$course->id] ?? 0;
                            @endphp
                            <div class="mb-6 last:mb-0">
                                <div class="flex justify-between items-center mb-2">
                                    <h4 class="text-md font-semibold text-gray-800">{{ $course->title }}</h4>
                                    <span class="text-sm font-medium {{ $progress == 100 ? 'text-green-600' : 'text-blue-600' }}">
                                        {{ $progress }}% Completed
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="{{ $progress == 100 ? 'bg-green-500' : 'bg-blue-600' }} h-2.5 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                                </div>
                                <div class="mt-3 text-sm text-gray-600 flex justify-between">
                                    <span>Modules: {{ $course->modules->count() }}</span>
                                    <a href="{{ route('student.courses.show', $course) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">Continue Learning &rarr;</a>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 italic">You are not enrolled in any courses yet.</p>
                        @endforelse
                    </div>

                    <!-- Recent Quiz Attempts -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 h-96 overflow-y-auto">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2 sticky top-0 bg-white">Recent Quiz Results</h3>
                        
                        @if($quizAttempts->count() > 0)
                            <div class="space-y-4">
                                @foreach($quizAttempts as $attempt)
                                    <div class="flex items-center justify-between p-4 rounded-lg border {{ $attempt->result === 'PASS' ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-800">{{ $attempt->quiz->title ?? 'Unknown Quiz' }}</h4>
                                            <p class="text-xs text-gray-500 mt-1">{{ $attempt->completed_at->format('M d, Y h:i A') }}</p>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xl font-bold {{ $attempt->result === 'PASS' ? 'text-green-600' : 'text-red-600' }}">
                                                {{ rtrim(rtrim(number_format($attempt->score, 2), '0'), '.') }}%
                                            </div>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $attempt->result === 'PASS' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }}">
                                                {{ $attempt->result }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 italic">No quiz attempts found. Start learning to take your first quiz!</p>
                        @endif
                    </div>

                </div>

                <!-- Right Column (Narrower): Certificates & Badges -->
                <div class="space-y-6">
                    <!-- My Certificates -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 h-full border-t-4 border-yellow-400">
                        <div class="flex items-center mb-6">
                            <svg class="w-6 h-6 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <h3 class="text-lg font-bold text-gray-900">Earned Certificates</h3>
                        </div>
                        
                        @forelse($certificates as $certificate)
                            <div class="mb-4 bg-gray-50 p-4 rounded-lg border border-gray-100 hover:shadow-md transition duration-200">
                                <div class="text-xs font-bold text-indigo-600 mb-1">
                                    {{ $certificate->course->title ?? 'Course' }}
                                </div>
                                <div class="text-xs text-gray-500 mb-3">
                                    Issued: {{ $certificate->issued_at->format('M d, Y') }}<br>
                                    ID: {{ $certificate->certificate_number }}
                                </div>
                                <a href="{{ route('student.certificates.download', $certificate) }}" target="_blank" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Download PDF
                                </a>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                <p class="text-gray-500 italic text-sm">Graduation pending.</p>
                                <p class="text-gray-400 text-xs mt-1">Complete your courses to earn certificates.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
