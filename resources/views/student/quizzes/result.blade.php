<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Quiz Results: ') . $quiz->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-8 text-center border-b border-gray-200">
                    @if(session('success'))
                        <div class="mb-4 text-green-600 bg-green-50 p-3 rounded-lg border border-green-200">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('info'))
                        <div class="mb-4 text-blue-600 bg-blue-50 p-3 rounded-lg border border-blue-200">
                            {{ session('info') }}
                        </div>
                    @endif

                    <h3 class="text-3xl font-bold mb-2">Quiz Completed!</h3>
                    
                    <div class="flex justify-center items-center mt-6 mb-6 space-x-12">
                        <div class="text-center">
                            <p class="text-gray-500 text-sm uppercase tracking-wider font-semibold">Your Score</p>
                            <p class="text-4xl font-black mt-1 text-gray-800">{{ $attempt->score }} <span class="text-xl text-gray-400 font-medium">/ {{ $quiz->totalMarks() }}</span></p>
                        </div>
                        
                        <div class="text-center">
                            <p class="text-gray-500 text-sm uppercase tracking-wider font-semibold">Status</p>
                            <div class="mt-2">
                                @if($attempt->result === 'PASS')
                                    <span class="px-4 py-2 bg-green-100 text-green-800 rounded-full text-lg font-bold shadow-sm inline-block">PASSED</span>
                                @else
                                    <span class="px-4 py-2 bg-red-100 text-red-800 rounded-full text-lg font-bold shadow-sm inline-block">FAILED</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h4 class="text-xl font-bold text-gray-800 mb-6 pb-2 border-b">Review Your Answers</h4>

                    @foreach ($quiz->questions as $index => $question)
                        @php
                            // Find the answer for this question from the attempt's loaded answers
                            $studentAnswer = $attempt->answers->firstWhere('question_id', $question->id);
                            $selectedOptionId = $studentAnswer ? $studentAnswer->selected_option_id : null;
                            $isCorrect = $studentAnswer ? $studentAnswer->is_correct : false;
                        @endphp

                        <div class="mb-6 p-5 bg-white rounded-lg border {{ $isCorrect ? 'border-green-300' : 'border-red-300' }} shadow-sm">
                            <div class="flex justify-between items-start mb-3">
                                <h5 class="text-md font-bold text-gray-800">
                                    {{ $index + 1 }}. {{ $question->question_text }}
                                </h5>
                                @if($isCorrect)
                                    <span class="text-green-600 font-bold flex items-center">
                                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        {{ $question->marks }} / {{ $question->marks }}
                                    </span>
                                @else
                                    <span class="text-red-500 font-bold flex items-center">
                                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        0 / {{ $question->marks }}
                                    </span>
                                @endif
                            </div>

                            <div class="space-y-2 mt-3 pl-2 border-l-2 border-gray-100">
                                @foreach ($question->options as $option)
                                    @php
                                        $isSelected = ($selectedOptionId == $option->id);
                                        $isActualCorrect = $option->is_correct;
                                        
                                        $optionClass = 'text-gray-600';
                                        $icon = '';
                                        
                                        if ($isActualCorrect) {
                                            $optionClass = 'text-green-700 font-bold bg-green-50 rounded px-2 py-1';
                                            $icon = '<svg class="w-4 h-4 ml-2 text-green-600 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                                        } elseif ($isSelected && !$isActualCorrect) {
                                            $optionClass = 'text-red-700 bg-red-50 rounded px-2 py-1';
                                            $icon = '<svg class="w-4 h-4 ml-2 text-red-500 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
                                        }
                                    @endphp

                                    <div class="flex items-center text-sm mb-1 py-1">
                                        <div class="w-5 h-5 flex-shrink-0 mr-3 ml-1 rounded-full border border-gray-300 {{ $isSelected ? ($isActualCorrect ? 'bg-green-500 border-green-500' : 'bg-red-500 border-red-500') : 'bg-white' }} flex items-center justify-center">
                                            @if($isSelected)
                                                <div class="w-2 h-2 bg-white rounded-full"></div>
                                            @endif
                                        </div>
                                        <span class="{{ $optionClass }} flex items-center w-full">
                                            {{ $option->option_text }}
                                            {!! $icon !!}
                                            @if($isSelected)
                                                <span class="ml-2 text-xs text-gray-400 italic">(Your Answer)</span>
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    
                    <div class="mt-8 text-center">
                        <a href="{{ route('student.quizzes.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Back to Quizzes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
