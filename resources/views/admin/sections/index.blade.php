@extends('admin.admin_dashboard')

@section('main')
<div class="page-inner">
    <div class="row">
        <div class="col-md-12">
            <h4 class="page-title">Sections</h4>
            <a href="{{ route('admin.sections.create') }}" class="btn btn-primary">Add New Section</a>

            {{-- Toggle buttons --}}
            <div class="mt-3 mb-3">
                <button id="btn-section" class="btn btn-info">Sections</button>
                <button id="btn-search" class="btn btn-secondary">Search Sections</button>
            </div>

            {{-- Sections Table --}}
            <div id="table-section" class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Section Name</th>
                            <th>Section Type</th>
                            <th>Category</th>
                            <th>Background IMG</th>
                            <th>Index</th>
                            <th>Visibility</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sections as $section)
                        <tr>
                            <td>{{ $section->section_name }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $section->section_type)) }}</td>
                            <td>{{ $section->category->name ?? '-' }}</td>
                            <td>
                                @if($section->background_image)
                                <img src="{{ asset($section->background_image) }}" alt="back Image" width="100" />
                                @else
                                -
                                @endif
                            </td>
                            <td>{{ $section->index }}</td>
                            <td>{{ $section->visibility ? 'Visible' : 'Hidden' }}</td>
                            <td>
                                <a href="{{ route('admin.sections.edit', $section->id) }}" class="btn btn-warning">View / Edit</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Search Sections Table (hidden by default) --}}
            <div id="table-search" class="table-responsive" style="display:none;">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Section Name</th>
                            <th>Section Type</th>
                            <th>Category</th>
                            <th>Index</th>
                            <th>Visibility</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($searchSections as $section)
                        <tr>
                            <td>{{ $section->section_name }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $section->section_type)) }}</td>
                            <td>{{ $section->category->name ?? '-' }}</td>
                            <td>{{ $section->index }}</td>
                            <td>{{ $section->visibility ? 'Visible' : 'Hidden' }}</td>
                            <td>
                                <a href="{{ route('admin.sections.edit', $section->id) }}" class="btn btn-warning">View / Edit</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

{{-- Toggle Script --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnSection = document.getElementById('btn-section');
        const btnSearch = document.getElementById('btn-search');
        const tableSection = document.getElementById('table-section');
        const tableSearch = document.getElementById('table-search');

        btnSection.addEventListener('click', function() {
            tableSection.style.display = '';
            tableSearch.style.display = 'none';

            btnSection.classList.add('btn-info');
            btnSection.classList.remove('btn-secondary');

            btnSearch.classList.add('btn-secondary');
            btnSearch.classList.remove('btn-info');
        });

        btnSearch.addEventListener('click', function() {
            tableSection.style.display = 'none';
            tableSearch.style.display = '';

            btnSearch.classList.add('btn-info');
            btnSearch.classList.remove('btn-secondary');

            btnSection.classList.add('btn-secondary');
            btnSection.classList.remove('btn-info');
        });
    });
</script>
@endsection
