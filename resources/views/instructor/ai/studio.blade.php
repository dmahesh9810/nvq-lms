@extends('layouts.app')

@section('title', 'AI Content Studio')
@section('page-title', 'AI Content Studio')

@section('content')
<div class="container-fluid py-4" x-data="aiStudio()">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><span class="text-primary">✨ AI Content Studio</span></h2>
            <p class="text-muted mb-0">Generate interactive Micro-Topics, Concept Cards, and Quizzes instantly from your lecture notes for: <strong>{{ $lesson->title }}</strong></p>
        </div>
        <a href="{{ route('instructor.lessons.micro-topics.index', $lesson) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Topics
        </a>
    </div>

    <div class="row g-4">
        {{-- Left Column: Input --}}
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text me-2 text-primary"></i>1. Paste Lecture Notes</h5>
                </div>
                <div class="card-body">
                    <form @submit.prevent="generateContent">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Topic Name</label>
                            <input type="text" x-model="input.topic_name" class="form-control" placeholder="e.g. CPU Architecture" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Raw Notes / Transcript</label>
                            <textarea x-model="input.notes" class="form-control" rows="12" placeholder="Paste your lecture notes, textbook excerpt, or presentation transcript here..." required minlength="50"></textarea>
                            <div class="form-text text-muted">The more detailed the notes, the better the AI output.</div>
                        </div>

                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <label class="form-label fw-semibold small">Concept Cards</label>
                                <input type="number" x-model="input.card_count" class="form-control form-control-sm" min="2" max="8" value="4">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold small">Quiz Questions</label>
                                <input type="number" x-model="input.quiz_count" class="form-control form-control-sm" min="2" max="10" value="4">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm py-2" :disabled="isLoading">
                            <span x-show="!isLoading"><i class="bi bi-magic me-2"></i>Generate with Gemini AI</span>
                            <span x-show="isLoading" style="display:none;">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Generating Content...
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Right Column: Output / Preview --}}
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm h-100" style="background-color: #f8f9fa;">
                <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-phone me-2 text-success"></i>2. Preview & Save</h5>
                    
                    {{-- Save Form (Hidden inputs bound to Alpine state) --}}
                    <form action="{{ route('instructor.lessons.ai-studio.save', $lesson) }}" method="POST" x-show="isGenerated" style="display:none;">
                        @csrf
                        <input type="hidden" name="topic_name" :value="output.topic_name">
                        <input type="hidden" name="estimated_minutes" :value="output.estimated_minutes">
                        <input type="hidden" name="key_takeaway" :value="output.key_takeaway">
                        <input type="hidden" name="concept_cards" :value="JSON.stringify(output.concept_cards)">
                        <input type="hidden" name="quiz_questions" :value="JSON.stringify(output.quiz_questions)">
                        
                        <button type="submit" class="btn btn-success btn-sm fw-bold shadow-sm">
                            <i class="bi bi-cloud-check me-1"></i> Save to Course
                        </button>
                    </form>
                </div>
                <div class="card-body p-4 overflow-auto" style="max-height: 800px;">
                    
                    {{-- Empty State --}}
                    <div x-show="!isGenerated && !isLoading" class="text-center py-5 h-100 d-flex flex-column justify-content-center">
                        <i class="bi bi-robot display-1 text-muted opacity-25 mb-3 d-block"></i>
                        <h4 class="text-muted fw-bold">Ready to Generate</h4>
                        <p class="text-muted">Paste your notes on the left and click generate to create a complete learning module.</p>
                    </div>

                    {{-- Loading State --}}
                    <div x-show="isLoading" style="display:none;" class="text-center py-5 h-100 d-flex flex-column justify-content-center">
                        <div class="spinner-grow text-primary mb-3 mx-auto" style="width: 3rem; height: 3rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5 class="text-primary fw-bold">AI is thinking...</h5>
                        <p class="text-muted small">Reading notes, extracting concepts, and formulating quiz questions.</p>
                    </div>

                    {{-- Error State --}}
                    <div x-show="error" style="display:none;" class="alert alert-danger shadow-sm border-0">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <span x-text="error"></span>
                    </div>

                    {{-- Success State (Preview) --}}
                    <div x-show="isGenerated" style="display:none;">
                        
                        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4">
                            <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                            <div>
                                <strong>Estimated Time:</strong> <span x-text="output.estimated_minutes"></span> mins<br>
                                <strong>Key Takeaway:</strong> <span x-text="output.key_takeaway"></span>
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Concept Cards Preview (<span x-text="output.concept_cards ? output.concept_cards.length : 0"></span>)</h6>
                        
                        <div class="row g-3 mb-4">
                            <template x-for="(card, index) in output.concept_cards" :key="index">
                                <div class="col-12 col-md-6">
                                    <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #4f46e5 !important;">
                                        <div class="card-body">
                                            <div class="display-5 text-center mb-2" x-text="card.emoji"></div>
                                            <h6 class="fw-bold text-center text-dark" x-text="card.title"></h6>
                                            <p class="small text-muted text-center mb-0" x-text="card.body"></p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 mt-4">Quiz Questions (<span x-text="output.quiz_questions ? output.quiz_questions.length : 0"></span>)</h6>
                        
                        <div class="space-y-3">
                            <template x-for="(quiz, index) in output.quiz_questions" :key="index">
                                <div class="card border-0 shadow-sm mb-3">
                                    <div class="card-body">
                                        <div class="fw-bold mb-2">
                                            <span class="text-primary me-1" x-text="`Q${index+1}.`"></span>
                                            <span x-text="quiz.question"></span>
                                        </div>
                                        <ul class="list-group list-group-flush small">
                                            <template x-for="(opt, oidx) in quiz.options" :key="oidx">
                                                <li class="list-group-item bg-transparent px-0 py-1" 
                                                    :class="opt === quiz.answer ? 'text-success fw-bold' : 'text-muted'">
                                                    <i class="bi me-2" :class="opt === quiz.answer ? 'bi-check-circle-fill' : 'bi-circle'"></i>
                                                    <span x-text="opt"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>
                            </template>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('aiStudio', () => ({
        input: {
            topic_name: '',
            notes: '',
            card_count: 4,
            quiz_count: 4
        },
        isLoading: false,
        isGenerated: false,
        error: null,
        output: {
            topic_name: '',
            estimated_minutes: 0,
            key_takeaway: '',
            concept_cards: [],
            quiz_questions: []
        },

        async generateContent() {
            this.isLoading = true;
            this.error = null;
            this.isGenerated = false;

            try {
                const response = await axios.post('{{ route('instructor.lessons.ai-studio.generate', $lesson) }}', this.input, {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                if (response.data.status === 'success' || response.data.status === 'fallback') {
                    this.output = response.data.data;
                    this.isGenerated = true;
                    
                    if (response.data.status === 'fallback') {
                        // Optional: show a warning toast about fallback
                        console.warn(response.data.message);
                    }
                } else {
                    this.error = response.data.message || 'An unknown error occurred.';
                }
            } catch (err) {
                console.error(err);
                this.error = err.response?.data?.message || 'Failed to connect to AI service. Please check your internet connection and API key.';
            } finally {
                this.isLoading = false;
            }
        }
    }));
});
</script>
@endsection
