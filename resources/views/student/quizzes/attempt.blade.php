<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Attempting Quiz: ') . $quiz->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <form action="{{ route('student.quizzes.submit', [$quiz, $attempt]) }}" method="POST">
                        @csrf
                        
                        @foreach ($quiz->questions as $index => $question)
                        <div class="mb-8 p-6 bg-gray-50 rounded-lg border border-gray-200 shadow-sm">
                            <div class="flex justify-between items-start mb-4">
                                <h4 class="text-lg font-semibold text-gray-800">
                                    {{ $index + 1 }}. {{ $question->question_text }}
                                </h4>
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                    {{ $question->marks }} Marks
                                </span>
                            </div>
                            
                            <div class="space-y-3 mt-4 ml-2">
                                @foreach ($question->options as $option)
                                <label class="flex items-center space-x-3 cursor-pointer p-2 rounded hover:bg-gray-100 transition">
                                    <input type="radio" 
                                           name="answers[{{ $question->id }}]" 
                                           value="{{ $option->id }}"
                                           class="form-radio h-5 w-5 text-indigo-600 transition duration-150 ease-in-out border-gray-300">
                                    <span class="text-gray-700 font-medium">{{ $option->option_text }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach

                        <div class="flex items-center justify-between mt-8 pt-4 border-t border-gray-200">
                            <p class="text-sm text-gray-500">Make sure you have answered all questions before submitting.</p>
                            <x-primary-button type="submit" onclick="return confirm('Are you sure you want to submit your answers? You cannot change them after submitting.')">
                                {{ __('Submit Quiz') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
