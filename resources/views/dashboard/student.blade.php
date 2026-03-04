<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            {{-- Analytics Overview --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Courses -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Enrolled Courses</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ $courses->count() }}</div>
                </div>

                <!-- Lessons Progress -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-500">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Lessons Completed</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $totalLessonsCompleted }} <span class="text-lg text-gray-400 font-normal">/ {{ $totalLessons }}</span>
                    </div>
                </div>

                <!-- Quizzes Progress -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-indigo-500">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Quizzes Passed</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $totalQuizzesPassed }} <span class="text-lg text-gray-400 font-normal">/ {{ $totalQuizzes }}</span>
                    </div>
                </div>

                <!-- Certificates -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-500">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Certificates Earned</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ $certificates->count() }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- Left Column (Wider: My Courses & Quiz History) --}}
                <div class="lg:col-span-2 space-y-8">
                    
                    {{-- My Courses --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800">My Learning Path</h3>
                        </div>
                        <div class="p-6">
                            @if($courses->isEmpty())
                                <p class="text-gray-500 text-center py-4">You are not enrolled in any courses yet. <a href="{{ route('student.courses.browse') }}" class="text-blue-600 hover:underline">Browse Catalog</a></p>
                            @else
                                <ul class="divide-y divide-gray-200">
                                    @foreach($courses as $course)
                                        <li class="py-4">
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1">
                                                    <a href="{{ route('student.courses.show', $course) }}" class="text-lg font-semibold text-blue-600 hover:text-blue-800 transition">
                                                        {{ $course->title }}
                                                    </a>
                                                    <p class="text-sm text-gray-500 mt-1">{{ $course->modules->count() }} Modules</p>
                                                </div>
                                                <div class="w-1/3 text-right">
                                                    <div class="flex items-center justify-end mb-1">
                                                        <span class="text-sm font-medium text-gray-700">{{ $courseProgress[$course->id] }}%</span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                        <div class="h-2.5 rounded-full {{ $courseProgress[$course->id] == 100 ? 'bg-green-600' : 'bg-blue-600' }}" style="width: {{ $courseProgress[$course->id] }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>

                    {{-- Quiz Results --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800">Recent Quiz Results</h3>
                        </div>
                        <div class="p-0">
                            @if($quizAttempts->isEmpty())
                                <p class="text-gray-500 text-center p-6">No quizzes attempted yet.</p>
                            @else
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quiz</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Result</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($quizAttempts->take(5) as $attempt)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <a href="{{ route('student.quizzes.result', [$attempt->quiz_id, $attempt->id]) }}" class="text-sm font-medium text-blue-600 hover:underline">
                                                        {{ $attempt->quiz->title }}
                                                    </a>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 font-semibold">
                                                    {{ $attempt->score }} <span class="text-gray-400 font-normal text-xs">marks</span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    @if($attempt->result === 'PASS')
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">PASS</span>
                                                    @else
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">FAIL</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                                    {{ $attempt->completed_at->format('M j, Y') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>

                </div>

                {{-- Right Column (Narrower: Certificates) --}}
                <div class="space-y-8">
                    
                    {{-- Certificates Panel --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-800">My Certificates</h3>
                            <svg class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                        </div>
                        <div class="p-6">
                            @if($certificates->isEmpty())
                                <p class="text-gray-500 text-center text-sm py-4">Complete a course to earn your first certificate!</p>
                            @else
                                <ul class="divide-y divide-gray-100">
                                    @foreach($certificates as $cert)
                                        <li class="py-4">
                                            <div class="mb-2">
                                                <h4 class="text-sm font-semibold text-gray-900">{{ $cert->course->title }}</h4>
                                                <p class="text-xs text-gray-500 mt-1">Issued: {{ $cert->issued_at->format('M j, Y') }}</p>
                                                <p class="text-xs font-mono text-gray-400 mt-0.5">ID: {{ $cert->certificate_number }}</p>
                                            </div>
                                            
                                            <div class="mt-3 flex items-center justify-between">
                                                @if($cert->status === 'active')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                        Active
                                                    </span>
                                                    <a href="{{ route('student.certificates.download', $cert) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center">
                                                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                        </svg>
                                                        Download PDF
                                                    </a>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                        Revoked
                                                    </span>
                                                    <span class="text-sm text-gray-400 font-medium cursor-not-allowed">Unavailable</span>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</x-app-layout>
