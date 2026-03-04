<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Instructor Analytics Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            @if($courses->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
                    <p class="text-gray-500 text-center">You have not created any courses yet to analyze.</p>
                </div>
            @endif

            @foreach($courses as $course)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200" x-data="{ expanded: false }">
                    <!-- Course Header -->
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center cursor-pointer hover:bg-gray-50 transition" @click="expanded = !expanded">
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900">{{ $course->title }}</h3>
                            <p class="text-sm text-gray-500 mt-1">Status: <span class="uppercase font-medium {{ $course->status === 'published' ? 'text-green-600' : 'text-yellow-600' }}">{{ $course->status }}</span></p>
                        </div>
                        <div class="hidden md:flex space-x-8 mr-8 text-center">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Enrollments</p>
                                <p class="text-2xl font-bold text-blue-600">{{ $course->enrollments_count }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Certificates</p>
                                <p class="text-2xl font-bold text-yellow-500">{{ $course->certificates_count }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Avg. Completion</p>
                                <p class="text-2xl font-bold text-green-600">{{ $course->average_completion_percentage }}%</p>
                            </div>
                        </div>
                        <div>
                            <svg class="w-6 h-6 text-gray-400 transform transition-transform" :class="{'rotate-180': expanded}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <!-- Expandable Details -->
                    <div x-show="expanded" style="display: none;" class="p-6 bg-gray-50">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            
                            <!-- Lesson Engagement -->
                            <div>
                                <div class="flex items-center space-x-2 mb-4 border-b border-gray-200 pb-2">
                                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h4 class="text-md font-bold text-gray-700">Lesson Engagement</h4>
                                </div>
                                <div class="space-y-3">
                                    @php $hasLessons = false; @endphp
                                    @foreach($course->modules as $module)
                                        @foreach($module->units as $unit)
                                            @foreach($unit->lessons as $lesson)
                                                @php $hasLessons = true; @endphp
                                                <div class="flex justify-between items-center text-sm bg-white p-3 rounded shadow-sm border border-gray-100">
                                                    <span class="text-gray-800 font-medium truncate pr-4">{{ $lesson->title }}</span>
                                                    <span class="text-gray-500 whitespace-nowrap"><strong class="text-indigo-600 font-bold text-base">{{ $lesson->completions_count }}</strong> completions</span>
                                                </div>
                                            @endforeach
                                        @endforeach
                                    @endforeach
                                    @if(!$hasLessons)
                                        <p class="text-sm text-gray-500 italic">No lessons available in this course.</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Quiz Analytics -->
                            <div>
                                <div class="flex items-center space-x-2 mb-4 border-b border-gray-200 pb-2">
                                    <svg class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <h4 class="text-md font-bold text-gray-700">Quiz Analytics</h4>
                                </div>
                                <div class="space-y-4">
                                    @php $hasQuizzes = false; @endphp
                                    @foreach($course->modules as $module)
                                        @foreach($module->units as $unit)
                                            @foreach($unit->quizzes as $quiz)
                                                @php $hasQuizzes = true; @endphp
                                                <div class="bg-white p-4 rounded shadow border border-gray-100">
                                                    <div class="flex justify-between items-center mb-3">
                                                        <span class="text-gray-800 font-bold truncate pr-3">{{ $quiz->title }}</span>
                                                        <span class="text-xs font-semibold bg-gray-100 text-gray-600 px-2 py-1 rounded">Pass Threshold: {{ $quiz->pass_mark }}%</span>
                                                    </div>
                                                    <div class="grid grid-cols-3 gap-2 text-center text-sm bg-gray-50 py-3 rounded">
                                                        <div>
                                                            <p class="text-gray-500 text-xs mb-1">Total Attempts</p>
                                                            <p class="font-bold text-gray-900 text-lg">{{ $quiz->total_attempts }}</p>
                                                        </div>
                                                        <div class="border-l border-r border-gray-200">
                                                            <p class="text-gray-500 text-xs mb-1">Avg Score</p>
                                                            <p class="font-bold text-purple-600 text-lg">{{ round($quiz->score_avg, 1) }}</p>
                                                        </div>
                                                        <div>
                                                            <p class="text-gray-500 text-xs mb-1">Pass Rate</p>
                                                            <p class="font-bold text-lg {{ ($quiz->total_attempts > 0 && ($quiz->passed_attempts / $quiz->total_attempts) * 100 >= 50) ? 'text-green-500' : 'text-red-500' }}">
                                                                {{ $quiz->total_attempts > 0 ? round(($quiz->passed_attempts / $quiz->total_attempts) * 100, 1) : 0 }}%
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endforeach
                                    @endforeach
                                    @if(!$hasQuizzes)
                                        <p class="text-sm text-gray-500 italic">No active quizzes mapped to this course.</p>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</x-app-layout>
