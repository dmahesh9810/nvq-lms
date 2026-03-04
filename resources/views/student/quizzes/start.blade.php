<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Start Quiz: ') . $quiz->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    <h3 class="text-2xl font-bold mb-4">{{ $quiz->title }}</h3>
                    <p class="mb-6 text-gray-600">{{ $quiz->description }}</p>
                    
                    <div class="mb-8 p-4 bg-gray-50 rounded-lg border">
                        <ul class="list-disc list-inside space-y-2">
                            <li><strong>Pass Mark:</strong> {{ $quiz->pass_mark }}%</li>
                            <li><strong>Total Marks Available:</strong> {{ $quiz->totalMarks() }}</li>
                            <li><strong>Questions:</strong> {{ $quiz->questions()->count() }}</li>
                        </ul>
                    </div>

                    <form action="{{ route('student.quizzes.start', $quiz) }}" method="POST">
                        @csrf
                        <div class="flex items-center justify-end">
                            <a href="{{ route('student.quizzes.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                            <x-primary-button>
                                {{ __('Start Quiz Now') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
