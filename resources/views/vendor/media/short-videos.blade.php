@extends('vendor.vendor_dashboard')

@section('main')
<div class="container mt-4">
    <h2>Manage Videos</h2>

    <!-- Toggle Section -->
    <button id="toggleForm" class="btn btn-primary">+ Add Video</button>
    <div id="videoForm" style="display: none;" class="mt-3">
        <form action="{{ route('vendor.video.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="video" class="form-label">Upload Video</label>
                <input type="file" class="form-control" name="video" accept="video/*" required>
            </div>
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" name="title">
            </div>
            <div class="mb-3">
                <label for="alt_text" class="form-label">Alternative Text</label>
                <input type="text" class="form-control" name="alt_text">
            </div>
            <button type="submit" class="btn btn-success">Upload</button>
        </form>
    </div>

    <!-- Video List -->
    <div class="row mt-4">
        @foreach ($shorts as $short)
        <div class="col-md-4">
            <div class="card mb-3">
                <video width="100%" height="200" controls>
                    <source src="{{ asset($short->video) }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <div class="card-body">
                    <!-- Toggle for Update Section -->
                    <button class="btn btn-sm btn-info" id="toggleUpdateForm_{{ $short->id }}">Update Video</button>

                    <div id="updateForm_{{ $short->id }}" style="display: none;" class="mt-3">
                        <form action="{{ route('vendor.video.update', $short->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-2">
                                <label for="title_{{ $short->id }}" class="form-label">Video Title</label>
                                <input type="text" id="title_{{ $short->id }}" name="title" class="form-control"
                                    value="{{ $short->title }}" placeholder="Update video title">
                            </div>
                            <div class="mb-2">
                                <label for="alt_text_{{ $short->id }}" class="form-label">Alt Text</label>
                                <input type="text" id="alt_text_{{ $short->id }}" name="alt_text" class="form-control"
                                    value="{{ $short->alt_text }}" placeholder="Update alt text">
                            </div>
                            <button type="submit" class="btn btn-sm btn-warning">Update</button>
                        </form>
                    </div>

                    <form action="{{ route('vendor.video.delete', $short->id) }}" method="POST" class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

</div>

<script>
    // Toggle for "Add Video" form
    document.getElementById('toggleForm').addEventListener('click', function() {
        let form = document.getElementById('videoForm');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    });

    // Toggle for individual video update form
    @foreach($shorts as $short)
    document.getElementById('toggleUpdateForm_{{ $short->id }}').addEventListener('click', function() {
        let form = document.getElementById('updateForm_{{ $short->id }}');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    });
    @endforeach
</script>
@endsection