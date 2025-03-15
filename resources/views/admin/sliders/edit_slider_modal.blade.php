<div class="modal fade" id="exampleModal_{{ $slider->id }}" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Edit
                    Slider</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="myForm" action="{{ route('admin.sliders.update', $slider->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('put')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="photo">Slider Photo <span class="text-danger">*</span>
                                </label>
                                <input type="file" name="photo"
                                    onchange="document.getElementById('photo_{{ $slider->id }}').src = window.URL.createObjectURL(this.files[0])"
                                    class="form-control @error('photo') is-invalid @enderror" multiple required>

                                @error('photo')
                                <div class="alert alert-danger">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <img id="photo_{{ $slider->id }}" src="{{ asset($slider->photo) }}" alt="photo"
                                    style="width: 100px;">
                            </div>
                            <div class="form-group">
                                <label for="photo_alt_{{ $slider->id }}">Photo Alt </label>
                                <input type="text" name="photo_alt" id="photo_alt_{{ $slider->id }}"
                                    class="form-control @error('photo_alt') is-invalid @enderror"
                                    value="{{ $slider->photo_alt }}" placeholder="Photo Alt">
                                @error('photo_alt')
                                <div class="alert alert-danger">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="title_{{ $slider->id }}">Slider Title </label>
                                <input type="text" name="title"
                                    class="form-control @error('title') is-invalid @enderror"
                                    value="{{ $slider->title }}" id="title_{{ $slider->id }}"
                                    placeholder="Slider Title">
                                @error('title')
                                <div class="alert alert-danger">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <!-- Other fields here -->
                            <div class="form-group">
                                <label for="category_id">Category</label>
                                <select name="category_id" id="category_id" class="form-control">
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ $slider->category_id == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="tag_id">Tag</label>
                                <select name="tag_id" id="tag_id" class="form-control">
                                    <option value="">Select Tag</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save
                        changes</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
        // When category is selected, fetch related tags (categories)
        $('#category_id').on('change', function() {
            var categoryId = $(this).val();
            if (categoryId) {
                $.ajax({
                    url: '{{ route("admin.getTags") }}',
                    type: 'GET',
                    data: {
                        category_id: categoryId
                    },
                    success: function(response) {
                        console.log(response); // Check the response from the server
                        $('#tag_id').empty().append('<option value="">Select Tag</option>');
                        $.each(response, function(index, tag) {
                            $('#tag_id').append('<option value="' + tag.id + '">' + tag.name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", error);
                    }
                });
            } else {
                $('#tag_id').empty().append('<option value="">Select Tag</option>');
            }
        });
    });
</script>
