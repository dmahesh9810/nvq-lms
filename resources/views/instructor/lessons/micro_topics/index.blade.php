@extends('layouts.app')

@section('title', 'Manage Micro-Topics')
@section('page-title', 'Micro-Topics for: ' . $lesson->title)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Micro-Topics & Flashcards</h2>
            <p class="text-muted mb-0">Manage the swipable concept cards for: <strong>{{ $lesson->title }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('instructor.lessons.ai-studio.index', $lesson) }}" class="btn btn-dark fw-bold shadow-sm" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); border: none;">
                <i class="bi bi-magic me-1 text-warning"></i> Generate with AI
            </a>
            <a href="{{ route('instructor.lessons.micro-topics.create', $lesson) }}" class="btn btn-primary fw-bold shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Create Manual
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">Topic Name</th>
                            <th scope="col">Est. Minutes</th>
                            <th scope="col">Concept Cards</th>
                            <th scope="col" class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($microTopics as $topic)
                            @php
                                $cardsCount = is_array($topic->concept_cards) ? count($topic->concept_cards) : 0;
                            @endphp
                            <tr>
                                <td class="ps-4 fw-semibold text-dark">{{ $topic->topic_name }}</td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1">
                                        <i class="bi bi-clock me-1"></i>{{ $topic->estimated_minutes }} min
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">
                                        🃏 {{ $cardsCount }} Cards
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('instructor.lessons.micro-topics.edit', [$lesson, $topic]) }}" class="btn btn-sm btn-outline-primary me-2">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <form action="{{ route('instructor.lessons.micro-topics.destroy', [$lesson, $topic]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this topic and all its flashcards?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-card-text display-4 d-block mb-3 text-secondary opacity-50"></i>
                                        <p class="mb-0 fs-5">No Micro-Topics found for this lesson yet.</p>
                                        <small>Click the "Create" button above to start converting your notes into interactive flashcards!</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
