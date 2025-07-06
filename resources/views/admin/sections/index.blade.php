@extends('admin.admin_dashboard')

@section('main')
<div class="page-inner">
    <div class="row">
        <div class="col-md-12">
            <h4 class="page-title">Sections</h4>
            <a href="{{ route('admin.sections.create') }}" class="btn btn-primary">Add New Section</a>
            <div class="table-responsive mt-3">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Section Name</th>
                            <th>Section Type</th>
                            <th>Category</th>
                            <th>Background IMG</th>
                            <th>Index</th>
                            <th>Visibility</th>
                            <th>Items</th> <!-- Display Items for each section -->
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sections as $section)
                        <tr>
                            <td>{{ $section->section_name }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $section->section_type)) }}</td>
                            <td>{{ $section->category->name }}</td>
                             <td><img src="{{ asset($section->background_image) }}" alt="back Image" width="200" /></td>
                            <td>{{ $section->index }}</td>
                            <td>{{ $section->visibility ? 'Visible' : 'Hidden' }}</td>

                            <!-- Display Items for the current section -->
                            <td>
                                @if ($section->items->isNotEmpty())
                                    <ul>
                                        @foreach ($section->items as $item)
                                            <li>
                                                <strong>Tag:</strong> {{ $item->tag->name }} <br>
                                                <strong>Index:</strong> {{ $item->index }} <br>
                                                <strong>Visibility:</strong> {{ $item->visibility ? 'Visible' : 'Hidden' }} <br>
                                                <img src="{{ asset($item->image) }}" alt="Item Image" width="50" /> <!-- Show Image -->
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span>No Items</span>
                                @endif
                            </td>

                            <td>
                                <a href="{{ route('admin.sections.edit', $section->id) }}" class="btn btn-warning">Edit</a>
                                <!-- You can add delete functionality if needed -->
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
